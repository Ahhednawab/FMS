<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Parts extends Model
{
    protected $table = 'parts';
    protected $fillable = ['name'];
}
