<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (! Schema::hasColumn('drivers', 'station_id')) {
                $table->foreignId('station_id')
                    ->nullable()
                    ->after('vehicle_id')
                    ->constrained('stations')
                    ->nullOnDelete();
                $table->index(['driver_type', 'station_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'station_id')) {
                $table->dropIndex(['driver_type', 'station_id']);
                $table->dropConstrainedForeignId('station_id');
            }
        });
    }
};
