<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_cart_assignments', function (Blueprint $table) {
            $table->id();

            // Job Cart
            $table->unsignedBigInteger('job_cart_id');

            // Assignment info
            $table->unsignedBigInteger('assigned_by');
            $table->unsignedBigInteger('assigned_to');

            // Inventory & product
            $table->unsignedBigInteger('inventory_id');
            $table->unsignedBigInteger('product_id');

            $table->integer('quantity');

            $table->timestamps();

            // Foreign keys
            $table->foreign('job_cart_id')
                ->references('id')
                ->on('job_carts')
                ->onDelete('cascade');

            $table->foreign('assigned_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('assigned_to')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventories')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_cart_assignments');
    }
};
    