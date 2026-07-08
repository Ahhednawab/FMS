<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceWorkDone extends Model
{
    protected $table = 'vehicle_maintenance_work_dones';

    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
