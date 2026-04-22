<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyMileageReport;
use App\Models\Draft;
use App\Models\MileageStatus;
use App\Models\Station;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Nette\Utils\Json;

class DailyMileageController extends Controller
{
    public function __construct()
    {
        if (!auth()->user()->hasPermission('daily_mileages')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    public function index(Request $request)
    {
        $query = DailyMileageReport::query();

        if ($request->filled('vehicle_id')) {
            $query->whereIn('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('current_km', 'like', "%{$search}%")
                    ->orWhere('previous_km', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($v) use ($search) {
                        $v->where('vehicle_no', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('report_date', '>=', Carbon::parse($request->from_date));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('report_date', '<=', Carbon::parse($request->to_date));
        }

        $perPage = $request->get('per_page', 10);

        $dailyMileages = $query
            ->where('is_active', 1)
            ->whereHas('vehicle', function ($q) {
                $q->where('is_active', 1);
            })
            ->with(['vehicle.station'])
            ->orderBy('report_date', 'DESC')
            ->paginate($perPage)
            ->withQueryString();

        $vehicles = Vehicle::where('is_active', 1)->get();

        return view('admin.dailyMileages.index', compact('dailyMileages', 'vehicles'));
    }

    public function create(Request $request)
    {
        $draftData = null;
        if ($request->filled('draft_id')) {
            $draftData = Draft::find($request->draft_id);
        }

        $selectedStation = $request->station_id ?? '';

        if ($draftData) {
            $selectedStation = $draftData->data['selectedstations'] ?? $selectedStation;
        }

        $vehicles = Vehicle::with('station')
            ->where('is_active', 1)
            ->when($selectedStation, function ($query) use ($selectedStation) {
                return $query->whereIn('station_id', $selectedStation);
            })
            ->orderBy(Station::select('area')->whereColumn('stations.id', 'vehicles.station_id')->limit(1))
            ->orderBy('vehicle_no')
            ->get();

        $vehicleData = [];
        foreach ($vehicles as $vehicle) {
            $previousRecord = DailyMileageReport::where('vehicle_id', $vehicle->id)
                ->where('is_active', 1)
                ->orderBy('report_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $previous_km = $previousRecord ? $previousRecord->current_km : $vehicle->kilometer;

            $vehicleData[] = [
                'vehicle_id' => $vehicle->id,
                'station' => $vehicle->station->area,
                'vehicle_no' => $vehicle->vehicle_no,
                'previous_km' => $previous_km
            ];
        }

        $stations = Vehicle::with('station')
            ->where('is_active', 1)
            ->get()
            ->pluck('station.area', 'station_id')
            ->sort()
            ->unique()
            ->toArray();

        return view('admin.dailyMileages.create', compact('vehicles', 'vehicleData', 'stations', 'selectedStation', 'draftData'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date|before_or_equal:today',
            'vehicles' => 'required|array',
        ], [
            'report_date.required' => 'Date is required',
            'report_date.date' => 'Invalid date',
            'report_date.before_or_equal' => 'Date cannot be in the future',
        ]);

        $reportDate = Carbon::parse($request->report_date)->format('Y-m-d');
        $vehicles = $request->vehicles;

        foreach ($vehicles as $vehicleId => $data) {
            $currentKm = $data['current_km'] ?? null;
            if ($currentKm === null || $currentKm === '') {
                continue;
            }

            $previousKm = $data['previous_km'] ?? null;

            if ($previousKm === null || $previousKm === '') {
                $last = DailyMileageReport::where('vehicle_id', $vehicleId)
                    ->whereDate('report_date', '<', $reportDate)
                    ->orderBy('report_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                $previousKm = $last?->current_km ?? 0;
            }

            if ($currentKm < $previousKm) {
                return back()->withErrors([
                    "vehicles.$vehicleId.current_km" => "Current KM cannot be less than Previous KM ($previousKm)"
                ])->withInput();
            }

            $vehicles[$vehicleId]['previous_km'] = $previousKm;
        }

        Draft::where('module', 'dailymileage')
            ->where('created_by', auth()->id())
            ->where('data->report_date', $reportDate)
            ->delete();

        Draft::create([
            'module' => 'dailymileage',
            'created_by' => auth()->id(),
            'data' => [
                'report_date' => $reportDate,
                'vehicles' => $vehicles
            ]
        ]);

        DB::transaction(function () use ($vehicles, $reportDate) {
            foreach ($vehicles as $vehicleId => $data) {
                if ($data['current_km'] === null || $data['current_km'] === '') {
                    continue;
                }

                $vehicle = Vehicle::lockForUpdate()->findOrFail($vehicleId);
                $previousRecord = $this->getPreviousMileageRecord((int) $vehicleId, $reportDate);
                $previousKm = $previousRecord?->current_km ?? (int) $vehicle->kilometer;
                $currentKm = (int) $data['current_km'];

                if ($currentKm < $previousKm) {
                    throw ValidationException::withMessages([
                        "vehicles.$vehicleId.current_km" => "Current KM cannot be less than Previous KM ($previousKm)",
                    ]);
                }

                $record = DailyMileageReport::updateOrCreate(
                    [
                        'vehicle_id' => $vehicleId,
                        'report_date' => $reportDate,
                    ],
                    [
                        'previous_km' => $previousKm,
                        'current_km' => $currentKm,
                        'mileage' => $currentKm - $previousKm,
                        'is_active' => 1,
                    ]
                );

                Log::info('Daily mileage saved', [
                    'vehicle_id' => (int) $vehicleId,
                    'report_date' => $reportDate,
                    'previous_record_id' => $previousRecord?->id,
                    'record_id' => $record->id,
                    'previous_km' => $previousKm,
                    'current_km' => $currentKm,
                ]);

                $this->recalculateVehicleMileageChain((int) $vehicleId, $reportDate, (int) $record->id);
            }
        });

        return redirect()->route('admin.dailyMileages.index')->with('success', 'Draft saved and partial mileage recorded.');
    }

    public function edit(DailyMileageReport $dailyMileage)
    {
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');

        $months = [];
        for ($i = 0; $i <= 3; $i++) {
            $months[] = date("F", strtotime("-$i months"));
        }

        return view('admin.dailyMileages.edit', compact('dailyMileage', 'months', 'vehicles'));
    }

    public function update(Request $request, DailyMileageReport $dailyMileage)
    {
        $request->validate([
            'report_date' => 'required|date',
            'current_km' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request, $dailyMileage) {
            $reportDate = Carbon::parse($request->report_date)->toDateString();
            $currentKm = (int) $request->current_km;

            $dailyMileage = DailyMileageReport::lockForUpdate()->findOrFail($dailyMileage->id);
            $vehicle = Vehicle::lockForUpdate()->findOrFail($dailyMileage->vehicle_id);

            $duplicateExists = DailyMileageReport::query()
                ->where('vehicle_id', $dailyMileage->vehicle_id)
                ->whereDate('report_date', $reportDate)
                ->where('id', '!=', $dailyMileage->id)
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'report_date' => 'A mileage entry already exists for this vehicle on the selected date.',
                ]);
            }

            $previousRecord = $this->getPreviousMileageRecord($dailyMileage->vehicle_id, $reportDate, $dailyMileage->id);
            $previousKm = $previousRecord?->current_km ?? (int) $vehicle->kilometer;

            if ($currentKm < $previousKm) {
                throw ValidationException::withMessages([
                    'current_km' => "Current KM must be greater than or equal to Previous KM ($previousKm).",
                ]);
            }

            Log::info('Daily mileage update started', [
                'record_id' => $dailyMileage->id,
                'vehicle_id' => $dailyMileage->vehicle_id,
                'old_report_date' => optional($dailyMileage->report_date)->toDateString() ?? (string) $dailyMileage->report_date,
                'new_report_date' => $reportDate,
                'previous_record_id' => $previousRecord?->id,
                'previous_km' => $previousKm,
                'requested_current_km' => $currentKm,
            ]);

            $dailyMileage->update([
                'report_date' => $reportDate,
                'previous_km' => $previousKm,
                'current_km' => $currentKm,
                'mileage' => $currentKm - $previousKm,
            ]);

            $this->recalculateVehicleMileageChain(
                (int) $dailyMileage->vehicle_id,
                $reportDate,
                (int) $dailyMileage->id
            );
        });

        return redirect()->route('dailyMileages.index')->with('success', 'Daily Mileage updated successfully.');
    }

    public function show(DailyMileageReport $dailyMileage)
    {
        return view('admin.dailyMileages.show', compact('dailyMileage'));
    }

    public function destroy(DailyMileageReport $dailyMileage)
    {
        $dailyMileage->delete();

        return redirect()
            ->route('dailyMileages.index')
            ->with('delete_msg', 'Daily Mileage deleted successfully.');
    }

    public function destroyMultiple(Request $request)
    {
        foreach ($request->ids as $id) {
            DailyMileageReport::findOrFail($id)->delete();
        }

        return redirect()
            ->route('dailyMileages.index')
            ->with('delete_msg', 'Records deleted successfully.');
    }

    public function fetchDailyMilages(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
        ]);

        $reportDate = Carbon::parse($request->report_date)->toDateString();

        $records = DailyMileageReport::with('vehicle')
            ->whereDate('report_date', $reportDate)
            ->get()
            ->keyBy('vehicle_id');

        $previousRecords = DailyMileageReport::with('vehicle')
            ->whereDate('report_date', '<', $reportDate)
            ->orderBy('report_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('vehicle_id')
            ->keyBy('vehicle_id');

        $finalData = [];

        foreach ($records as $vehicleId => $record) {
            $finalData[$vehicleId] = [
                'vehicle_id' => $vehicleId,
                'previous_km' => $record->previous_km ?? $record->vehicle->kilometer,
                'current_km' => $record->current_km,
                'mileage' => $record->mileage,
                'vehicle' => $record->vehicle,
            ];
        }

        foreach ($previousRecords as $vehicleId => $record) {
            if (!isset($finalData[$vehicleId])) {
                $finalData[$vehicleId] = [
                    'vehicle_id' => $vehicleId,
                    'previous_km' => $record->current_km,
                    'current_km' => null,
                    'mileage' => null,
                    'vehicle' => $record->vehicle,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => array_values($finalData),
            'message' => 'Data fetched successfully',
        ]);
    }

    public function fetchPreviousMileageByVehicleAndDate(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'report_date' => 'required|date',
            'exclude_id' => 'nullable|integer',
        ]);

        $reportDate = Carbon::parse($validated['report_date'])->toDateString();
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $previousRecord = DailyMileageReport::query()
            ->where('vehicle_id', $validated['vehicle_id'])
            ->where('is_active', 1)
            ->when(
                !empty($validated['exclude_id']),
                fn ($query) => $query->where('id', '!=', $validated['exclude_id'])
            )
            ->whereDate('report_date', '<', $reportDate)
            ->orderBy('report_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $duplicateExists = DailyMileageReport::query()
            ->where('vehicle_id', $validated['vehicle_id'])
            ->where('is_active', 1)
            ->when(
                !empty($validated['exclude_id']),
                fn ($query) => $query->where('id', '!=', $validated['exclude_id'])
            )
            ->whereDate('report_date', $reportDate)
            ->exists();

        return response()->json([
            'success' => true,
            'previous_km' => $previousRecord?->current_km ?? (int) $vehicle->kilometer,
            'previous_record_id' => $previousRecord?->id,
            'duplicate_exists' => $duplicateExists,
        ]);
    }

    private function getPreviousMileageRecord(int $vehicleId, string $reportDate, ?int $excludeId = null): ?DailyMileageReport
    {
        return DailyMileageReport::query()
            ->where('vehicle_id', $vehicleId)
            ->where('is_active', 1)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->whereDate('report_date', '<', $reportDate)
            ->orderBy('report_date', 'desc')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();
    }

    private function recalculateVehicleMileageChain(int $vehicleId, string $reportDate, int $anchorRecordId): void
    {
        $vehicle = Vehicle::query()->lockForUpdate()->findOrFail($vehicleId);

        $records = DailyMileageReport::query()
            ->where('vehicle_id', $vehicleId)
            ->where('is_active', 1)
            ->orderBy('report_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $runningCurrentKm = (int) $vehicle->kilometer;
        $anchorReached = false;

        foreach ($records as $record) {
            $isAnchorRecord = (int) $record->id === $anchorRecordId;

            if (! $anchorReached && ! $isAnchorRecord) {
                $runningCurrentKm = (int) $record->current_km;
                continue;
            }

            if ($isAnchorRecord) {
                $anchorReached = true;

                $anchorMileage = (int) $record->current_km - $runningCurrentKm;

                if ($anchorMileage < 0) {
                    Log::warning('Daily mileage chronology conflict detected on anchor record', [
                        'vehicle_id' => $vehicleId,
                        'record_id' => $record->id,
                        'report_date' => optional($record->report_date)->toDateString() ?? (string) $record->report_date,
                        'required_previous_km' => $runningCurrentKm,
                        'current_km' => (int) $record->current_km,
                    ]);

                    throw ValidationException::withMessages([
                        'current_km' => 'Current KM cannot be less than the previous mileage in the vehicle chain.',
                    ]);
                }

                $record->update([
                    'previous_km' => $runningCurrentKm,
                    'mileage' => $anchorMileage,
                    'current_km' => $runningCurrentKm + $anchorMileage,
                ]);

                Log::info('Daily mileage anchor recalculated', [
                    'vehicle_id' => $vehicleId,
                    'record_id' => $record->id,
                    'report_date' => optional($record->report_date)->toDateString() ?? (string) $record->report_date,
                    'previous_km' => $runningCurrentKm,
                    'current_km' => (int) $record->current_km,
                    'mileage' => $anchorMileage,
                ]);

                $runningCurrentKm = (int) $record->current_km;
                continue;
            }

            $preservedMileage = (int) $record->mileage;
            $recalculatedCurrentKm = $runningCurrentKm + $preservedMileage;

            $record->update([
                'previous_km' => $runningCurrentKm,
                'current_km' => $recalculatedCurrentKm,
                'mileage' => $preservedMileage,
            ]);

            Log::info('Daily mileage future record recalculated', [
                'vehicle_id' => $vehicleId,
                'record_id' => $record->id,
                'report_date' => optional($record->report_date)->toDateString() ?? (string) $record->report_date,
                'previous_km' => $runningCurrentKm,
                'current_km' => $recalculatedCurrentKm,
                'mileage' => $preservedMileage,
            ]);

            $runningCurrentKm = $recalculatedCurrentKm;
        }
    }
}
