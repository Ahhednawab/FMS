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
        Schema::create('inventory_demands', function (Blueprint $table) {
    $table->id();
    $table->date('requested_date')->nullable();
    $table->string('priority')->nullable();
    $table->string('status')->nullable();
    $table->string('warehouse')->nullable();
    $table->string('requested_by')->nullable();
    $table->string('department')->nullable();
    $table->string('product_name')->nullable();
    $table->decimal('product_price', 10, 2)->nullable();
    $table->integer('requested_quantity')->nullable();
    $table->date('expected_delivery_date')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_demands');
    }
};
