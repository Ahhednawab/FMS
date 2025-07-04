<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchTypes extends Model
{
    protected $table = 'dispatch_types';
    protected $fillable = ['name'];
}
