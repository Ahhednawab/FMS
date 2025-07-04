<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('invoice_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_quantity')->nullable();
            $table->string('product_price')->nullable();
            $table->string('order_price')->nullable();
            $table->string('discount')->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('sub_total')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_invoices');
    }
};
