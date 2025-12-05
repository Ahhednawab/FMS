<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductList extends Model
{
    protected $table = 'products_list';
    protected $fillable = [
        'name',
        'vendor_id',
        'product_category_id',
        'brand_id',
        'unit_id',
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }



    public static function GetSerialNumber()
    {
        $serial_no = DB::table('products_list');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
