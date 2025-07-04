<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryDispatchStatus extends Model
{
    protected $table = 'inventory_dispatch_status';
    protected $fillable = ['name'];
}
