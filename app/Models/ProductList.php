<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductList extends Model
{
	protected $table = 'products_list';
    protected $fillable = ['name'];
}
