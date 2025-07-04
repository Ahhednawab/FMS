<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashPayment extends Model
{
    protected $fillable = [
        'voucher_id',
        'voucher_date',
        'payment_method',
        'reference_no',
        'payment_status',
        'payee_name',
        'payee_type',
        'payee_contact',
        'payee_address',
        'amount',
        'tax_deduction',
        'total_amount',
        'description',
    ];
}
