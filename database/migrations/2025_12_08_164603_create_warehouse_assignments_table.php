<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_inventory_id')
                ->constrained('master_warehouse_inventory')
                ->onDelete('cascade');

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->onDelete('cascade');

            $table->integer('quantity');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('price', 12, 2);

            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_assignments');
    }
};
