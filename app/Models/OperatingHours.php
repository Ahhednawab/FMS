<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingHours extends Model
{
    protected $table = 'operating_hours';
    protected $fillable = ['start','end'];
}
