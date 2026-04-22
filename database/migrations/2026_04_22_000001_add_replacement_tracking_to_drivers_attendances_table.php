<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('drivers_attendances', 'vehicle_id')) {
                $table->unsignedBigInteger('vehicle_id')->nullable()->after('driver_id');
                $table->index('vehicle_id');
            }

            if (! Schema::hasColumn('drivers_attendances', 'original_driver_id')) {
                $table->unsignedBigInteger('original_driver_id')->nullable()->after('vehicle_id');
                $table->index('original_driver_id');
            }

            if (! Schema::hasColumn('drivers_attendances', 'replacement_driver_id')) {
                $table->unsignedBigInteger('replacement_driver_id')->nullable()->after('original_driver_id');
                $table->index('replacement_driver_id');
            }

            if (! Schema::hasColumn('drivers_attendances', 'is_replacement')) {
                $table->boolean('is_replacement')->default(false)->after('replacement_driver_id');
                $table->index('is_replacement');
            }
        });
    }

    public function down(): void
    {
        Schema::table('drivers_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('drivers_attendances', 'is_replacement')) {
                $table->dropColumn('is_replacement');
            }

            if (Schema::hasColumn('drivers_attendances', 'replacement_driver_id')) {
                $table->dropColumn('replacement_driver_id');
            }

            if (Schema::hasColumn('drivers_attendances', 'original_driver_id')) {
                $table->dropColumn('original_driver_id');
            }

            if (Schema::hasColumn('drivers_attendances', 'vehicle_id')) {
                $table->dropColumn('vehicle_id');
            }
        });
    }
};
