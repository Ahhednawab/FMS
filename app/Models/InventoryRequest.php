<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    protected $fillable = [
        'master_inventory_id',
        'requested_by',
        'product_id',
        'quantity',
        'price',
        'reason',
        'status'
    ];

    public function inventory()
    {
        return $this->belongsTo(
            MasterWarehouseInventory::class,
            'master_inventory_id'
        );
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
