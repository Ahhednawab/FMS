<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cash_payments', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_id')->nullable();
            $table->date('voucher_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payee_name')->nullable();
            $table->string('payee_type')->nullable();
            $table->string('payee_contact')->nullable();
            $table->string('payee_address')->nullable();
            $table->string('amount')->nullable();
            $table->string('tax_deduction')->nullable();
            $table->string('total_amount')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cash_payments');
    }
};
