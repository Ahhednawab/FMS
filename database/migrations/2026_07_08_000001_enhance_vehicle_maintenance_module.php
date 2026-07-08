<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vehicle_maintenance_work_dones')) {
            Schema::create('vehicle_maintenance_work_dones', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('workshops')) {
            Schema::create('workshops', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('vehicle_maintenances')) {
            Schema::create('vehicle_maintenances', function (Blueprint $table) {
                $table->id();
                $table->string('maintenance_id')->nullable()->unique();
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->string('vehicle_make')->nullable();
                $table->string('model')->nullable();
                $table->unsignedInteger('odometer_reading')->nullable();
                $table->date('service_date')->nullable();
                $table->string('maintenance_type')->nullable();
                $table->foreignId('work_done_id')->nullable()->constrained('vehicle_maintenance_work_dones')->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('workshop_id')->nullable()->constrained('workshops')->nullOnDelete();
                $table->decimal('labor_cost', 12, 2)->default(0);
                $table->decimal('service_cost', 12, 2)->default(0);
                $table->text('service_description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        } else {
            Schema::table('vehicle_maintenances', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicle_maintenances', 'vehicle_make')) {
                    $table->string('vehicle_make')->nullable()->after('vehicle_id');
                }
                if (!Schema::hasColumn('vehicle_maintenances', 'maintenance_type')) {
                    $table->string('maintenance_type')->nullable()->after('service_date');
                }
                if (!Schema::hasColumn('vehicle_maintenances', 'work_done_id')) {
                    $table->foreignId('work_done_id')->nullable()->after('maintenance_type')->constrained('vehicle_maintenance_work_dones')->nullOnDelete();
                }
                if (!Schema::hasColumn('vehicle_maintenances', 'warehouse_id')) {
                    $table->foreignId('warehouse_id')->nullable()->after('work_done_id')->constrained('warehouses')->nullOnDelete();
                }
                if (!Schema::hasColumn('vehicle_maintenances', 'workshop_id')) {
                    $table->foreignId('workshop_id')->nullable()->after('warehouse_id')->constrained('workshops')->nullOnDelete();
                }
                if (!Schema::hasColumn('vehicle_maintenances', 'labor_cost')) {
                    $table->decimal('labor_cost', 12, 2)->default(0)->after('workshop_id');
                }
                if (!Schema::hasColumn('vehicle_maintenances', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
                }
            });

            $this->makeLegacyColumnsNullable();
        }

        if (!Schema::hasTable('vehicle_maintenance_parts')) {
            Schema::create('vehicle_maintenance_parts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vehicle_maintenance_id')->constrained('vehicle_maintenances')->cascadeOnDelete();
                $table->foreignId('warehouse_assignment_id')->nullable()->constrained('warehouse_assignments')->nullOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->integer('product_id');
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_price', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_parts');

        if (Schema::hasTable('vehicle_maintenances')) {
            Schema::table('vehicle_maintenances', function (Blueprint $table) {
                foreach (['created_by', 'workshop_id', 'warehouse_id', 'work_done_id'] as $column) {
                    if (Schema::hasColumn('vehicle_maintenances', $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }
            });
        }

        Schema::dropIfExists('workshops');
        Schema::dropIfExists('vehicle_maintenance_work_dones');
    }

    private function makeLegacyColumnsNullable(): void
    {
        $columns = [
            'fuel_type' => 'INT',
            'category' => 'INT',
            'service_provider' => 'INT',
            'parts_replaced' => 'INT',
        ];

        foreach ($columns as $column => $type) {
            if (Schema::hasColumn('vehicle_maintenances', $column)) {
                DB::statement("ALTER TABLE vehicle_maintenances MODIFY {$column} {$type} NULL");
            }
        }
    }
};
