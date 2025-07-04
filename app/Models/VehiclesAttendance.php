<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclesAttendance extends Model
{
    protected $fillable = [
        'serial_no',
        'vehicle_no',
        'date',
    ];
}
