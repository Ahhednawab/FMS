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
        Schema::create('maintenance_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_maintenance_id')->nullable(); // the maintenance record that triggered it
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('alert_id');
            $table->unsignedInteger('threshold_km');
            $table->unsignedInteger('alert_before_km');
            $table->unsignedInteger('current_km');        // mileage at time of trigger
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->cascadeOnDelete();
            $table->foreign('alert_id')->references('id')->on('alerts')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_alerts');
    }
};
