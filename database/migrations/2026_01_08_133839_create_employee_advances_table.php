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
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();

            $table->date('advance_date');

            $table->decimal('amount', 10, 2);                // issued amount
            $table->decimal('per_month_deduction', 10, 2)->default(0);   // monthly recovery
            $table->decimal('remaining_amount', 10, 2);

            $table->text('remarks')->nullable();
            $table->boolean('is_closed')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_advances');
    }
};
