<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add warehouse_id FK to master_warehouse_inventory.
     * This links each inventory batch to the Master Warehouse it was received into,
     * enabling proper tracking of which warehouse received the stock.
     * Nullable for backward compatibility with existing records.
     */
    public function up(): void
    {
        Schema::table('master_warehouse_inventory', function (Blueprint $table) {
            if (!Schema::hasColumn('master_warehouse_inventory', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')
                    ->nullable()
                    ->after('product_id');

                $table->foreign('warehouse_id')
                    ->references('id')
                    ->on('warehouses')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_warehouse_inventory', function (Blueprint $table) {
            if (Schema::hasColumn('master_warehouse_inventory', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
        });
    }
};
