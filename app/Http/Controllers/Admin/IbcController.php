<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Station;
use App\Models\IbcCenter;


use Illuminate\Http\Request;

class IbcController extends Controller
{
    public function index()
    {
    	$ibcCenters = IbcCenter::with(['station'])->where('is_active',1)->get();
    	return view('admin.ibcCenters.index',compact('ibcCenters'));
    }

    public function create()
    {
    	$serial_no = IbcCenter::GetSerialNumber();
        $stations = Station::where('is_active',1)->orderBy('area','ASC')->pluck('area','id');
        return view('admin.ibcCenters.create',compact('serial_no','stations'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
            	'station_id'	=>	'required',
            	'name'         	=>	'required',
            ],
            [
            	'station_id.required'	=>	'Station is required',
            	'name.required'        	=>	'IBC Center Name is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $ibcCenter = new IbcCenter();
        $ibcCenter->serial_no	=	$request->serial_no;
        $ibcCenter->station_id	=	$request->station_id;
        $ibcCenter->name		=	$request->name;
        $ibcCenter->save();

        return redirect()->route('admin.ibcCenters.index')->with('success', 'IBC Center created successfully.');
    }

    public function edit(IbcCenter $ibcCenter)
    {
        $stations = Station::where('is_active',1)->orderBy('area','ASC')->pluck('area','id');
    	return view('admin.ibcCenters.edit', compact('ibcCenter','stations'));
    }

    public function update(Request $request, IbcCenter $ibcCenter)
    {
        $validator = \Validator::make(
            $request->all(),
            [
            	'station_id'	=>	'required',
            	'name'         	=>	'required',
            ],
            [
            	'station_id.required'	=>	'Station is required',
            	'name.required'        	=>	'IBC Center Name is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $ibcCenter->station_id	=	$request->station_id;
        $ibcCenter->name		=	$request->name;
        $ibcCenter->save();

        return redirect()->route('admin.ibcCenters.index')->with('success', 'IBC Center updated successfully.');
    }

    public function show(IbcCenter $ibcCenter)
    {
        return view('admin.ibcCenters.show',compact('ibcCenter'));
    }

    public function destroy(IbcCenter $ibcCenter)
    {
        $ibcCenter->is_active = 0;
        $ibcCenter->save();

        return redirect()->route('admin.ibcCenters.index')->with('delete_msg', 'IBC Center deleted successfully.');
    }
}
