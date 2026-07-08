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
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    use DraftTrait;
    public function __construct()
    {

        if (!auth()->user()->hasPermission('warehouses')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    public function index(Request $request)
    {
        $role_slug = $request->get('roleSlug');
        $warehouses = Warehouse::with(['manager', 'station'])->where('is_active', 1)->latest()->get();
        $masterWarehouse = $warehouses->firstWhere('is_master', true);
        return view('admin.warehouses.index', compact('warehouses', 'role_slug', 'masterWarehouse'));
    }

    public function create(Request $request)
    {

        $role_slug = $request->get('roleSlug');

        $serial_no = Warehouse::generateSerialNo();
        $managers = User::where('is_active', 1)->where('designation_id', 3)->where('is_active', 1)->orderBy('name', 'ASC')->pluck('name', 'id');
        $stations = Station::where('is_active', 1)->orderBy('area', 'ASC')->pluck('area', 'id');

        $masterWarehouse = Warehouse::master()->first();

        $draftInfo = $this->getDraftDataForView($request, 'warehouses');

        return view('admin.warehouses.create', compact('serial_no', 'managers', 'stations', 'masterWarehouse', 'role_slug') + $draftInfo);
    }

    public function store(Request $request)
    {

        // Handle draft saving (your existing logic)
        if ($this->handleDraftSave($request, 'warehouses')) {
            return back()->with('success', 'Draft saved successfully!');
        }

        $valid = $request->validate([
            'name'        => 'required|string|max:255',
            'is_master'   => 'nullable|boolean',
            'manager_id'  => 'required|exists:users,id',
            'station_id'  => 'required|exists:stations,id',
        ], [
            'name.required'       => 'Warehouse name is required.',
            'manager_id.required' => 'Please select a manager.',
            'station_id.required' => 'Please select a station.',
            'manager_id.exists'   => 'Selected manager is invalid.',
            'station_id.exists'   => 'Selected station is invalid.',
        ]);

        DB::transaction(function () use ($request) {
            $isMaster = $request->boolean('is_master');

            if ($isMaster) {
                Warehouse::where('is_master', true)->update([
                    'is_master' => false,
                    'type' => 'sub',
                ]);
            }

            Warehouse::create([
                'serial_no'   => (new Warehouse)->generateSerialNo(),
                'name'        => $request->name,
                'type'        => $isMaster ? 'master' : 'sub',
                'manager_id'  => $request->manager_id,
                'station_id'  => $request->station_id,
                'is_master'   => $isMaster,
                'is_active'   => true,
            ]);
        });

        // Delete draft after successful save
        $this->deleteDraftAfterSuccess($request, 'warehouses');

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Warehouse created successfully!');
    }

    public function show(Request $request, $id)
    {
        $role_slug = auth()->user()->role->slug;


        $warehouse = Warehouse::with('station')->findOrFail($id);
        return view('admin.warehouses.show', compact('warehouse', 'role_slug'));
    }

    public function edit(Request $request, Warehouse $warehouse)
    {
        $role_slug = auth()->user()->role->slug;


        $managers = User::where('is_active', 1)->where('designation_id', 3)->where('is_active', 1)->orderBy('name', 'ASC')->pluck('name', 'id');
        $stations = Station::where('is_active', 1)->orderBy('area', 'ASC')->pluck('area', 'id');

        $masterWarehouse = Warehouse::master()->where('id', '!=', $warehouse->id)->first();

        return view('admin.warehouses.edit', compact('warehouse', 'stations', 'managers', 'role_slug', 'masterWarehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $role_slug = auth()->user()->role->slug;


        $validator = \Validator::make(
            $request->all(),
            [
                'name'          =>  'required|string|max:255',
                'is_master'     => 'nullable|boolean',
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

        DB::transaction(function () use ($request, $warehouse) {
            $isMaster = $request->boolean('is_master');

            if ($isMaster) {
                Warehouse::where('id', '!=', $warehouse->id)
                    ->where('is_master', true)
                    ->update([
                        'is_master' => false,
                        'type' => 'sub',
                    ]);
            }

            $warehouse->name        = $request->name;
            $warehouse->type        = $isMaster ? 'master' : 'sub';
            $warehouse->manager_id  = $request->manager_id;
            $warehouse->station_id  = $request->station_id;
            $warehouse->is_master   = $isMaster;
            $warehouse->save();
        });

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Request $request, Warehouse $warehouse)
    {
        $role_slug = auth()->user()->role->slug;


        $warehouse->is_active = 0;
        $warehouse->save();

        return redirect()->route('warehouses.index')->with('delete_msg', 'Warehouse deleted successfully.');
    }

    /**
     * Quick-toggle: set a warehouse as Master directly from the index list.
     * Uses the same atomic logic as store() / update() to ensure only one master exists.
     */
    public function setMaster(Request $request, Warehouse $warehouse)
    {
        if (!auth()->user()->hasPermission('warehouses')) {
            abort(403, 'You do not have permission to perform this action.');
        }

        DB::transaction(function () use ($warehouse) {
            // Unset any existing master
            Warehouse::where('is_master', true)
                ->where('id', '!=', $warehouse->id)
                ->update([
                    'is_master' => false,
                    'type'      => 'sub',
                ]);

            // Set this warehouse as master
            $warehouse->is_master = true;
            $warehouse->type      = 'master';
            $warehouse->save();
        });

        return redirect()->route('warehouses.index')
            ->with('success', "'{$warehouse->name}' has been set as the Master Warehouse.");
    }
}

