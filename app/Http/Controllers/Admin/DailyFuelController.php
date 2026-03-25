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
        if (isset($request->station_id)) {
            $vehicles = $vehicles->where('station_id', $request->station_id);
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
                'vehicle_id'    =>  $vehicle->id,
                'station'       =>  $vehicle->station->area,
                'vehicle_no'    =>  $vehicle->vehicle_no,
                'previous_km'   =>  $previous_km
            );
        }

        $stations = Vehicle::with('station');
        $stations = $stations->where('is_active', 1);
        $stations = $stations->get();
        $stations = $stations->pluck('station.area', 'station_id');
        $stations = $stations->sort();
        $stations = $stations->unique();
        $stations = $stations->toArray();

        $selectedStation = $request->station_id ?? '';

        return view('admin.dailyFuels.create', compact('vehicles', 'vehicleData', 'stations', 'selectedStation', 'selectedDate'));
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
                'vehicle_id'  => $vehicle->id,
                'previous_km' => $previous_km,
                'current_km'  => $existingFuelReport ? $existingFuelReport->current_km : '',
                'fuel_taken'  => $existingFuelReport ? $existingFuelReport->fuel_taken : '',
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'report_date'           => 'required|date|before_or_equal:today',
                'current_km'            => 'array',
                'fuel_taken'            => 'array',
                'current_km.*'          => 'nullable|numeric|min:0',
                'fuel_taken.*'          => 'nullable|numeric|min:0',
            ],
            [
                'report_date.required'          => 'Report date is required.',
                'report_date.before_or_equal'   => 'Report date cannot be in the future.',
                'current_km.*.numeric'          => 'Current KMs must be a number.',
                'fuel_taken.*.numeric'          => 'Fuel Taken must be a number.',
                'current_km.*.min'              => 'Current KMs cannot be negative.',
                'fuel_taken.*.min'              => 'Fuel Taken cannot be negative.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $prevs = $request->input('previous_km', []);
            $currs = $request->input('current_km', []);
            $fuels = $request->input('fuel_taken', []);

            $max = max(count($prevs), count($currs), count($fuels));
            for ($i = 0; $i < $max; $i++) {
                $prev = $prevs[$i] ?? null;
                $curr = $currs[$i] ?? null;
                $fuel = $fuels[$i] ?? null;

                $hasCurr = !(is_null($curr) || $curr === '');
                $hasFuel = !(is_null($fuel) || $fuel === '');

                // current_km must not exceed previous_km
                if ($hasCurr && $prev !== null && $prev !== '') {
                    if (is_numeric($curr) && is_numeric($prev)) {
                        if ((float)$curr < (float)$prev) {
                            $validator->errors()->add("current_km.$i", 'Current KMs cannot be greater than Previous KMs.');
                        }
                    }
                }

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

        $vehicleIds   = $request->input('vehicle_id', []);
        $previousKms  = $request->input('previous_km', []);
        $currentKms   = $request->input('current_km', []);
        $mileages     = $request->input('mileage', []);
        $fuelTakens   = $request->input('fuel_taken', []);
        $fuelAverages = $request->input('fuel_average', []);

        $count = count($vehicleIds);
        for ($i = 0; $i < $count; $i++) {
            $vehicleId   = $vehicleIds[$i] ?? null;
            // $prevKm      = $previousKms[$i] ?? null;
            $currKm      = $currentKms[$i] ?? null;
            $mileage     = $mileages[$i] ?? null;
            $fuelTaken   = $fuelTakens[$i] ?? null;
            $fuelAverage = $fuelAverages[$i] ?? null;

            $currEmpty = is_null($currKm) || $currKm === '';
            $fuelEmpty = is_null($fuelTaken) || $fuelTaken === '';

            if ($currEmpty && $fuelEmpty) {
                continue;
            }

            // ✅ GET PREVIOUS KM BASED ON REPORT DATE
            $lastReport = DailyFuelReport::where('vehicle_id', $vehicleId)
                ->where('report_date', '<', $request->report_date)
                ->where('is_active', 1)
                ->orderBy('report_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $prevKmFromDB = $lastReport ? $lastReport->current_km : 0;


            $exists = DailyFuelReport::where('vehicle_id', $vehicleId)
                ->where('report_date', $request->report_date)
                ->where('is_active', 1)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withErrors([
                        "vehicle_id.$i" => "Fuel entry already exists for this vehicle on selected date."
                    ])
                    ->withInput();
            }


            $dailyFuel = new DailyFuelReport();
            $dailyFuel->vehicle_id    = $vehicleId;
            $dailyFuel->report_date   = $request->report_date;
            $dailyFuel->previous_km   = $prevKmFromDB;
            $dailyFuel->current_km    = $currKm;
            $dailyFuel->mileage       = $mileage;
            $dailyFuel->fuel_taken    = $fuelTaken;
            $dailyFuel->fuel_average  = $fuelAverage;
            $dailyFuel->is_active     = 1;
            $dailyFuel->save();
        }
foreach ($vehicleIds as $vehicleId) {
    $this->recalculateVehicleReports($vehicleId);
}
        return redirect()->route('dailyFuels.index')->with('success', 'Daily Fuel created successfully.');
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
                'report_date'   =>  'required|date|before_or_equal:today',
                'current_km'    =>  'required|numeric|min:0',
                'fuel_taken'    =>  'required|numeric|min:0',
            ],
            [
                'report_date.required'          =>  'Report date is required.',
                'report_date.before_or_equal'   =>  'Report date cannot be in the future.',
                'current_km.required'           =>  'Current Km is required.',
                'current_km.numeric'            =>  'Current Km must be a number.',
                'fuel_taken.required'           =>  'Fuel Taken is required.',
                'fuel_taken.numeric'            =>  'Fuel Taken must be a number.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $prev = $request->previous_km;
            $curr = $request->current_km;
            if ($curr !== null && $curr !== '' && $prev !== null && $prev !== '') {
                if (is_numeric($curr) && is_numeric($prev)) {
                    if ((float)$curr < (float)$prev) {
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

        $dailyFuel->report_date     =   $request->report_date;
        $dailyFuel->current_km      =   $request->current_km;
        $dailyFuel->mileage         =   $request->mileage;
        $dailyFuel->fuel_taken      =   $request->fuel_taken;
        $dailyFuel->fuel_average    =   $request->fuel_average;
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
