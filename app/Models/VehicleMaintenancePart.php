<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenancePart extends Model
{
    protected $fillable = [
        'vehicle_maintenance_id',
        'warehouse_assignment_id',
        'warehouse_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function maintenance()
    {
        return $this->belongsTo(VehicleMaintenance::class, 'vehicle_maintenance_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseAssignment()
    {
        return $this->belongsTo(WarehouseAssignment::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductList::class, 'product_id');
    }
}
