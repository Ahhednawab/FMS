<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no')->nullable();
            $table->date('dispatch_date')->nullable();
            $table->string('dispatched_by')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('dispatch_type')->nullable();
            $table->string('status')->nullable();
            $table->string('product_name')->nullable();
            $table->decimal('order_price', 10, 2)->nullable();
            $table->string('warehouse')->nullable();
            $table->integer('dispatched_quantity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_dispatches');
    }
};
