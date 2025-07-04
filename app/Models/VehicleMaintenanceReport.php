<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VehicleMaintenanceReport extends Model
{
    protected $table = 'vehicle_maintenance_reports';

    protected $fillable = [
        'vehicle_no',
        'model',
        'odometer_reading',
        'fuel_type',
        'category',
        'service_date',
        'service_provider',
        'parts_replaced',
        'service_cost',
        'service_description',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class, 'fuel_type');
    }

    public function maintenanceCategory()
    {
        return $this->belongsTo(MaintenanceCategory::class, 'category');
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider');
    }

    public function parts()
    {
        return $this->belongsTo(Parts::class, 'parts_replaced');
    }

    public function tyreCondition()
    {
        return $this->belongsTo(TyreCondition::class, 'tyre_condition');
    }

    public function brakeCondition()
    {
        return $this->belongsTo(BrakeCondition::class, 'brake_condition');
    }

    public function engineCondition()
    {
        return $this->belongsTo(EngineCondition::class, 'engine_condition');
    }

    public function batteryCondition()
    {
        return $this->belongsTo(BatteryCondition::class, 'battery_condition');
    }

    public static function GetMaintenanceReportId()
    {
        $maintenance_report_id = DB::table('vehicle_maintenance_reports');
        $maintenance_report_id = $maintenance_report_id->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $maintenance_report_id = $maintenance_report_id->first()->id;

        return $maintenance_report_id;
    }
}
