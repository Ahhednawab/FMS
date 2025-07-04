<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientInvoice extends Model
{
    protected $fillable = [
        'invoice_id',
        'invoice_date',
        'due_date',
        'client_name',
        'client_email',
        'billing_address',
        'shipping_address',
        'invoice_status',
        'payment_method',
        'reference_no',
        'product_name',
        'product_quantity',
        'product_price',
        'order_price',
        'discount',
        'tax_rate',
        'sub_total',
    ];
}
