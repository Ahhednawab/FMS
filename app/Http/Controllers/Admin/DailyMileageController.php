<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyMileageReport;
use App\Models\Vehicle;
use App\Models\MileageStatus;


class DailyMileageController extends Controller
{
    public function index(){
        $dailyMileages = DailyMileageReport::with(['vehicle','mileageStatus'])->where('is_active',1)->orderby('id','DESC')->get();
        
        return view('admin.dailyMileages.index', compact('dailyMileages'));
    }

    public function create(){
        $serial_no = DailyMileageReport::GetSerialNumber();
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        $mileages = MileageStatus::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        
        $months = [];

        for ($i = 0; $i <= 3; $i++) {
            $months[] = date("F", strtotime("-$i months"));
        }        

        return view('admin.dailyMileages.create',compact('serial_no','mileages','months','vehicles'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'location' => 'required',
                'remarks' => 'required',
                'date' => 'required',
                'mileage' => 'required',
                'last_third_month_km' => 'required|numeric',
                'last_second_month_km' => 'required|numeric',
                'last_month_km' => 'required|numeric',
                'current_month_km' => 'required|numeric',
            ],
            [
                'vehicle_id.required'           =>  'Vehicle No is required',
                'location.required'             =>  'Location is required',
                'remarks.required'              =>  'Remarks is required',
                'date.required'                 =>  'Date is required',
                'mileage.required'              =>  'Mileage is required',
                'last_third_month_km.required'  =>  'Field is required',
                'last_third_month_km.numeric'  =>  'Field must be a number',
                'last_second_month_km.required' =>  'Field is required',
                'last_second_month_km.numeric' =>  'Field must be a number',
                'last_month_km.required'        =>  'Field is required',
                'last_month_km.numeric'        =>  'Field must be a number',
                'current_month_km.required'     =>  'Field is required',
                'current_month_km.numeric'     =>  'Field must be a number',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dailyMileage = new DailyMileageReport();
        $dailyMileage->serial_no = $request->serial_no;
        $dailyMileage->vehicle_id = $request->vehicle_id;
        $dailyMileage->location = $request->location;
        $dailyMileage->remarks = $request->remarks;
        $dailyMileage->date = $request->date;
        $dailyMileage->mileage = $request->mileage;
        $dailyMileage->last_third_month_km = $request->last_third_month_km;
        $dailyMileage->last_second_month_km = $request->last_second_month_km;
        $dailyMileage->last_month_km = $request->last_month_km;
        $dailyMileage->current_month_km = $request->current_month_km;
        $dailyMileage->save();

        return redirect()->route('admin.dailyMileages.index')->with('success', 'Daily Mileage created successfully.');
    }

    public function edit(DailyMileageReport $dailyMileage)
    {
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        $mileages = MileageStatus::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');

        $months = [];

        for ($i = 0; $i <= 3; $i++) {
            $months[] = date("F", strtotime("-$i months"));
        }

        return view('admin.dailyMileages.edit',compact('dailyMileage','mileages','months','vehicles'));
    }

    public function update(Request $request, DailyMileageReport $dailyMileage)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'location' => 'required',
                'remarks' => 'required',
                'date' => 'required',
                'mileage' => 'required',
                'last_third_month_km' => 'required|numeric',
                'last_second_month_km' => 'required|numeric',
                'last_month_km' => 'required|numeric',
                'current_month_km' => 'required|numeric',
            ],
            [
                'vehicle_id.required'           =>  'Vehicle No is required',
                'location.required'             =>  'Location is required',
                'remarks.required'              =>  'Remarks is required',
                'date.required'                 =>  'Date is required',
                'mileage.required'              =>  'Mileage is required',
                'last_third_month_km.required'  =>  'Field is required',
                'last_third_month_km.numeric'  =>  'Field must be a number',
                'last_second_month_km.required' =>  'Field is required',
                'last_second_month_km.numeric' =>  'Field must be a number',
                'last_month_km.required'        =>  'Field is required',
                'last_month_km.numeric'        =>  'Field must be a number',
                'current_month_km.required'     =>  'Field is required',
                'current_month_km.numeric'     =>  'Field must be a number',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dailyMileage->vehicle_id = $request->vehicle_id;
        $dailyMileage->location = $request->location;
        $dailyMileage->remarks = $request->remarks;
        $dailyMileage->date = $request->date;
        $dailyMileage->mileage = $request->mileage;
        $dailyMileage->last_third_month_km = $request->last_third_month_km;
        $dailyMileage->last_second_month_km = $request->last_second_month_km;
        $dailyMileage->last_month_km = $request->last_month_km;
        $dailyMileage->current_month_km = $request->current_month_km;
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
