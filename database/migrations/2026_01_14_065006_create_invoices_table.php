<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('serial_no')->nullable();
            $table->string('dp_no')->nullable();
            $table->string('invoice_no')->nullable();

            $table->date('invoice_month')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('submission_date')->nullable();

            $table->string('po_no')->nullable();

            $table->integer('vehicle_qty')->nullable();
            $table->integer('days')->nullable();

            $table->decimal('vehicle_rent', 15, 2)->nullable();
            $table->decimal('monthly_rent', 15, 2)->nullable();
            $table->decimal('sunday_gazette', 15, 2)->nullable();
            $table->decimal('control_room_charges', 15, 2)->nullable();

            $table->decimal('total_claim', 15, 2)->nullable();
            $table->decimal('sales_tax', 15, 2)->nullable();
            $table->decimal('inclusive_sales_tax', 15, 2)->nullable();
            $table->decimal('tax_value', 15, 2)->nullable();
            $table->decimal('withholding_on_sales_tax', 15, 2)->nullable();

            $table->decimal('actual_payment', 15, 2)->nullable();
            $table->decimal('agreed_deduction', 15, 2)->nullable();

            $table->decimal('cheque_value', 15, 2)->nullable();
            $table->string('cheque_no')->nullable();

            $table->decimal('diff', 15, 2)->nullable();

            $table->date('due_date')->nullable();
            $table->date('cheque_rec_date')->nullable();

            $table->integer('payment_time_line_days')->nullable();
            $table->integer('payment_difference_in_days')->nullable();

            $table->integer('created_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
