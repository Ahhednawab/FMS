<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The predictive engine now derives every schedule baseline from the
     * Vehicle Maintenance Configuration (per make + model) plus the vehicle's
     * maintenance history / current mileage. Old rows were seeded from a
     * hardcoded default list with mismatched item names and intervals, so we
     * clear them and let the engine reseed a clean, config-aligned state.
     */
    public function up(): void
    {
        if (Schema::hasTable('vehicle_maintenance_schedules')) {
            DB::table('vehicle_maintenance_schedules')->delete();
        }
    }

    public function down(): void
    {
        // No-op: the previous rows were disposable alert-tracking state.
    }
};
