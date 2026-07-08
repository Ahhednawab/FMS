<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vehicle_maintenance_parts') && Schema::hasColumn('vehicle_maintenance_parts', 'product_id')) {
            DB::statement('ALTER TABLE vehicle_maintenance_parts MODIFY product_id INT NOT NULL');
        }
    }
};
