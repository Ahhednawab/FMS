<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('primary_driver_id')
                ->nullable()
                ->after('shift_timing_id')
                ->constrained('drivers')
                ->nullOnDelete();

            $table->foreignId('current_driver_id')
                ->nullable()
                ->after('primary_driver_id')
                ->constrained('drivers')
                ->nullOnDelete();

            $table->boolean('is_new_vehicle')
                ->default(false)
                ->after('current_driver_id');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['primary_driver_id']);
            $table->dropForeign(['current_driver_id']);
            $table->dropColumn(['primary_driver_id', 'current_driver_id', 'is_new_vehicle']);
        });
    }
};
