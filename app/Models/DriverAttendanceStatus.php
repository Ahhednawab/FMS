<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DriverAttendanceStatus extends Model
{
    protected $table = 'driver_attendance_status';
    protected $fillable = ['name'];
}
