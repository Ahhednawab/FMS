<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceAlert extends Model
{
    protected $table = 'maintenance_alerts';

    protected $fillable = [
        'vehicle_maintenance_id',
        'vehicle_id',
        'alert_id',
        'threshold_km',
        'alert_before_km',
        'current_km',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    public function vehicleMaintenance()
    {
        return $this->belongsTo(VehicleMaintenance::class);
    }
}
