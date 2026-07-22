<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance_work_done_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_maintenance_id');
            $table->foreignId('work_done_id');
            $table->timestamps();

            $table->foreign('vehicle_maintenance_id', 'vm_wdi_maintenance_fk')
                ->references('id')->on('vehicle_maintenances')
                ->cascadeOnDelete();
            $table->foreign('work_done_id', 'vm_wdi_work_done_fk')
                ->references('id')->on('vehicle_maintenance_work_dones')
                ->cascadeOnDelete();

            $table->unique(['vehicle_maintenance_id', 'work_done_id'], 'vm_work_done_unique');
        });

        // Seed pivot rows from the legacy single work_done_id column so
        // existing maintenance records keep their Work Done value.
        DB::statement('
            INSERT INTO vehicle_maintenance_work_done_items (vehicle_maintenance_id, work_done_id, created_at, updated_at)
            SELECT id, work_done_id, NOW(), NOW()
            FROM vehicle_maintenances
            WHERE work_done_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_work_done_items');
    }
};
