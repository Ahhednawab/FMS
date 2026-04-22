<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDriverReplacementLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'date',
        'main_driver_id',
        'replacement_driver_id',
        'drivers_attendance_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mainDriver()
    {
        return $this->belongsTo(Driver::class, 'main_driver_id');
    }

    public function replacementDriver()
    {
        return $this->belongsTo(Driver::class, 'replacement_driver_id');
    }

    public function attendance()
    {
        return $this->belongsTo(DriversAttendance::class, 'drivers_attendance_id');
    }
}
