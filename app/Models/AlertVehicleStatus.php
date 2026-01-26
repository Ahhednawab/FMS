<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertVehicleStatus extends Model
{
    protected $fillable = [
        'alert_id',
        'vehicle_id',
        'last_mileage',
    ];

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
