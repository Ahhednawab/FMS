<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriversAttendance extends Model
{
    protected $fillable = [
        'serial_no',
        'name',
        'father_name',
        'shift_time',
        'vehicle_no',
        'remarks',
        'date',
        'status',
    ];
}
