<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Allow decimal quantities (e.g. 15.5 Liter, 2.75 KG) across the
     * inventory and vehicle maintenance flows. Existing integer values
     * are preserved unchanged.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE master_warehouse_inventory MODIFY quantity DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE warehouse_assignments MODIFY quantity DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE vehicle_maintenance_parts MODIFY quantity DECIMAL(12,2) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE inventory_larger_reports MODIFY order_quantity DECIMAL(12,2) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE master_warehouse_inventory MODIFY quantity INT(11) NOT NULL');
        DB::statement('ALTER TABLE warehouse_assignments MODIFY quantity INT(11) NOT NULL');
        DB::statement('ALTER TABLE vehicle_maintenance_parts MODIFY quantity INT(10) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE inventory_larger_reports MODIFY order_quantity INT(11) NOT NULL');
    }
};
