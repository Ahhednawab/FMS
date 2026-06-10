<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->string('vehicle_no');
            $table->string('display_vehicle_no')->nullable();
            $table->string('akpl')->nullable();
            $table->string('shift')->nullable();
            $table->decimal('peak_kms', 12, 2)->default(0);
            $table->decimal('api_off_peak_kms', 12, 2)->default(0);
            $table->decimal('api_ams_kms', 12, 2)->default(0);
            $table->decimal('off_peak', 12, 2)->default(0);
            $table->decimal('mis_peak_hrs', 12, 2)->default(0);
            $table->decimal('ams', 12, 2)->default(0);
            $table->decimal('parking', 12, 2)->default(0);
            $table->decimal('total_kms', 12, 2)->default(0);
            $table->decimal('odo_kms', 12, 2)->default(0);
            $table->decimal('diff', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['report_date', 'vehicle_no']);
            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_reports');
    }
};
