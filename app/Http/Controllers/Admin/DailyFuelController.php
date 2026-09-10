<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Station;
use App\Models\Vehicle;
use App\Models\Destination;
use App\Models\FuelStation;
use Illuminate\Http\Request;
use App\Models\DailyFuelReport;

use App\Models\DailyMileageReport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DailyFuelController extends Controller
{
    public function __construct()
    {
        if (!auth()->user()->hasPermission('daily_fuels')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    public function index(Request $request)
    {
        $fromDate = $request->filled('from_date') ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->filled('to_date') ? Carbon::parse($request->to_date) : Carbon::now();

        $dailyFuels = DailyFuelReport::where('is_active', 1)
            ->whereHas('vehicle', function ($query) {
                $query->where('is_active', 1);
            })
            ->with(['vehicle.station'])->orderby('id', 'DESC')
            ->with('vehicle');

        if ($request->filled('vehicle_id')) {
            $dailyFuels = $dailyFuels->whereHas('vehicle', function ($q) use ($request) {
                $q->where('vehicle_id', $request->vehicle_id);
            });
        }


        $dailyFuels = $dailyFuels->whereBetween('report_date', [
            $fromDate->toDateString(),
            $toDate->toDateString(),
        ]);
        $dailyFuels = $dailyFuels->orderBy('id', 'DESC');
        $dailyFuels = $dailyFuels->get();
        $totalFuelTaken = $dailyFuels->sum('fuel_taken');
        $averageFuelAvg = $dailyFuels->avg('fuel_average');

        $vehicles = Vehicle::where('is_active', 1)->get();

        $vehicleData = [];
        foreach ($vehicles as $vehicle) {
            $previousRecord = DailyMileageReport::where('vehicle_id', $vehicle->id)
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->first();

            $previous_km = ($previousRecord) ? $previousRecord->current_km : $vehicle->kilometer;

            $vehicleData[] = [
                'vehicle_id' => $vehicle->id,
                'station' => $vehicle->station->area,
                'vehicle_no' => $vehicle->vehicle_no,
                'previous_km' => $previous_km
            ];
        }
        return view('admin.dailyFuels.index', compact('dailyFuels', 'vehicles', 'vehicleData', 'totalFuelTaken', 'averageFuelAvg'));
    }

    public function create(Request $request)
    {
        $selectedDate = $request->report_date ?? date('Y-m-d');

        $vehicles = Vehicle::with('station');
        $vehicles = $vehicles->where('is_active', 1);

        // Station filter accepts one or many stations (multi-select).
        $stationIds = array_filter((array) $request->input('station_id', []));
        if (!empty($stationIds)) {
            $vehicles = $vehicles->whereIn('station_id', $stationIds);
        }
        $vehicles = $vehicles->orderBy(Station::select('area')->whereColumn('stations.id', 'vehicles.station_id')->limit(1));
        $vehicles = $vehicles->orderBy('vehicle_no');
        $vehicles = $vehicles->get();

        $vehicleData = array();

        foreach ($vehicles as $vehicle) {
            $previousRecord = DailyFuelReport::where('vehicle_id', $vehicle->id)
                ->whereDate('report_date', '<', $selectedDate)
                ->where('is_active', 1)
                ->orderBy('report_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();



            $previous_km = ($previousRecord) ? $previousRecord->current_km : $vehicle->kilometer;

            $vehicleData[] = array(
                'vehicle_id' => $vehicle->id,
                'station' => $vehicle->station->area,
                'vehicle_no' => $vehicle->vehicle_no,
                'previous_km' => $previous_km,
                'has_history' => (bool) $previousRecord,
            );
        }

        $stations = Vehicle::with('station');
        $stations = $stations->where('is_active', 1);
        $stations = $stations->get();
        $stations = $stations->pluck('station.area', 'station_id');
        $stations = $stations->sort();
        $stations = $stations->unique();
        $stations = $stations->toArray();

        $selectedStations = array_map('strval', $stationIds);

        return view('admin.dailyFuels.create', compact('vehicles', 'vehicleData', 'stations', 'selectedStations', 'selectedDate'));
    }

    public function fetchPreviousKmByDate(Request $request)
    {
        $reportDate = $request->report_date;

        // Get all active vehicles, optionally filter by station if needed
        $vehicles = Vehicle::where('is_active', 1)->get();

        $data = [];

        foreach ($vehicles as $vehicle) {
            // Find the most recent fuel report before selected date
            $previousFuelReport = DailyFuelReport::where('vehicle_id', $vehicle->id)
                ->where('is_active', 1)
                ->whereDate('report_date', '<', $reportDate)
                ->orderBy('report_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            // Determine previous_km
            if ($previousFuelReport && $previousFuelReport->current_km !== null) {
                $previous_km = $previousFuelReport->current_km;
            } else {
                // Fallback: use initial vehicle kilometer
                $previous_km = $vehicle->kilometer ?? 0;
            }

            // Check if a fuel report already exists for selected date
            $existingFuelReport = DailyFuelReport::where('vehicle_id', $vehicle->id)
                ->whereDate('report_date', $reportDate)
                ->where('is_active', 1)
                ->first();

            $data[] = [
                'vehicle_id' => $vehicle->id,
                'previous_km' => $previous_km,
                'current_km' => $existingFuelReport ? $existingFuelReport->current_km : '',
                'fuel_taken' => $existingFuelReport ? $existingFuelReport->fuel_taken : '',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'report_date' => 'required|date|before_or_equal:today',
                'current_km' => 'array',
                'fuel_taken' => 'array',
                'current_km.*' => 'nullable|numeric|min:0',
                'fuel_taken.*' => 'nullable|numeric|min:0',
            ],
            [
                'report_date.required' => 'Report date is required.',
                'report_date.before_or_equal' => 'Report date cannot be in the future.',
                'current_km.*.numeric' => 'Current KMs must be a number.',
                'fuel_taken.*.numeric' => 'Fuel Taken must be a number.',
                'current_km.*.min' => 'Current KMs cannot be negative.',
                'fuel_taken.*.min' => 'Fuel Taken cannot be negative.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $currs = $request->input('current_km', []);
            $fuels = $request->input('fuel_taken', []);

            // The current-vs-previous KM check runs in the save loop below
            // against the database, not against posted values.
            $indexes = array_unique(array_merge(array_keys($currs), array_keys($fuels)));
            foreach ($indexes as $i) {
                $curr = $currs[$i] ?? null;
                $fuel = $fuels[$i] ?? null;

                $hasCurr = !(is_null($curr) || $curr === '');
                $hasFuel = !(is_null($fuel) || $fuel === '');

                // If one of current_km or fuel_taken is provided, the other is required
                if ($hasCurr && !$hasFuel) {
                    $validator->errors()->add("fuel_taken.$i", 'Fuel Taken is required when Current KMs is provided.');
                }
                if ($hasFuel && !$hasCurr) {
                    $validator->errors()->add("current_km.$i", 'Current KMs is required when Fuel Taken is provided.');
                }
            }
        });
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vehicleIds = $request->input('vehicle_id', []);
        $currentKms = $request->input('current_km', []);
        $fuelTakens = $request->input('fuel_taken', []);
        $reportDate = $request->report_date;

        // Guard against PHP max_input_vars truncation: the form reports how
        // many vehicle rows it rendered; if fewer arrived, refuse to save a
        // partial batch (same failure class as the attendance module fix).
        $expectedRows = (int) $request->input('row_count', 0);
        if ($expectedRows > 0 && count($vehicleIds) < $expectedRows) {
            return redirect()->back()->withInput()->withErrors([
                'report_date' => 'The submission arrived incomplete (' . count($vehicleIds) . ' of ' . $expectedRows
                    . ' vehicles received) — nothing was saved. Please ask the administrator to raise PHP max_input_vars.',
            ]);
        }

        $errors = [];
        $creates = [];
        $placeholderUpdates = [];
        $touchedVehicleIds = [];
        $enteredCount = 0;
        $carriedCount = 0;

        // Iterate by key: rows excluded on the client (hidden + empty under a
        // vehicle filter) leave gaps in the posted arrays.
        foreach ($vehicleIds as $i => $postedVehicleId) {
            $vehicleId = (int) $postedVehicleId;
            if ($vehicleId <= 0) {
                continue;
            }

            $currKm = $currentKms[$i] ?? null;
            $fuelTaken = $fuelTakens[$i] ?? null;

            $currEmpty = is_null($currKm) || $currKm === '';
            $fuelEmpty = is_null($fuelTaken) || $fuelTaken === '';
            $entered = !($currEmpty && $fuelEmpty);

            $lastReport = DailyFuelReport::where('vehicle_id', $vehicleId)
                ->where('report_date', '<', $reportDate)
                ->where('is_active', 1)
                ->orderBy('report_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $prevKmFromDB = $lastReport ? (float) $lastReport->current_km : 0.0;

            $existing = DailyFuelReport::where('vehicle_id', $vehicleId)
                ->where('report_date', $reportDate)
                ->where('is_active', 1)
                ->first();

            if (! $entered) {
                // Row left blank: carry the previous KMs forward as the day's
                // reading (vehicle assumed parked / no fuel taken) — but never
                // touch a record that already exists for this date, and skip
                // vehicles with no fuel history (nothing to carry forward).
                if ($existing || ! $lastReport) {
                    continue;
                }

                $creates[] = [
                    'vehicle_id' => $vehicleId,
                    'previous_km' => $prevKmFromDB,
                    'current_km' => $prevKmFromDB,
                    'mileage' => 0,
                    'fuel_taken' => 0,
                    'fuel_average' => 0,
                ];
                $touchedVehicleIds[] = $vehicleId;
                $carriedCount++;
                continue;
            }

            if ((float) $currKm < $prevKmFromDB) {
                $errors["current_km.$i"] = "Current KMs cannot be less than Previous KMs ($prevKmFromDB).";
                continue;
            }

            $mileage = (float) $currKm - $prevKmFromDB;
            $rowValues = [
                'vehicle_id' => $vehicleId,
                'previous_km' => $prevKmFromDB,
                'current_km' => $currKm,
                'mileage' => $mileage,
                'fuel_taken' => $fuelTaken,
                'fuel_average' => ((float) $fuelTaken > 0) ? round($mileage / (float) $fuelTaken, 1) : 0,
            ];

            if ($existing) {
                // A carried-forward placeholder (no fuel, no movement) may be
                // upgraded with real figures; a genuine earlier entry may not.
                $isPlaceholder = (float) ($existing->fuel_taken ?? 0) == 0.0
                    && (float) $existing->current_km == (float) $existing->previous_km;

                if ($isPlaceholder) {
                    $placeholderUpdates[] = ['id' => $existing->id] + $rowValues;
                    $touchedVehicleIds[] = $vehicleId;
                    $enteredCount++;
                } else {
                    $errors["vehicle_id.$i"] = 'Fuel entry already exists for this vehicle on selected date.';
                }
                continue;
            }

            $creates[] = $rowValues;
            $touchedVehicleIds[] = $vehicleId;
            $enteredCount++;
        }

        if (! empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        DB::transaction(function () use ($creates, $placeholderUpdates, $reportDate) {
            foreach ($creates as $row) {
                $dailyFuel = new DailyFuelReport();
                $dailyFuel->vehicle_id = $row['vehicle_id'];
                $dailyFuel->report_date = $reportDate;
                $dailyFuel->previous_km = $row['previous_km'];
                $dailyFuel->current_km = $row['current_km'];
                $dailyFuel->mileage = $row['mileage'];
                $dailyFuel->fuel_taken = $row['fuel_taken'];
                $dailyFuel->fuel_average = $row['fuel_average'];
                $dailyFuel->is_active = 1;
                $dailyFuel->save();
            }

            foreach ($placeholderUpdates as $row) {
                DailyFuelReport::where('id', $row['id'])->update([
                    'previous_km' => $row['previous_km'],
                    'current_km' => $row['current_km'],
                    'mileage' => $row['mileage'],
                    'fuel_taken' => $row['fuel_taken'],
                    'fuel_average' => $row['fuel_average'],
                ]);
            }
        });

        foreach (array_unique($touchedVehicleIds) as $vehicleId) {
            $this->recalculateVehicleReports($vehicleId);
        }

        $message = 'Daily Fuel saved successfully — ' . $enteredCount . ' entered';
        if ($carriedCount > 0) {
            $message .= ', ' . $carriedCount . ' carried forward from Previous KMs';
        }
        $message .= '.';

        return redirect()->route('dailyFuels.index')->with('success', $message);
    }

    public function edit(DailyFuelReport $dailyFuel)
    {
        return view('admin.dailyFuels.edit', compact('dailyFuel'));
    }

    public function update(Request $request, DailyFuelReport $dailyFuel)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'report_date' => 'required|date|before_or_equal:today',
                'current_km' => 'required|numeric|min:0',
                'fuel_taken' => 'required|numeric|min:0',
            ],
            [
                'report_date.required' => 'Report date is required.',
                'report_date.before_or_equal' => 'Report date cannot be in the future.',
                'current_km.required' => 'Current Km is required.',
                'current_km.numeric' => 'Current Km must be a number.',
                'fuel_taken.required' => 'Fuel Taken is required.',
                'fuel_taken.numeric' => 'Fuel Taken must be a number.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $prev = $request->previous_km;
            $curr = $request->current_km;
            if ($curr !== null && $curr !== '' && $prev !== null && $prev !== '') {
                if (is_numeric($curr) && is_numeric($prev)) {
                    if ((float) $curr < (float) $prev) {
                        $validator->errors()->add('current_km', 'Current KMs cannot be less than Previous KMs.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $vehicleId = $dailyFuel->vehicle_id;

        $dailyFuel->report_date = $request->report_date;
        $dailyFuel->current_km = $request->current_km;
        $dailyFuel->mileage = $request->mileage;
        $dailyFuel->fuel_taken = $request->fuel_taken;
        $dailyFuel->fuel_average = $request->fuel_average;
        $this->recalculateVehicleReports($vehicleId);
        $dailyFuel->save();

        return redirect()->route('dailyFuels.index')->with('success', 'Daily Fuel updated successfully.');
    }

    public function show(DailyFuelReport $dailyFuel)
    {
        return view('admin.dailyFuels.show', compact('dailyFuel'));
    }

    public function destroy(DailyFuelReport $dailyFuel)
    {
        $vehicleId = $dailyFuel->vehicle_id;

        $dailyFuel->delete();
        $this->recalculateVehicleReports($vehicleId);
        return redirect()
            ->route('dailyFuels.index')
            ->with('delete_msg', 'Daily Fuel deleted successfully.');
    }

    private function recalculateVehicleReports($vehicleId)
    {
        $records = DailyFuelReport::where('vehicle_id', $vehicleId)
            ->where('is_active', 1)
            ->orderBy('report_date')
            ->orderBy('id')
            ->get();

        $previousKm = 0;

        foreach ($records as $record) {
            $record->previous_km = $previousKm;

            $mileage = $record->current_km - $previousKm;
            $record->mileage = $mileage;

            $record->fuel_average = ($record->fuel_taken > 0)
                ? round($mileage / $record->fuel_taken, 1)
                : 0;

            $record->save();

            $previousKm = $record->current_km;
        }
    }
}
