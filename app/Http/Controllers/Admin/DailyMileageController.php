<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyMileageReport;
use App\Models\Vehicle;
use App\Models\MileageStatus;
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

        if ($request->filled('from_date')) {
            $query->whereDate('report_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('report_date', '<=', $request->to_date);
        }
        
        $dailyMileages = $query->where('is_active',1)
            ->whereRaw('MONTH(report_date) = MONTH(CURRENT_DATE())')
            ->whereRaw('YEAR(report_date) = YEAR(CURRENT_DATE())')
            ->whereHas('vehicle', function ($query) {
                $query->where('is_active', 1);
            })
            ->with('vehicle')->orderby('id','DESC')
            ->get();
        $vehicles = Vehicle::where('is_active',1)->get();
        
        return view('admin.dailyMileages.index', compact('dailyMileages','vehicles'));
    }

    public function create(){
        $vehicleData = array();
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->select('*')->get();
        foreach($vehicles as $vehicle){
            $previousRecord = DailyMileageReport::where('vehicle_id',$vehicle->id)
                ->where('is_active',1)
                ->orderBy('id','DESC')
                ->select('*')
                ->first();

            $previous_km = ($previousRecord) ? $previousRecord->current_km : $vehicle->kilometer;

            $vehicleData[] = array(
                'vehicle_id'    =>  $vehicle->id,
                'vehicle_no'    =>  $vehicle->vehicle_no,
                'previous_km'   =>  $previous_km
            );
        }

        return view('admin.dailyMileages.create',compact('vehicles','vehicleData'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'current_km'    => 'array',
                'current_km.*'  => 'required|numeric|min:0'
            ],
            [
                'current_km.*.required'     =>  'Current km is required'
            ]
        );
        
        $validator->after(function ($validator) use ($request) {
            $previous_kms = $request->previous_km;
            $current_kms = $request->current_km;
            foreach ($current_kms as $index => $current_km){
                $previous_km = $previous_kms[$index] ?? 0;

                if($current_km < $previous_km){
                    $validator->errors()->add("current_km.$index", "Current KM must be greater than Previous KM.");
                }
            
            }
        });
        
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vehicle_ids    = $request->vehicle_id;
        $mileages       = $request->mileage;
        $previous_kms   = $request->previous_km;
        $current_kms    = $request->current_km;

        foreach ($vehicle_ids as $index => $vehicle_id) {
            DailyMileageReport::Create(
                [
                    'vehicle_id'    =>  $vehicle_id,
                    'report_date'   =>  Carbon::today()->toDateString(), //'2025-07-16',
                    'mileage'       =>  $mileages[$index] ?? 0,
                    'previous_km'   =>  $previous_kms[$index] ?? 0,
                    'current_km'    =>  $current_kms[$index] ?? 0,
                ]
            );
            
        }

        return redirect()->route('admin.dailyMileages.index')->with('success', 'Daily Mileage created successfully.');
    }

    public function edit(DailyMileageReport $dailyMileage)
    {
        
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        //$mileages = MileageStatus::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');

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

        $dailyMileage->report_date = $request->report_date;
        $dailyMileage->mileage = $request->mileage;
        $dailyMileage->previous_km = $request->previous_km;
        $dailyMileage->current_km = $request->current_km;
        $dailyMileage->save();

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
