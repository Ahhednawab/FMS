<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePoolDriver extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_id',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
