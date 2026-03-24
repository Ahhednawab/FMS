<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use Illuminate\Http\Request;
use App\Models\DailyMileageReport;
use App\Models\Vehicle;
use App\Models\MileageStatus;
use App\Models\Station;
use Carbon\Carbon;
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

        // Vehicle filter
        if ($request->filled('vehicle_id')) {
            $query->whereIn('vehicle_id', $request->vehicle_id);
        }

        // Search filter
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

        // Date filters
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
        // Get draft data if available
        $draftData = null;
        if ($request->filled('draft_id')) {
            $draftData = Draft::find($request->draft_id);
        }

        // Initialize selectedStation as empty
        $selectedStation = $request->station_id ?? '';


        // If draftData is available, use the selected station from the draft
        if ($draftData) {
            $selectedStation = $draftData->data['selectedstations'] ?? $selectedStation;
        }

        // Get vehicles based on the selected station
        $vehicles = Vehicle::with('station')
            ->where('is_active', 1)
            ->when($selectedStation, function ($query) use ($selectedStation) {
                return $query->whereIn('station_id', $selectedStation);  // Filter vehicles by the selected station
            })
            ->orderBy(Station::select('area')->whereColumn('stations.id', 'vehicles.station_id')->limit(1))
            ->orderBy('vehicle_no')
            ->get();

        // Prepare vehicle data
        $vehicleData = [];
        foreach ($vehicles as $vehicle) {
            $previousRecord = DailyMileageReport::where('vehicle_id', $vehicle->id)
                ->where('is_active', 1)
                ->orderBy('report_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $previous_km = ($previousRecord) ? $previousRecord->current_km : $vehicle->kilometer;

            $vehicleData[] = [
                'vehicle_id' => $vehicle->id,
                'station' => $vehicle->station->area,
                'vehicle_no' => $vehicle->vehicle_no,
                'previous_km' => $previous_km
            ];
        }

        // Get all stations for the filter dropdown
        $stations = Vehicle::with('station')
            ->where('is_active', 1)
            ->get()
            ->pluck('station.area', 'station_id')
            ->sort()
            ->unique()
            ->toArray();

        // Return view with necessary data
        return view('admin.dailyMileages.create', compact('vehicles', 'vehicleData', 'stations', 'selectedStation', 'draftData'));
    }


    //    public function store(Request $request)
    //    {
    //        $validator = \Validator::make(
    //            $request->all(),
    //            [
    //                'current_km'    => 'array',
    //                'current_km.*'  => 'nullable|numeric|min:0',
    //                'report_date' => 'required|date|before_or_equal:today',
    //            ],
    //            [
    //                'report_date.required'        =>  'Date is required',
    //                'report_date.date'            =>  'Date must be a valid date',
    //                'report_date.before_or_equal' =>  'Date cannot be greater than today',
    //            ]
    //        );
    //
    //
    //        $validator->after(function ($validator) use ($request) {
    //            $previous_kms = $request->previous_km;
    //            $current_kms = $request->current_km;
    //            $vehicle_ids = $request->vehicle_id;
    //            $report_date = $request->report_date;
    //
    //            foreach ($current_kms as $index => $current_km){
    //                $previous_km = $previous_kms[$index] ?? 0;
    //
    //                if($current_km < $previous_km && isset($current_km)){
    //                    $validator->errors()->add("current_km.$index", "Current KM must be greater than Previous KM.");
    //                }
    //
    //                $vehicle_id = $vehicle_ids[$index] ?? null;
    //                if ($vehicle_id && isset($current_km)) {
    //                    $last = DailyMileageReport::where('vehicle_id', $vehicle_id)
    //                        ->where('is_active', 1)
    //                        ->orderBy('report_date', 'desc')
    //                        ->orderBy('id', 'desc')
    //                        ->first();
    //
    //                    if ($last) {
    //                        if ($report_date && Carbon::parse($report_date)->lt(Carbon::parse($last->report_date))) {
    //                            $lastDateFormatted = Carbon::parse($last->report_date)->format('d-M-Y');
    //                            $validator->errors()->add("current_km.$index", "Report date cannot be earlier than last date ($lastDateFormatted) for this vehicle.");
    //                        }
    //
    //                        if ($current_km < $last->current_km) {
    //                            $validator->errors()->add("current_km.$index", "Current KM for this vehicle cannot be less than last recorded KM ({$last->current_km}).");
    //                        }
    //                    }
    //                }
    //            }
    //        });
    //
    //        if ($validator->fails()) {
    //            $messages = $validator->getMessageBag();
    //            return redirect()->back()->withErrors($validator)->withInput();
    //        }
    //
    //        $vehicle_ids    = $request->vehicle_id;
    //        $report_date    = $request->report_date;
    //        $mileages       = $request->mileage;
    //        $previous_kms   = $request->previous_km;
    //        $current_kms    = $request->current_km;
    //
    //        foreach ($vehicle_ids as $index => $vehicle_id) {
    //            if (isset($current_kms[$index])) {
    //                DailyMileageReport::Create([
    //                    'vehicle_id'    =>  $vehicle_id,
    //                    'report_date'   =>  $report_date,
    //                    'mileage'       =>  ($current_kms[$index] - $previous_kms[$index]),
    //                    'previous_km'   =>  $previous_kms[$index],
    //                    'current_km'    =>  $current_kms[$index],
    //                ]);
    //            }
    //        }
    //
    //        return redirect()->route('admin.dailyMileages.index')->with('success', 'Daily Mileage created successfully.');
    //    }


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

        // ---------------- VALIDATE EACH VEHICLE ----------------
        foreach ($vehicles as $vehicleId => $data) {

            $currentKm = $data['current_km'] ?? null;
            if ($currentKm === null || $currentKm === '') {
                continue; // skip empty rows
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
                    "vehicles.$vehicleId.current_km" =>
                        "Current KM cannot be less than Previous KM ($previousKm)"
                ])->withInput();
            }

            $vehicles[$vehicleId]['previous_km'] = $previousKm;
        }

        // ---------------- SAVE DRAFT ----------------
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

        // ---------------- SAVE REPORTS ----------------
        foreach ($vehicles as $vehicleId => $data) {

            if (empty($data['current_km'])) {
                continue; // skip incomplete rows
            }

            $previousKm = $data['previous_km'];
            $currentKm = $data['current_km'];
            $mileage = $currentKm - $previousKm;

            // Only updateOrCreate if current_km exists
            DailyMileageReport::updateOrCreate(
                [
                    'vehicle_id' => $vehicleId,
                    'report_date' => $reportDate,
                ],
                [
                    'previous_km' => $previousKm,
                    'current_km' => $currentKm,
                    'mileage' => $mileage,
                    'is_active' => 1,
                ]
            );
        }

        return back()->with('success', 'Draft saved and partial mileage recorded.');
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
            'previous_km' => 'required|numeric',
        ]);

        $currentKm = $request->current_km;
        $previousKm = $request->previous_km;

        if ($currentKm < $previousKm) {
            return back()->withErrors(['current_km' => "Current KM must be greater than Previous KM."])->withInput();
        }

        // Update current row
        $dailyMileage->update([
            'report_date' => $request->report_date,
            'previous_km' => $previousKm,
            'current_km' => $currentKm,
            'mileage' => $currentKm - $previousKm,
        ]);

        // Update future rows safely
        $futureRecords = DailyMileageReport::where('vehicle_id', $dailyMileage->vehicle_id)
            ->where('id', '>', $dailyMileage->id)
            ->whereNotNull('current_km') // ✅ only update rows that have current_km
            ->get();

        foreach ($futureRecords as $record) {
            $record->previous_km = $currentKm;
            $record->mileage = $record->current_km - $currentKm;
            $record->save();

            $currentKm = $record->current_km; // next iteration
        }

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

        /**
         * 1️⃣ Records for the selected date
         *    (current day data)
         */
        $records = DailyMileageReport::with('vehicle')
            ->whereDate('report_date', $reportDate)
            ->get()
            ->keyBy('vehicle_id');

        /**
         * 2️⃣ Previous records
         *    Get the LAST entry BEFORE selected date (per vehicle)
         */
        $previousRecords = DailyMileageReport::with('vehicle')
            ->whereDate('report_date', '<', $reportDate)
            ->orderBy('report_date', 'desc') // latest date first
            ->orderBy('id', 'desc')          // tie-breaker
            ->get()
            ->unique('vehicle_id')           // one record per vehicle
            ->keyBy('vehicle_id');

        /**
         * 3️⃣ Merge current & previous data
         */
        $finalData = [];

        // Current date records
        foreach ($records as $vehicleId => $record) {
            $finalData[$vehicleId] = [
                'vehicle_id' => $vehicleId,
                'previous_km' => $record->previous_km ?? $record->vehicle->kilometer,
                'current_km' => $record->current_km,
                'mileage' => $record->mileage,
                'vehicle' => $record->vehicle,
            ];
        }
        // Previous date records (only if current date missing)
        foreach ($previousRecords as $vehicleId => $record) {
            if (!isset($finalData[$vehicleId])) {
                $finalData[$vehicleId] = [
                    'vehicle_id' => $vehicleId,
                    'previous_km' => $record->current_km, // ← 20 Jan value
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
}
