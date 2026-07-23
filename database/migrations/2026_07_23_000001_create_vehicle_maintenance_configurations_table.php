<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Predictive maintenance configuration: one row per Vehicle Make + Model,
     * holding the service interval (KM) for every predefined maintenance item.
     */
    public function up(): void
    {
        Schema::create('vehicle_maintenance_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('make');
            $table->string('model');

            // Predefined predictive maintenance intervals (kilometers).
            $table->unsignedInteger('engine_oil')->nullable();
            $table->unsignedInteger('oil_filter')->nullable();
            $table->unsignedInteger('air_filter')->nullable();
            $table->unsignedInteger('transmission_oil')->nullable();
            $table->unsignedInteger('differential_oil')->nullable();
            $table->unsignedInteger('wheel_bearing_greasing')->nullable();
            $table->unsignedInteger('fuel_filter_small')->nullable();
            $table->unsignedInteger('fuel_filter_large')->nullable();
            $table->unsignedInteger('power_steering_oil')->nullable();
            $table->unsignedInteger('brake_oil')->nullable();
            $table->unsignedInteger('king_pin_greasing')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['make', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_configurations');
    }
};
