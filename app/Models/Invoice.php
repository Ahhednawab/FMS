<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($invoice) {
            $lastId = self::max('id') + 1;
            $invoice->serial_no = 'INV-' . str_pad($lastId, 6, '0', STR_PAD_LEFT);
        });
    }

    protected $fillable = [
        'serial_no',
        'dp_no',
        'invoice_no',
        'invoice_month',
        'invoice_date',
        'submission_date',
        'po_no',
        'vehicle_qty',
        'days',
        'vehicle_rent',
        'monthly_rent',
        'sunday_gazette',
        'control_room_charges',
        'total_claim',
        'sales_tax',
        'inclusive_sales_tax',
        'tax_value',
        'withholding_on_sales_tax',
        'actual_payment',
        'agreed_deduction',
        'cheque_value',
        'cheque_no',
        'diff',
        'due_date',
        'cheque_rec_date',
        'payment_time_line_days',
        'payment_difference_in_days',
        'created_by'
    ];

    protected $casts = [
        'invoice_month' => 'date',
        'invoice_date' => 'date',
        'submission_date' => 'date',
        'due_date' => 'date',
        'cheque_rec_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
