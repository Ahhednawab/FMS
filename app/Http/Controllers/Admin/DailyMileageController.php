<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyMileageReport;
use App\Models\Vehicle;
use App\Models\MileageStatus;
use App\Models\Station;
use Carbon\Carbon;


class DailyMileageController extends Controller
{
    public function index(Request $request){

        $query = DailyMileageReport::query();
        
        if ($request->filled('vehicle_id')) {
            $query->whereHas('vehicle', function ($q) use ($request) {
                $q->where('vehicle_id', $request->vehicle_id);
            });
        }

        $fromDate = $request->filled('from_date') ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->filled('to_date') ? Carbon::parse($request->to_date) : Carbon::now();

        $query->whereBetween('report_date', [
            $fromDate->toDateString(),
            $toDate->toDateString(),
        ]);
        
        $dailyMileages = $query->where('is_active',1)
            ->whereHas('vehicle', function ($query) {
                $query->where('is_active', 1);
            })
            ->with(['vehicle.station'])->orderby('id','DESC')
            ->get();

        $vehicles = Vehicle::where('is_active',1)->get();

        return view('admin.dailyMileages.index', compact('dailyMileages','vehicles'));
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
            $previousRecord = DailyMileageReport::where('vehicle_id',$vehicle->id)
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
        
        return view('admin.dailyMileages.create',
            compact('vehicles','vehicleData','stations','selectedStation'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'current_km'    => 'array',
                'current_km.*'  => 'nullable|numeric|min:0',                
                'report_date' => 'required|date|before_or_equal:today',
            ],
            [
                'report_date.required'        =>  'Date is required',
                'report_date.date'            =>  'Date must be a valid date',
                'report_date.before_or_equal' =>  'Date cannot be greater than today',
            ]
        );

        
        $validator->after(function ($validator) use ($request) {
            $previous_kms = $request->previous_km;
            $current_kms = $request->current_km;
            $vehicle_ids = $request->vehicle_id;
            $report_date = $request->report_date;

            foreach ($current_kms as $index => $current_km){
                $previous_km = $previous_kms[$index] ?? 0;

                if($current_km < $previous_km && isset($current_km)){
                    $validator->errors()->add("current_km.$index", "Current KM must be greater than Previous KM.");
                }

                $vehicle_id = $vehicle_ids[$index] ?? null;
                if ($vehicle_id && isset($current_km)) {
                    $last = DailyMileageReport::where('vehicle_id', $vehicle_id)
                        ->where('is_active', 1)
                        ->orderBy('report_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($last) {
                        if ($report_date && Carbon::parse($report_date)->lt(Carbon::parse($last->report_date))) {
                            $lastDateFormatted = Carbon::parse($last->report_date)->format('d-M-Y');
                            $validator->errors()->add("current_km.$index", "Report date cannot be earlier than last date ($lastDateFormatted) for this vehicle.");
                        }

                        if ($current_km < $last->current_km) {
                            $validator->errors()->add("current_km.$index", "Current KM for this vehicle cannot be less than last recorded KM ({$last->current_km}).");
                        }
                    }
                }
            }
        });
        
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vehicle_ids    = $request->vehicle_id;
        $report_date    = $request->report_date;
        $mileages       = $request->mileage;
        $previous_kms   = $request->previous_km;
        $current_kms    = $request->current_km;

        foreach ($vehicle_ids as $index => $vehicle_id) {
            if (isset($current_kms[$index])) {
                DailyMileageReport::Create([
                    'vehicle_id'    =>  $vehicle_id,
                    'report_date'   =>  $report_date,
                    'mileage'       =>  ($current_kms[$index] - $previous_kms[$index]),
                    'previous_km'   =>  $previous_kms[$index],
                    'current_km'    =>  $current_kms[$index],
                ]);
            }
        }
        
        return redirect()->route('admin.dailyMileages.index')->with('success', 'Daily Mileage created successfully.');
    }

    public function edit(DailyMileageReport $dailyMileage)
    {        
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
    
        $months = [];

            for ($i = 0; $i <= 3; $i++) {
            $months[] = date("F", strtotime("-$i months"));
        }

        return view('admin.dailyMileages.edit',compact('dailyMileage','months','vehicles'));
    }

    public function update(Request $request, DailyMileageReport $dailyMileage)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'report_date' => 'required',
                'current_km' => 'required|numeric',
            ],
            [
                'report_date.required'                 =>  'Date is required',
                'current_km.required'     =>  'Current km is required'
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $previous_km = $request->previous_km;
            $current_km = $request->current_km;

            if($current_km < $previous_km){
                $validator->errors()->add("current_km", "Current KM must be greater than Previous KM.");
            }
        
        });

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dailyMileage->report_date  = $request->report_date;
        $dailyMileage->mileage      = $request->mileage;
        $dailyMileage->previous_km = $request->previous_km;
        $dailyMileage->current_km = $request->current_km;
        $dailyMileage->save();

        $succeded_record = DailyMileageReport::where('vehicle_id',$dailyMileage->vehicle_id)->where('id','>',$dailyMileage->id)->first();

        if (isset($succeded_record)) {
            // echo "if";return;
            $succeded_record->previous_km =  $request->current_km;
            $succeded_record->mileage = ($succeded_record->current_km - $request->current_km);
            $succeded_record->save();
        }
        // echo "<br><br>out";return;

        return redirect()->route('admin.dailyMileages.index')->with('success', 'Daily Mileage updated successfully.');
    }

    public function show(DailyMileageReport $dailyMileage)
    {
        return view('admin.dailyMileages.show', compact('dailyMileage'));
    }

    public function destroy(DailyMileageReport $dailyMileage)
    {
        $dailyMileage->is_active = 0;
        $dailyMileage->save();
        return redirect()->route('admin.dailyMileages.index')->with('delete_msg', 'Daily Mileage deleted successfully.');
    }
}
