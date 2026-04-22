<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vehicle_driver_replacement_logs')) {
            return;
        }

        Schema::create('vehicle_driver_replacement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('main_driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->foreignId('replacement_driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->unsignedBigInteger('drivers_attendance_id')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['vehicle_id', 'date']);
            $table->index('drivers_attendance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_driver_replacement_logs');
    }
};
