<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    protected $fillable = [
        'driver_id',
        'advance_date',
        'amount',
        'remaining_amount',
        'per_month_deduction', // Added this
        'remarks',
        'is_closed'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
