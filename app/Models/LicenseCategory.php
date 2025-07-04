<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseCategory extends Model
{
    protected $table = 'licence_category';
    protected $fillable = ['name'];
}
