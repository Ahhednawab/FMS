<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('master_inventory_id');
            $table->unsignedBigInteger('requested_by'); // sub-warehouse / user
            $table->integer('quantity');
            $table->decimal('price', 10, 2);

            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->timestamps();

            $table->foreign('master_inventory_id')
                ->references('id')
                ->on('master_warehouse_inventory')
                ->cascadeOnDelete();

            $table->foreign('requested_by')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_requests');
    }
};
