<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AttendanceStatus extends Model
{
    protected $table = 'attendance_status';
    protected $fillable = ['name'];
}
