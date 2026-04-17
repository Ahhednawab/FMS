<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('maintenance_item');
            $table->unsignedInteger('service_interval_km');
            $table->unsignedInteger('alert_before_km')->default(500);
            $table->unsignedInteger('last_service_km')->nullable();
            $table->unsignedInteger('next_due_km')->nullable();
            $table->date('last_service_date')->nullable();
            $table->timestamp('last_alerted_at')->nullable();
            $table->timestamps();

            $table->unique(['vehicle_id', 'maintenance_item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_schedules');
    }
};
