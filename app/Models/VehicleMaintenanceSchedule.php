<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceSchedule extends Model
{
    protected $fillable = [
        'vehicle_id',
        'maintenance_item',
        'service_interval_km',
        'alert_before_km',
        'last_service_km',
        'next_due_km',
        'last_service_date',
        'last_alerted_at',
    ];

    protected $casts = [
        'last_service_date' => 'date',
        'last_alerted_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
