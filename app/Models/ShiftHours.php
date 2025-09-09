<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShiftHours extends Model
{
    protected $table = 'shift_hours';
    protected $fillable = ['hours','name'];
}
