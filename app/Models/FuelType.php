<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FuelType extends Model
{
    protected $table = 'fuel_types';
    protected $fillable = ['name'];
}
