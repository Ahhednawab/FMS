<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\User;
use App\Models\Country;
use App\Models\Station;
use App\Models\Warehouse;
use App\Traits\DraftTrait;
use Illuminate\Http\Request;
use App\Models\WarehouseType;
use App\Http\Controllers\Controller;

class WarehouseController extends Controller
{
    use DraftTrait;
    public function index()
    {
        $warehouses = Warehouse::with(['manager', 'station'])->where('is_active', 1)->latest()->get();
        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function create(Request $request)
    {
        $serial_no = Warehouse::generateSerialNo();
        $managers = User::where('is_active', 1)->where('designation_id', 3)->where('is_active', 1)->orderBy('name', 'ASC')->pluck('name', 'id');
        $stations = Station::where('is_active', 1)->orderBy('area', 'ASC')->pluck('area', 'id');

        $types = ["master", "sub"];

        $draftInfo = $this->getDraftDataForView($request, 'warehouses');

        return view('admin.warehouses.create', compact('serial_no', 'managers', 'stations', 'types') + $draftInfo);
    }

    public function store(Request $request)
    {
        // Handle draft saving (your existing logic)
        if ($this->handleDraftSave($request, 'warehouses')) {
            return back()->with('success', 'Draft saved successfully!');
        }
        // dd("hwre");

        $valid = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:master,sub',
            'manager_id'  => 'required|exists:users,id',
            'station_id'  => 'required|exists:stations,id',
        ], [
            'name.required'       => 'Warehouse name is required.',
            'type.required'       => 'Warehouse type is required.',
            'manager_id.required' => 'Please select a manager.',
            'station_id.required' => 'Please select a station.',
            'manager_id.exists'   => 'Selected manager is invalid.',
            'station_id.exists'   => 'Selected station is invalid.',
        ]);

        // dd($valid);

        $warehouse = Warehouse::create([
            'serial_no'   => (new Warehouse)->generateSerialNo(), // Auto-generated
            'name'        => $request->name,
            'type'        => $request->type,
            'manager_id'  => $request->manager_id,
            'station_id'  => $request->station_id,
            'is_active'   => true,
        ]);

        // Delete draft after successful save
        $this->deleteDraftAfterSuccess($request, 'warehouses');

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Warehouse created successfully!');
    }

    public function show($id)
    {
        $warehouse = Warehouse::with('country', 'city')->findOrFail($id);
        return view('admin.warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        $managers = User::where('is_active', 1)->where('designation_id', 3)->where('is_active', 1)->orderBy('name', 'ASC')->pluck('name', 'id');
        $stations = Station::where('is_active', 1)->orderBy('area', 'ASC')->pluck('area', 'id');
        return view('admin.warehouses.edit', compact('warehouse', 'stations', 'managers'));
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
