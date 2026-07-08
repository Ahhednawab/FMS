<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the is_master boolean column to warehouses.
     * The Warehouse model and controller already reference this field
     * but it was never added to the DB via a migration.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'is_master')) {
                $table->boolean('is_master')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (Schema::hasColumn('warehouses', 'is_master')) {
                $table->dropColumn('is_master');
            }
        });
    }
};
