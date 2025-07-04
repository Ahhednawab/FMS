<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MileageStatus extends Model
{
    protected $table = 'mileage_status';
    protected $fillable = ['name'];
}
