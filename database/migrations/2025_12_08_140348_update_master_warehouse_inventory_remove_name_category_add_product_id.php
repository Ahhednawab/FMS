<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_warehouse_inventory', function (Blueprint $table) {
            // 1. Add product_id first (with nullable so existing rows are safe)
            $table->foreignId('product_id')
                ->after('id')
                ->constrained('products_list')
                ->onDelete('cascade');

            // 2. Now remove the old columns
            $table->dropColumn(['name', 'category']);
        });
    }

    public function down(): void
    {
        Schema::table('master_warehouse_inventory', function (Blueprint $table) {
            // Reverse: add back name & category
            $table->string('name')->after('id');
            $table->string('category')->nullable()->after('name');

            // Drop the foreign key and column
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
