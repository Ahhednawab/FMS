<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('car_maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_id')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('model')->nullable();
            $table->string('odometer')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('maintenance_category')->nullable();
            $table->date('service_date')->nullable();
            $table->string('service_provider')->nullable();
            $table->string('parts_replaced')->nullable();
            $table->string('cost_of_service')->nullable();
            $table->text('service_description')->nullable();
            $table->string('tire_condition')->nullable();
            $table->string('brake_condition')->nullable();
            $table->string('engine_condition')->nullable();
            $table->string('battery_condition')->nullable();
            $table->string('next_service_due')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('car_maintenance_reports');
    }
};
