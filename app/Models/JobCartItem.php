<?php

namespace App\Models;

use App\Models\JobCart;
use App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_cart_id',
        'product_id',
        'quantity',
    ];

    public function jobCart()
    {
        return $this->belongsTo(JobCart::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductList::class, 'product_id');
    }
    public function assignments()
    {
        return $this->hasMany(JobCartAssignment::class, 'job_cart_id');
    }
}
