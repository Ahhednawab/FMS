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
            $table->unsignedBigInteger('alert_id')->nullable()->after('remarks');
            $table->unsignedInteger('threshold_km')->nullable()->after('alert_id');
            $table->unsignedInteger('alert_before_km')->nullable()->after('threshold_km');

            $table->foreign('alert_id')->references('id')->on('alerts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            $table->dropForeign(['alert_id']);
            $table->dropColumn(['alert_id', 'threshold_km', 'alert_before_km']);
        });
    }
};
