<?php

namespace App\Models;

use App\Models\ProductList;
use Illuminate\Database\Eloquent\Model;

class WarehouseAssignment extends Model
{
    protected $table = 'warehouse_assignments';

    protected $fillable = [
        'master_inventory_id',
        'warehouse_id',
        'quantity',
        'batch_number',
        'expiry_date',
        'price',
        'assigned_by',
    ];

    public function masterInventory()
    {
        return $this->belongsTo(MasterWarehouseInventory::class, 'master_inventory_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->hasOneThrough(
            ProductList::class,
            MasterWarehouseInventory::class,
            'id',
            'id',
            'master_inventory_id',
            'product_id'
        );
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
