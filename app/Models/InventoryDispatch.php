<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryDispatch extends Model
{
    protected $fillable = [
        'dispatch_date',
        'dispatched_by',
        'designation_id',
        'department',
        'location',
        'dispatch_type',
        'status',
        'product_name',
        'order_price',
        'warehouse',
        'dispatched_qty',
    ];

    public function dispatchBy()
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function department()
    {
        return $this->belongsTo(department::class, 'department_id');
    }

    public function dispatchType()
    {
        return $this->belongsTo(DispatchTypes::class, 'dispatch_type');
    }

    public function inventoryDispatchStatus()
    {
        return $this->belongsTo(InventoryDispatchStatus::class, 'status');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }



    public static function GetSerialNumber()
    {
        $serial_no = DB::table('inventory_dispatches');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
