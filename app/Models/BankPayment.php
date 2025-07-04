<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankPayment extends Model
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
        'bank_name',
        'bank_branch',
        'description',
        'amount',
        'tax_deduction',
        'total_amount',
    ];
}
