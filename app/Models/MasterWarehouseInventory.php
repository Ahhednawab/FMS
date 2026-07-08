<?php

namespace App\Models;

use App\Models\ProductList;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class MasterWarehouseInventory extends Model
{
    protected $table = "master_warehouse_inventory";

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'price',
    ];

    /**
     * The product linked to this inventory batch.
     */
    public function product()
    {
        return $this->belongsTo(ProductList::class, 'product_id');
    }

    /**
     * The Master Warehouse that received this inventory batch.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public static function GetBatchNumber()
    {
        $serial_no = DB::table('master_warehouse_inventory');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
