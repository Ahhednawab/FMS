<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrakeCondition extends Model
{
	protected $table = 'brake_conditions';
    protected $fillable = ['name'];
}
