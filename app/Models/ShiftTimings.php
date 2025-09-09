<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShiftTimings extends Model
{
    protected $table = 'shift_timing';
    protected $fillable = ['name','start_time','end_time'];
}
