<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrackerMileageReport;
use App\Models\Vehicle;
use App\Models\Days;
use App\Models\IbcCenter;


class TrackerMileageController extends Controller
{
    public function index(){
        $trackerMileages = TrackerMileageReport::with(['vehicle','days','ibcCenter'])->where('is_active',1)->orderby('id','DESC')->get();
        
        return view('admin.trackerMileages.index', compact('trackerMileages'));
    }

    public function create(){
        $serial_no = TrackerMileageReport::GetSerialNumber();
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        $days = Days::where('is_active',1)->pluck('name','id');
        $ibc_center = IbcCenter::where('is_active',1)->orderby('name','ASC')->pluck('name','id');
        
        return view('admin.trackerMileages.create',compact('serial_no','days','ibc_center','vehicles'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'day' => 'required',
                'date' => 'required',
                'akpl' => 'required',
                'ibc_center' => 'required',
                'before_peak_one_hour' => 'required|numeric',
                'before_peak_two_hour' => 'required|numeric',
                'kms_driven_peak' => 'required|numeric',
                'kms_driven_off_peak' => 'required|numeric',
                'total_kms_in_a_day' => 'required|numeric',
                'after_peak_one_hour' => 'required|numeric',
                'after_peak_two_hour' => 'required|numeric',
                'difference' => 'required|numeric',
                'odo_meter' => 'required|numeric',
            ],
            [
                'vehicle_id.required'           =>  'Vehicle No is required',
                'day.required'                  =>  'Day is required',
                'date.required'                 =>  'Date is required',
                'akpl.required'                 =>  'AKPL is required',
                'ibc_center.required'           =>  'IBC/Center is required',
                'before_peak_one_hour.required' =>  'Before Peak 01 Hour is required',
                'before_peak_two_hour.required' =>  'Before Peak 02 Hour is required',
                'kms_driven_peak.required'      =>  'KMS Driven Peak is required',
                'kms_driven_off_peak.required'  =>  'KMS Driven Off Peak is required',
                'total_kms_in_a_day.required'   =>  'Total KMs In a day is required',
                'after_peak_one_hour.required'  =>  'After Peak 01 Hour is required',
                'after_peak_two_hour.required'  =>  'After Peak 02 Hour is required',
                'difference.required'           =>  'Difference is required',
                'odo_meter.required'            =>  'Odo Meter is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $trackerMilage = new TrackerMileageReport();
        $trackerMilage->serial_no = $request->serial_no;
        $trackerMilage->vehicle_id = $request->vehicle_id;
        $trackerMilage->date = $request->date;
        $trackerMilage->day = $request->day;
        $trackerMilage->akpl = $request->akpl;
        $trackerMilage->ibc_center = $request->ibc_center;
        $trackerMilage->before_peak_one_hour = $request->before_peak_one_hour;
        $trackerMilage->before_peak_two_hour = $request->before_peak_two_hour;
        $trackerMilage->kms_driven_peak = $request->kms_driven_peak;
        $trackerMilage->kms_driven_off_peak = $request->kms_driven_off_peak;
        $trackerMilage->total_kms_in_a_day = $request->total_kms_in_a_day;
        $trackerMilage->after_peak_one_hour = $request->after_peak_one_hour;
        $trackerMilage->after_peak_two_hour = $request->after_peak_two_hour;
        $trackerMilage->difference = $request->difference;
        $trackerMilage->odo_meter = $request->odo_meter;
        $trackerMilage->save();

        return redirect()->route('admin.trackerMileages.index')->with('success', 'Tracker Mileage created successfully.');
    }

    public function edit($id)
    {
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        $days = Days::where('is_active',1)->pluck('name','id');
        $ibc_center = IbcCenter::where('is_active',1)->orderby('name','ASC')->pluck('name','id');
        $trackerMileage = TrackerMileageReport::find($id);

        return view('admin.trackerMileages.edit', compact('trackerMileage','days','ibc_center','vehicles'));
    }

    public function update(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'day' => 'required',
                'date' => 'required',
                'akpl' => 'required',
                'ibc_center' => 'required',
                'before_peak_one_hour' => 'required|numeric',
                'before_peak_two_hour' => 'required|numeric',
                'kms_driven_peak' => 'required|numeric',
                'kms_driven_off_peak' => 'required|numeric',
                'total_kms_in_a_day' => 'required|numeric',
                'after_peak_one_hour' => 'required|numeric',
                'after_peak_two_hour' => 'required|numeric',
                'difference' => 'required|numeric',
                'odo_meter' => 'required|numeric',
            ],
            [
                'vehicle_id.required'           =>  'Vehicle No is required',
                'day.required'                  =>  'Day is required',
                'date.required'                 =>  'Date is required',
                'akpl.required'                 =>  'AKPL is required',
                'ibc_center.required'           =>  'IBC/Center is required',
                'before_peak_one_hour.required' =>  'Before Peak 01 Hour is required',
                'before_peak_two_hour.required' =>  'Before Peak 02 Hour is required',
                'kms_driven_peak.required'      =>  'KMS Driven Peak is required',
                'kms_driven_off_peak.required'  =>  'KMS Driven Off Peak is required',
                'total_kms_in_a_day.required'   =>  'Total KMs In a day is required',
                'after_peak_one_hour.required'  =>  'After Peak 01 Hour is required',
                'after_peak_two_hour.required'  =>  'After Peak 02 Hour is required',
                'difference.required'           =>  'Difference is required',
                'odo_meter.required'            =>  'Odo Meter is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $trackerMilage = TrackerMileageReport::find($id);
        $trackerMilage->vehicle_id = $request->vehicle_id;
        $trackerMilage->date = $request->date;
        $trackerMilage->day = $request->day;
        $trackerMilage->akpl = $request->akpl;
        $trackerMilage->ibc_center = $request->ibc_center;
        $trackerMilage->before_peak_one_hour = $request->before_peak_one_hour;
        $trackerMilage->before_peak_two_hour = $request->before_peak_two_hour;
        $trackerMilage->kms_driven_peak = $request->kms_driven_peak;
        $trackerMilage->kms_driven_off_peak = $request->kms_driven_off_peak;
        $trackerMilage->total_kms_in_a_day = $request->total_kms_in_a_day;
        $trackerMilage->after_peak_one_hour = $request->after_peak_one_hour;
        $trackerMilage->after_peak_two_hour = $request->after_peak_two_hour;
        $trackerMilage->difference = $request->difference;
        $trackerMilage->odo_meter = $request->odo_meter;
        $trackerMilage->save();

        return redirect()->route('admin.trackerMileages.index')->with('success', 'Tracker Mileage updated successfully.');
    }

    public function show($id)
    {
        $trackerMilage = TrackerMileageReport::with(['vehicle','days','ibcCenter'])->where('id',$id)->first();
        return view('admin.trackerMileages.show', compact('trackerMilage'));
    }

    public function destroy($id)
    {
        $trackerMilage = TrackerMileageReport::find($id);
        $trackerMilage->is_active = 0;
        $trackerMilage->save();
        
        return redirect()->route('admin.trackerMileages.index')->with('delete_msg', 'Tracker Mileage deleted successfully.');
    }
}
