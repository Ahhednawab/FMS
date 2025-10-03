<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyFuelReport;
use App\Models\Vehicle;
use App\Models\Destination;
use App\Models\FuelStation;
use App\Models\Station;

use Illuminate\Http\Request;

class DailyFuelController extends Controller
{
    public function index(){
        $dailyFuels = DailyFuelReport::where('is_active', 1)
            ->whereHas('vehicle', function ($query) {
                $query->where('is_active', 1);
            })
            ->with(['vehicle.station'])->orderby('id','DESC')
            ->with('vehicle')
            ->get();

        return view('admin.dailyFuels.index', compact('dailyFuels'));
    }

    public function create(Request $request){
        
        $vehicles = Vehicle::with('station');
        $vehicles = $vehicles->where('is_active', 1);
        if (isset($request->station_id)) {
            $vehicles = $vehicles->where('station_id', $request->station_id);
        }
        $vehicles = $vehicles->orderBy(Station::select('area')->whereColumn('stations.id', 'vehicles.station_id')->limit(1));
        $vehicles = $vehicles->orderBy('vehicle_no');
        $vehicles = $vehicles->get();

        $vehicleData = array();

        foreach($vehicles as $vehicle){
            $previousRecord = DailyFuelReport::where('vehicle_id',$vehicle->id)
                ->where('is_active',1)
                ->orderBy('id','DESC')
                ->select('*')
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
        
        return view('admin.dailyFuels.create',compact('vehicles','vehicleData','stations','selectedStation'));
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

        $validator->after(function($validator) use ($request) {
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
                if ($hasCurr && $prev !== null && $prev !== '' ) {
                    if (is_numeric($curr) && is_numeric($prev)) {
                        if ((float)$curr > (float)$prev) {
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
            $prevKm      = $previousKms[$i] ?? null;
            $currKm      = $currentKms[$i] ?? null;
            $mileage     = $mileages[$i] ?? null;
            $fuelTaken   = $fuelTakens[$i] ?? null;
            $fuelAverage = $fuelAverages[$i] ?? null;

            $currEmpty = is_null($currKm) || $currKm === '';
            $fuelEmpty = is_null($fuelTaken) || $fuelTaken === '';

            if ($currEmpty && $fuelEmpty) {
                continue;
            }

            $dailyFuel = new DailyFuelReport();
            $dailyFuel->vehicle_id    = $vehicleId;
            $dailyFuel->report_date   = $request->report_date;
            $dailyFuel->previous_km   = $prevKm;
            $dailyFuel->current_km    = $currKm;
            $dailyFuel->mileage       = $mileage;
            $dailyFuel->fuel_taken    = $fuelTaken;
            $dailyFuel->fuel_average  = $fuelAverage;
            $dailyFuel->is_active     = 1;
            $dailyFuel->save();
        }

        return redirect()->route('admin.dailyFuels.index')->with('success', 'Daily Fuel created successfully.');
    }

    public function edit(DailyFuelReport $dailyFuel)
    {
        return view('admin.dailyFuels.edit',compact('dailyFuel'));
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

        $validator->after(function($validator) use ($request) {
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
        
        $dailyFuel->report_date     =   $request->report_date;
        $dailyFuel->current_km      =   $request->current_km;
        $dailyFuel->mileage         =   $request->mileage;
        $dailyFuel->fuel_taken      =   $request->fuel_taken;
        $dailyFuel->fuel_average    =   $request->fuel_average;
        $dailyFuel->save();

        return redirect()->route('admin.dailyFuels.index')->with('success', 'Daily Fuel updated successfully.');
    }

    public function show(DailyFuelReport $dailyFuel)
    {
        return view('admin.dailyFuels.show', compact('dailyFuel'));
    }

    public function destroy(DailyFuelReport $dailyFuel)
    {
        $dailyFuel->is_active = 0;
        $dailyFuel->save();
        
        return redirect()->route('admin.dailyFuels.index')->with('delete_msg', 'Daily Fuel deleted successfully.');
    }
}
