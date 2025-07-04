<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class InventoryDemand extends Model
{
    protected $fillable = [
        'request_date',
        'requested_by',
        'department',
        'priority',
        'status',
        'product_name',
        'product_price',
        'warehouse',
        'requested_qty',
        'expected_delivery_date',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    public function inventoryStatus()
    {
        return $this->belongsTo(InventoryStatus::class, 'status');
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
        $serial_no = DB::table('inventory_demands');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}

