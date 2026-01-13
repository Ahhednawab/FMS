<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccidentDetailFile extends Model
{
    protected $fillable = [
        'accident_detail_id',
        'file_name',
        'file_path',
        'file_type',
        'original_name',
        'file_size',
    ];

    public function accidentDetail()
    {
        return $this->belongsTo(AccidentDetail::class, 'accident_detail_id');
    }
}
