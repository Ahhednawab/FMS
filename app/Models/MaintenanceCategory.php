<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MaintenanceCategory extends Model
{
    protected $table = 'maintenance_categories';
    protected $fillable = ['category'];
}
