<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VehicleMaintenance extends Model
{
    protected $table = 'vehicle_maintenances';

    protected $fillable = [
        'maintenance_id',
        'vehicle_no',
        'vehicle_id',
        'vehicle_make',
        'model',
        'odometer_reading',
        'fuel_type',
        'category',
        'service_date',
        'maintenance_type',
        'work_done_id',
        'warehouse_id',
        'workshop_id',
        'service_provider',
        'parts_replaced',
        'labor_cost',
        'service_cost',
        'service_description',
        'remarks',
        'alert_id',
        'threshold_km',
        'alert_before_km',
        'next_due_mileage',
        'alert_start_mileage',
        'is_alert_triggered',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'service_date' => 'date',
        'labor_cost' => 'decimal:2',
        'service_cost' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function alert()
    {
        return $this->belongsTo(Alert::class);
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

    public function workDone()
    {
        return $this->belongsTo(VehicleMaintenanceWorkDone::class, 'work_done_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function maintenanceParts()
    {
        return $this->hasMany(VehicleMaintenancePart::class, 'vehicle_maintenance_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parts()
    {
        return $this->belongsTo(Parts::class, 'parts_replaced');
    }

    public static function GetMaintenanceId()
    {
        $maintenance_id = DB::table('vehicle_maintenances');
        $maintenance_id = $maintenance_id->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $maintenance_id = $maintenance_id->first()->id;

        return $maintenance_id;
    }
}
