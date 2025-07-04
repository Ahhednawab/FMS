<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('drivers_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no')->nullable();
            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('shift_time')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('remarks')->nullable();
            $table->date('date')->nullable();
            $table->string('status')->nullable(); // Present / Absent
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('drivers_attendances');
    }
};
