<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleMaintenanceReport;
use App\Models\Vehicle;
use App\Models\FuelType;
use App\Models\MaintenanceCategory;
use App\Models\ServiceProvider;
use App\Models\Parts;
use App\Models\TyreCondition;
use App\Models\BrakeCondition;
use App\Models\EngineCondition;
use App\Models\BatteryCondition;


use Illuminate\Http\Request;

class VehicleMaintenanceReportController extends Controller
{
    public function index(){
        $vehicleMaintenanceReports = VehicleMaintenanceReport::with(['vehicle','fuelType','maintenanceCategory','serviceProvider','parts','tyreCondition','brakeCondition','engineCondition','batteryCondition'])->where('is_active',1)->get();

        return view('admin.vehicleMaintenanceReports.index', compact('vehicleMaintenanceReports'));
    }

    public function create(){
        $maintenance_report_id = VehicleMaintenanceReport::GetMaintenanceReportId();
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        $fuel_types = FuelType::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $category = MaintenanceCategory::where('is_active', 1)->orderBy('category')->pluck('category', 'id');
        $service_provider = ServiceProvider::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $parts = Parts::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $tyre_conditions = TyreCondition::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $brake_conditions = BrakeCondition::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $engine_conditions = EngineCondition::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $battery_conditions = BatteryCondition::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        
        return view('admin.vehicleMaintenanceReports.create',compact('maintenance_report_id','fuel_types','category','service_provider','parts','vehicles','tyre_conditions','brake_conditions','engine_conditions','battery_conditions'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'model' => 'required',
                'odo_meter_reading' => 'required|numeric',
                'fuel_type' => 'required',
                'category' => 'required',
                'service_date' => 'required',
                'service_provider' => 'required',
                'parts_replaced' => 'required',
                'service_cost' => 'required|numeric',
                'service_description' => 'required',
                'tyre_condition' => 'required',
                'brake_condition' => 'required',
                'engine_condition' => 'required',
                'battery_condition' => 'required',
                'next_service_date' => 'required',
            ],
            [
                'vehicle_id.required'           =>  'Vehicle No is required',
                'model.required'           =>  'Model is required',
                'odo_meter_reading.required'           =>  'Odometer Reading is required',
                'fuel_type.required'           =>  'Fuel Type is required',
                'category.required'           =>  'Maintenance Category is required',
                'service_date.required'           =>  'Service Date is required',
                'service_provider.required'           =>  'Service Provider is required',
                'parts_replaced.required'           =>  'Parts Replaced is required',
                'service_cost.required'           =>  'Cost of Service is required',
                'service_description.required'           =>  'Service Description is required',
                'tyre_condition.required'           =>  'Tyre Condition is required',
                'brake_condition.required'           =>  'Brake Condition is required',
                'engine_condition.required'           =>  'Engine Condition is required',
                'battery_condition.required'           =>  'Battery Condition is required',
                'next_service_date.required'           =>  'Next Service Due is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vehicleMaintenanceReport = new VehicleMaintenanceReport();
        $vehicleMaintenanceReport->maintenance_report_id    =   $request->maintenance_report_id;
        $vehicleMaintenanceReport->vehicle_id               =   $request->vehicle_id;
        $vehicleMaintenanceReport->model                    =   $request->model;
        $vehicleMaintenanceReport->odo_meter_reading        =   $request->odo_meter_reading;
        $vehicleMaintenanceReport->fuel_type                =   $request->fuel_type;
        $vehicleMaintenanceReport->category                 =   $request->category;
        $vehicleMaintenanceReport->service_date             =   $request->service_date;
        $vehicleMaintenanceReport->service_provider         =   $request->service_provider;
        $vehicleMaintenanceReport->parts_replaced           =   $request->parts_replaced;
        $vehicleMaintenanceReport->service_cost             =   $request->service_cost;
        $vehicleMaintenanceReport->service_description      =   $request->service_description;
        $vehicleMaintenanceReport->tyre_condition           =   $request->tyre_condition;
        $vehicleMaintenanceReport->brake_condition          =   $request->brake_condition;
        $vehicleMaintenanceReport->engine_condition         =   $request->engine_condition;
        $vehicleMaintenanceReport->battery_condition        =   $request->battery_condition;
        $vehicleMaintenanceReport->next_service_date        =   $request->next_service_date;
        $vehicleMaintenanceReport->save();


        return redirect()->route('admin.vehicleMaintenanceReports.index')->with('success', 'Vehicle Maintenance Report created successfully.');
    }

    public function edit(VehicleMaintenanceReport $vehicleMaintenanceReport)
    {
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        $fuel_types = FuelType::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $category = MaintenanceCategory::where('is_active', 1)->orderBy('category')->pluck('category', 'id');
        $service_provider = ServiceProvider::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $parts = Parts::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $tyre_conditions = TyreCondition::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $brake_conditions = BrakeCondition::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $engine_conditions = EngineCondition::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $battery_conditions = BatteryCondition::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        return view('admin.vehicleMaintenanceReports.edit', compact('vehicleMaintenanceReport','fuel_types','category','service_provider','parts','vehicles','tyre_conditions','brake_conditions','engine_conditions','battery_conditions'));
    }

    public function update(Request $request, VehicleMaintenanceReport $vehicleMaintenanceReport)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'model' => 'required',
                'odo_meter_reading' => 'required|numeric',
                'fuel_type' => 'required',
                'category' => 'required',
                'service_date' => 'required',
                'service_provider' => 'required',
                'parts_replaced' => 'required',
                'service_cost' => 'required|numeric',
                'service_description' => 'required',
                'tyre_condition' => 'required',
                'brake_condition' => 'required',
                'engine_condition' => 'required',
                'battery_condition' => 'required',
                'next_service_date' => 'required',
            ],
            [
                'vehicle_id.required'           =>  'Vehicle No is required',
                'model.required'           =>  'Model is required',
                'odo_meter_reading.required'           =>  'Odometer Reading is required',
                'fuel_type.required'           =>  'Fuel Type is required',
                'category.required'           =>  'Maintenance Category is required',
                'service_date.required'           =>  'Service Date is required',
                'service_provider.required'           =>  'Service Provider is required',
                'parts_replaced.required'           =>  'Parts Replaced is required',
                'service_cost.required'           =>  'Cost of Service is required',
                'service_description.required'           =>  'Service Description is required',
                'tyre_condition.required'           =>  'Tyre Condition is required',
                'brake_condition.required'           =>  'Brake Condition is required',
                'engine_condition.required'           =>  'Engine Condition is required',
                'battery_condition.required'           =>  'Battery Condition is required',
                'next_service_date.required'           =>  'Next Service Due is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vehicleMaintenanceReport->vehicle_id               =   $request->vehicle_id;
        $vehicleMaintenanceReport->model                    =   $request->model;
        $vehicleMaintenanceReport->odo_meter_reading        =   $request->odo_meter_reading;
        $vehicleMaintenanceReport->fuel_type                =   $request->fuel_type;
        $vehicleMaintenanceReport->category                 =   $request->category;
        $vehicleMaintenanceReport->service_date             =   $request->service_date;
        $vehicleMaintenanceReport->service_provider         =   $request->service_provider;
        $vehicleMaintenanceReport->parts_replaced           =   $request->parts_replaced;
        $vehicleMaintenanceReport->service_cost             =   $request->service_cost;
        $vehicleMaintenanceReport->service_description      =   $request->service_description;
        $vehicleMaintenanceReport->tyre_condition           =   $request->tyre_condition;
        $vehicleMaintenanceReport->brake_condition          =   $request->brake_condition;
        $vehicleMaintenanceReport->engine_condition         =   $request->engine_condition;
        $vehicleMaintenanceReport->battery_condition        =   $request->battery_condition;
        $vehicleMaintenanceReport->next_service_date        =   $request->next_service_date;
        $vehicleMaintenanceReport->save();


        return redirect()->route('admin.vehicleMaintenanceReports.index')->with('success', 'Vehicle Maintenance Report updated successfully.');
    }

    public function show(VehicleMaintenanceReport $vehicleMaintenanceReport)
    {
        return view('admin.vehicleMaintenanceReports.show', compact('vehicleMaintenanceReport'));
    }

    public function destroy(VehicleMaintenanceReport $vehicleMaintenanceReport)
    {
        $vehicleMaintenanceReport->is_active = 0;
        $vehicleMaintenanceReport->save();
        return redirect()->route('admin.vehicleMaintenanceReports.index')->with('delete_msg', 'Vehicle Maintenance Report deleted successfully.');
    }
}
