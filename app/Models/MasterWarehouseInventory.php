<?php

namespace App\Models;

use App\Models\ProductList;


use Illuminate\Database\Eloquent\Model;

class MasterWarehouseInventory extends Model
{
    protected $table = "master_warehouse_inventory";
    protected $fillable = ['product_id', 'batch_number', 'expiry_date', 'quantity', 'price'];


    public function product()
    {
        return $this->belongsTo(ProductList::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
