<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterWarehouseInventory extends Model
{
    protected $fillable = ['name', 'batch_number', 'expiry_date', 'quantity','price','category'];
}
