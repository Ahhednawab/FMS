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
        Schema::create('master_warehouse_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Name of the inventory item
            $table->string('category')->nullable();
            $table->string('batch_number')->nullable();  // Optional batch number
            $table->date('expiry_date')->nullable();  // Expiry date (nullable)
            $table->integer('quantity');  // Quantity of the item
            $table->integer('price')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_warehouse_inventory');
    }
};
