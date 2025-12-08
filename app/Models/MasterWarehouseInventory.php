<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterWarehouseInventory extends Model
{
    protected $table = "master_warehouse_inventory";
    protected $fillable = ['name', 'batch_number', 'expiry_date', 'quantity', 'price', 'category'];
}
