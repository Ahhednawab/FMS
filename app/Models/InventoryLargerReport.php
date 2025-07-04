<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryLargerReport extends Model
{
    protected $fillable = [
        'report_date',
        'product_name',
        'warehouse',
        'category',
        'location',
        'transaction_type',
        'supplier',
        'order_quantity',
        'order_price',
        'status',
        'delievery_date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function inventoryLargerReportCategory()
    {
        return $this->belongsTo(InventoryLargerReportCategory::class, 'category');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function inventoryLargerReportStatus()
    {
        return $this->belongsTo(InventoryLargerReportStatus::class, 'status');
    }





    public static function GetReportId()
    {
        $report_id = DB::table('inventory_larger_reports');
        $report_id = $report_id->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $report_id = $report_id->first()->id;

        return $report_id;
    }
}
