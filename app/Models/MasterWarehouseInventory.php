<?php

namespace App\Models;

use App\Models\ProductList;


use Illuminate\Support\Facades\DB;
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

    public static function GetBatchNumber()
    {
        $serial_no = DB::table('master_warehouse_inventory');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
