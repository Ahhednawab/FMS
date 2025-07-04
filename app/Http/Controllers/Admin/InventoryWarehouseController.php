<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryWarehouse;
use App\Models\Country;
use App\Models\User;
use App\Models\WarehouseType;
use App\Models\OperatingHours;
use App\Models\HandlingEquipment;
use App\Models\City;


use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryWarehouseController extends Controller
{
    public function index(){
        $inventoryWarehouses = InventoryWarehouse::with(['city','warehouseManager'])->where('is_active',1)->orderBy('id','DESC')->get();

        return view('admin.inventoryWarehouses.index', compact('inventoryWarehouses'));
    }

    public function create(){
        $serial_no = InventoryWarehouse::GetSerialNumber();

        // $countries = Country::where('is_active', 1)
        //     ->whereHas('cities', function ($query) {
        //         $query->where('is_active', 1);
        //     })
        //     ->with(['cities' => function ($query) {
        //         $query->where('is_active', 1)->orderBy('name', 'ASC');
        //     }])
        //     ->orderBy('name', 'ASC')
        //     ->get();

        $cities = City::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $warehouseManagers = User::where('is_active',1)->where('designation_id',3)->orderBy('name','ASC')->pluck('name','id');
        $warehouseTypes = WarehouseType::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $operatingHours = OperatingHours::select('id', 'start', 'end')
            ->where('is_active', 1)
            ->orderBy('start', 'ASC')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->id => Carbon::parse($item->start)->format('h:i A') . ' - ' . Carbon::parse($item->end)->format('h:i A')
                ];
            })
            ->toArray();

        $handlingEquipments = HandlingEquipment::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');

        return view('admin.inventoryWarehouses.create',compact('serial_no','cities','warehouseManagers','warehouseTypes','operatingHours','handlingEquipments'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'warehouse_name' => 'required',
                // 'location' => 'required',
                'country_id' => 'required',
                'city_id' => 'required',
                'contact' => 'required',
                'warehouse_manager' => 'required',
                'warehouse_type' => 'required',
                'operating_hour' => 'required',
                'handling_equipment' => 'required',
            ],
            [
                'warehouse_name.required'       =>  'Warehouse Name is required',
                // 'location.required'             =>  'Location is required',
                'country_id.required'           =>  'Country is required',
                'city_id.required'              =>  'City is required',
                'contact.required'              =>  'Contact is required',
                'warehouse_manager.required'    =>  'Wahehouse Manager is required',
                'warehouse_type.required'       =>  'Wahehouse Type is required',
                'operating_hour.required'       =>  'Operating hours is required',
                'handling_equipment.required'   =>  'Handling Equipment is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $inventoryWarehouse = new InventoryWarehouse();
        $inventoryWarehouse->serial_no = $request->serial_no;
        $inventoryWarehouse->warehouse_name = $request->warehouse_name;
        // $inventoryWarehouse->location = $request->location;
        $inventoryWarehouse->country_id = $request->country_id;
        $inventoryWarehouse->city_id = $request->city_id;
        $inventoryWarehouse->contact = $request->contact;
        $inventoryWarehouse->warehouse_manager = $request->warehouse_manager;
        $inventoryWarehouse->warehouse_type = $request->warehouse_type;
        $inventoryWarehouse->operating_hour = $request->operating_hour;
        $inventoryWarehouse->handling_equipment = $request->handling_equipment;
        $inventoryWarehouse->save();

        return redirect()->route('admin.inventoryWarehouses.index')->with('success', 'Warehouse Inventory created successfully.');
    }

    public function show(InventoryWarehouse $inventoryWarehouse)
    {
        return view('admin.inventoryWarehouses.show', compact('inventoryWarehouse'));
    }

    public function edit(InventoryWarehouse $inventoryWarehouse)
    {
        $countries = Country::where('is_active', 1)
            ->whereHas('cities', function ($query) {
                $query->where('is_active', 1);
            })
            ->with(['cities' => function ($query) {
                $query->where('is_active', 1)->orderBy('name', 'ASC');
            }])
            ->orderBy('name', 'ASC')
            ->get();
        $warehouseManagers = User::where('is_active',1)->where('designation_id',3)->orderBy('name','ASC')->pluck('name','id');
        $warehouseTypes = WarehouseType::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $operatingHours = OperatingHours::select('id', 'start', 'end')
            ->where('is_active', 1)
            ->orderBy('start', 'ASC')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->id => Carbon::parse($item->start)->format('h:i A') . ' - ' . Carbon::parse($item->end)->format('h:i A')
                ];
            })
            ->toArray();

        $handlingEquipments = HandlingEquipment::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');

        return view('admin.inventoryWarehouses.edit', compact('inventoryWarehouse','countries','warehouseManagers','warehouseTypes','operatingHours','handlingEquipments'));
    }

    public function update(Request $request, InventoryWarehouse $inventoryWarehouse)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'warehouse_name' => 'required',
                // 'location' => 'required',
                'country_id' => 'required',
                'city_id' => 'required',
                'contact' => 'required',
                'warehouse_manager' => 'required',
                'warehouse_type' => 'required',
                'operating_hour' => 'required',
                'handling_equipment' => 'required',
            ],
            [
                'warehouse_name.required'       =>  'Warehouse Name is required',
                // 'location.required'             =>  'Location is required',
                'country_id.required'           =>  'Country is required',
                'city_id.required'              =>  'City is required',
                'contact.required'              =>  'Contact is required',
                'warehouse_manager.required'    =>  'Wahehouse Manager is required',
                'warehouse_type.required'       =>  'Wahehouse Type is required',
                'operating_hour.required'       =>  'Operating hours is required',
                'handling_equipment.required'   =>  'Handling Equipment is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $inventoryWarehouse->warehouse_name = $request->warehouse_name;
        // $inventoryWarehouse->location = $request->location;
        $inventoryWarehouse->country_id = $request->country_id;
        $inventoryWarehouse->city_id = $request->city_id;
        $inventoryWarehouse->contact = $request->contact;
        $inventoryWarehouse->warehouse_manager = $request->warehouse_manager;
        $inventoryWarehouse->warehouse_type = $request->warehouse_type;
        $inventoryWarehouse->operating_hour = $request->operating_hour;
        $inventoryWarehouse->handling_equipment = $request->handling_equipment;
        $inventoryWarehouse->save();

        return redirect()->route('admin.inventoryWarehouses.index')->with('success', 'Warehouse Inventory updated successfully.');
    }

    public function destroy(InventoryWarehouse $inventoryWarehouse)
    {
        $inventoryWarehouse->is_active = 0;
        $inventoryWarehouse->save();

        return redirect()->route('admin.inventoryWarehouses.index')->with('delete_msg', 'Warehouse Inventory deleted successfully.');
    }
}
