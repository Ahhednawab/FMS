<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouses extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type'];

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
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
