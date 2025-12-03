<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'quantity', 'warehouse_id'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouses::class);
    }

    public function inventoryRequests()
    {
        return $this->hasMany(InventoryRequest::class);
    }
    public function inventoryIssues()
    {
        return $this->hasMany(InventoryIssue::class);
    }
}
