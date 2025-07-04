<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_larger_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_id')->nullable();
            $table->date('report_date')->nullable();
            $table->string('product_name')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('supplier')->nullable();
            $table->integer('order_quantity')->nullable();
            $table->decimal('order_price', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->date('delivery_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_larger_reports');
    }
};
