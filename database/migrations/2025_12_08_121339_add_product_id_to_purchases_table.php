<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Add product_id column after item_name (optional positioning)
            $table->foreignId('product_id')
                ->after('item_name')
                ->nullable()                    // allow old records to stay valid
                ->constrained('products_list')  // references id on products_list
                ->onDelete('set null');         // if product deleted → product_id becomes null

            // Optional: add index for faster queries
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['product_id']);
            // Then drop the column
            $table->dropColumn('product_id');
        });
    }
};
