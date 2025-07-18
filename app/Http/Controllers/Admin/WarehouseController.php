<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Country;
use App\Models\City;
use App\Models\User;
use App\Models\Station;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with(['manager','station'])->where('is_active',1)->latest()->get();
        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $serial_no = Warehouse::GetSerialNumber();        
        $managers = User::where('is_active',1)->where('designation_id',3)->where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $stations = Station::where('is_active',1)->orderBy('area','ASC')->pluck('area','id');

        return view('admin.warehouses.create', compact('serial_no','managers','stations'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name'          =>  'required|string|max:255',
                'manager_id'    =>  'required',
                'station_id'    =>  'required',
            ],
            [
                'name.required'         =>  'Warehouse Name is required',
                'manager_id.required'   =>  'Warehouse Manager is required',
                'station_id.required'   =>  'Station is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $warehouse = new Warehouse();
        $warehouse->serial_no   =   $request->serial_no;
        $warehouse->name        =   $request->name;
        $warehouse->manager_id  =   $request->manager_id;
        $warehouse->station_id  =   $request->station_id;
        $warehouse->save();
        
        return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    public function show($id)
    {
        $warehouse = Warehouse::with('country', 'city')->findOrFail($id);
        return view('admin.warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        $managers = User::where('is_active',1)->where('designation_id',3)->where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $stations = Station::where('is_active',1)->orderBy('area','ASC')->pluck('area','id');
        return view('admin.warehouses.edit', compact('warehouse', 'stations','managers'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name'          =>  'required|string|max:255',
                'manager_id'    =>  'required',
                'station_id'    =>  'required',
            ],
            [
                'name.required'         =>  'Warehouse Name is required',
                'manager_id.required'   =>  'Warehouse Manager is required',
                'station_id.required'   =>  'Station is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $warehouse->name        =   $request->name;
        $warehouse->manager_id  =   $request->manager_id;
        $warehouse->station_id  =   $request->station_id;
        $warehouse->save();

        return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->is_active = 0;
        $warehouse->save();

        return redirect()->route('admin.warehouses.index')->with('delete_msg', 'Warehouse deleted successfully.');
    }
}
