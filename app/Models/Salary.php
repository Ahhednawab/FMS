<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = [
        'driver_id',
        'salary_month',
        'overtime_amount',
        'deduction_amount',
        'advance_deduction',
        'gross_salary',
        'remarks',
        'status',               // new: pending/paid
        'paid_absent',          // new
        'extra',                // new
        'advance_issued',       // new
        'per_month_deduction',  // new
        'total_recovered',      // new
        'remaining_amount',     // new
    ];

    /* -----------------------------
       Relationships
    ----------------------------- */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /* -----------------------------
       Accessors / Helpers
    ----------------------------- */

    /**
     * Calculate gross salary dynamically
     */
    public function calculateGross()
    {
        $basic = $this->driver->salary ?? 0;
        $overtime = $this->overtime_amount ?? 0;
        $extra = $this->extra ?? 0;
        $deduction = $this->deduction_amount ?? 0;
        $advance = $this->advance_deduction ?? 0;
        $perMonthDeduction = $this->per_month_deduction ?? 0;

        $this->gross_salary = $basic + $overtime + $extra - $deduction - $advance - $perMonthDeduction;
        $this->remaining_amount = $this->gross_salary - ($this->total_recovered ?? 0);
    }
}
