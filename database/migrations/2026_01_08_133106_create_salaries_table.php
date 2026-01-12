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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('driver_id')
                ->constrained('drivers')
                ->cascadeOnDelete();

            $table->date('salary_month');

            // Monetary fields as unsigned integers
            $table->unsignedInteger('overtime_amount')->default(0);
            $table->unsignedInteger('deduction_amount')->default(0);
            $table->unsignedInteger('advance_deduction')->default(0);
            $table->unsignedInteger('gross_salary');

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['driver_id', 'salary_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
