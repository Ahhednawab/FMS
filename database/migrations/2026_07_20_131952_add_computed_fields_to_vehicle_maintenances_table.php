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
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            $table->unsignedInteger('next_due_mileage')->nullable()->after('alert_before_km');
            $table->unsignedInteger('alert_start_mileage')->nullable()->after('next_due_mileage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            $table->dropColumn(['next_due_mileage', 'alert_start_mileage']);
        });
    }
};
