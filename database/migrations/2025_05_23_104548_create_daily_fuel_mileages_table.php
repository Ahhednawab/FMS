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
        Schema::create('daily_fuel_mileages', function (Blueprint $table) {
    $table->id();
    $table->string('serial_no')->unique();
    $table->string('vehicle_no');
    $table->string('destination');
    $table->date('date');
    $table->integer('current_reading');
    $table->integer('previous_reading');
    $table->integer('difference')->nullable();
    $table->decimal('fuel_taken', 8, 2);
    $table->decimal('consumption', 8, 2);
    $table->string('fuel_station');
    $table->string('driver_name');
    $table->string('location')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_fuel_mileages');
    }
};
