<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobCartAssignment extends Model
{
    use HasFactory;

    protected $table = 'job_cart_assignments';

    protected $fillable = [
        'job_cart_id',
        'assigned_by',
        'assigned_to',
        'inventory_id',
        'product_id',
        'quantity',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // User who assigned the job
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // User who received the job
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Inventory reference
    public function inventory()
    {
        return $this->belongsTo(MasterWarehouseInventory::class, 'inventory_id');
        // or MasterWarehouseInventory::class if that's your table
    }

    // Product reference
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
