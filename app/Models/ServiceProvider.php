<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceProvider extends Model
{
    protected $table = 'service_providers';
    protected $fillable = ['name'];
}
