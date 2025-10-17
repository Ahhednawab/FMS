<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclesAttendance extends Model
{
    protected $fillable = [
        'vehicle_id',
        'date',
        'status',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status');
    }
}
