<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatteryCondition extends Model
{
    protected $table = 'battery_conditions';
    protected $fillable = ['name'];
}
