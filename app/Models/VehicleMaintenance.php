<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VehicleMaintenance extends Model
{
    protected $table = 'vehicle_maintenances';

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

    public static function GetMaintenanceId()
    {
        $maintenance_id = DB::table('vehicle_maintenances');
        $maintenance_id = $maintenance_id->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $maintenance_id = $maintenance_id->first()->id;

        return $maintenance_id;
    }
}
