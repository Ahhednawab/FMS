<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('serial_no')->unique();
        $table->string('name');
        $table->string('category')->nullable();
        $table->string('brand')->nullable();
        $table->string('stock_status')->nullable();
        $table->string('supplier')->nullable();
        $table->string('warehouse')->nullable();
        $table->string('product_type')->nullable();
        $table->decimal('price', 10, 2)->default(0);
        $table->integer('stock_quantity')->default(0);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
