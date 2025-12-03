<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryIssue extends Model
{
    use HasFactory;

    protected $fillable = ['master_warehouse_id', 'sub_warehouse_id', 'inventory_item_id', 'quantity'];

    public function masterWarehouse()
    {
        return $this->belongsTo(Warehouses::class, 'master_warehouse_id');
    }

    public function subWarehouse()
    {
        return $this->belongsTo(Warehouses::class, 'sub_warehouse_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
