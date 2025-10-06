<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'module',
        'data',
        'file_info',
    ];

    protected $casts = [
        'data' => 'array',
        'file_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}