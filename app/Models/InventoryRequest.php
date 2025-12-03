<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    use HasFactory;

    protected $fillable = ['sub_warehouse_id', 'inventory_item_id', 'quantity'];

    public function subWarehouse()
    {
        return $this->belongsTo(Warehouses::class, 'sub_warehouse_id');
    }
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
