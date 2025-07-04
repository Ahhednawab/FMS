<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'product_id',
        'product_category_id',
        'brands',
        'quantity',
        'price',
        'restock_qty_alarm',
        'supplier_id',
        'warehouse_id',
        'procured_date',
        'expiry_date',
        'description',
    ];

    public function product()
    {
        return $this->belongsTo(ProductList::class, 'product_id');
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Vendor::class, 'supplier_id');
    }

    

    

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }


    public static function GetSerialNumber()
    {
        $serial_no = DB::table('products');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
