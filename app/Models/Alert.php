<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['title', 'threshold'];

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'alert_vehicle_statuses')
            ->withPivot('last_mileage')
            ->withTimestamps();
    }
}
