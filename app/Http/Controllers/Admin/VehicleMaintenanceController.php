<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleMaintenance;
use App\Models\Vehicle;
use App\Models\FuelType;
use App\Models\MaintenanceCategory;
use App\Models\ServiceProvider;
use App\Models\Parts;

use Illuminate\Http\Request;

class VehicleMaintenanceController extends Controller
{
    public function index()
    {
        $vehicleMaintenances = VehicleMaintenance::with(['vehicle', 'fuelType', 'maintenanceCategory', 'serviceProvider', 'parts'])->where('is_active', 1)->get();

        return view('admin.vehicleMaintenances.index', compact('vehicleMaintenances'));
    }

    public function create()
    {
        $maintenance_id = VehicleMaintenance::GetMaintenanceId();
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        $fuel_types = FuelType::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $category = MaintenanceCategory::where('is_active', 1)->orderBy('category')->pluck('category', 'id');
        $service_provider = ServiceProvider::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $parts = Parts::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        return view('admin.vehicleMaintenances.create', compact('maintenance_id', 'fuel_types', 'category', 'service_provider', 'parts', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'model' => 'required',
                'odometer_reading' => 'required|numeric',
                'fuel_type' => 'required',
                'category' => 'required',
                'service_date' => 'required',
                'service_provider' => 'required',
                'parts_replaced' => 'required',
                'service_cost' => 'required|numeric',
                'service_description' => 'required',
            ],
            [
                'vehicle_id.required'           =>  'Vehicle No is required',
                'model.required'                =>  'Model is required',
                'odometer_reading.required'     =>  'Odometer Reading is required',
                'fuel_type.required'            =>  'Fuel Type is required',
                'category.required'             =>  'Maintenance Category is required',
                'service_date.required'         =>  'Service Date is required',
                'service_provider.required'     =>  'Service Provider is required',
                'parts_replaced.required'       =>  'Parts Replaced is required',
                'service_cost.required'         =>  'Cost of Service is required',
                'service_description.required'  =>  'Service Description is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vehicleMaintenance = new VehicleMaintenance();
        $vehicleMaintenance->maintenance_id         =   $request->maintenance_id;
        $vehicleMaintenance->vehicle_id             =   $request->vehicle_id;
        $vehicleMaintenance->model                  =   $request->model;
        $vehicleMaintenance->odometer_reading       =   $request->odometer_reading;
        $vehicleMaintenance->fuel_type              =   $request->fuel_type;
        $vehicleMaintenance->category               =   $request->category;
        $vehicleMaintenance->service_date           =   $request->service_date;
        $vehicleMaintenance->service_provider       =   $request->service_provider;
        $vehicleMaintenance->parts_replaced         =   $request->parts_replaced;
        $vehicleMaintenance->service_cost           =   $request->service_cost;
        $vehicleMaintenance->service_description    =   $request->service_description;
        $vehicleMaintenance->save();

        return redirect()->route('vehicleMaintenances.index')->with('success', 'Vehicle Maintenances created successfully.');
    }

    public function edit(VehicleMaintenance $vehicleMaintenance)
    {
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        $fuel_types = FuelType::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $category = MaintenanceCategory::where('is_active', 1)->orderBy('category')->pluck('category', 'id');
        $service_provider = ServiceProvider::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $parts = Parts::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        return view('admin.vehicleMaintenances.edit', compact('vehicleMaintenance', 'fuel_types', 'category', 'service_provider', 'parts', 'vehicles'));
    }

    public function update(Request $request, VehicleMaintenance $vehicleMaintenance)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'model' => 'required',
                'odometer_reading' => 'required|numeric',
                'fuel_type' => 'required',
                'category' => 'required',
                'service_date' => 'required',
                'service_provider' => 'required',
                'parts_replaced' => 'required',
                'service_cost' => 'required|numeric',
                'service_description' => 'required',
            ],
            [
                'vehicle_id.required'           =>  'Vehicle No is required',
                'model.required'                =>  'Model is required',
                'odometer_reading.required'     =>  'Odometer Reading is required',
                'fuel_type.required'            =>  'Fuel Type is required',
                'category.required'             =>  'Maintenance Category is required',
                'service_date.required'         =>  'Service Date is required',
                'service_provider.required'     =>  'Service Provider is required',
                'parts_replaced.required'       =>  'Parts Replaced is required',
                'service_cost.required'         =>  'Cost of Service is required',
                'service_description.required'  =>  'Service Description is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vehicleMaintenance->vehicle_id             =   $request->vehicle_id;
        $vehicleMaintenance->model                  =   $request->model;
        $vehicleMaintenance->odometer_reading       =   $request->odometer_reading;
        $vehicleMaintenance->fuel_type              =   $request->fuel_type;
        $vehicleMaintenance->category               =   $request->category;
        $vehicleMaintenance->service_date           =   $request->service_date;
        $vehicleMaintenance->service_provider       =   $request->service_provider;
        $vehicleMaintenance->parts_replaced         =   $request->parts_replaced;
        $vehicleMaintenance->service_cost           =   $request->service_cost;
        $vehicleMaintenance->service_description    =   $request->service_description;
        $vehicleMaintenance->save();

        return redirect()->route('vehicleMaintenances.index')->with('success', 'Vehicle Maintenances updated successfully.');
    }

    public function show(VehicleMaintenance $vehicleMaintenance)
    {
        return view('admin.vehicleMaintenances.show', compact('vehicleMaintenance'));
    }

    public function destroy(VehicleMaintenance $vehicleMaintenance)
    {
        $vehicleMaintenance->is_active = 0;
        $vehicleMaintenance->save();
        return redirect()->route('vehicleMaintenances.index')->with('delete_msg', 'Vehicle Maintenances deleted successfully.');
    }
}
