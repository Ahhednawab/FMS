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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no')->unique();
            $table->string('name');
            $table->enum('type', ['master', 'sub'])->default('sub');

            $table->foreignId('manager_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // THIS IS THE FIX — use exact same type as stations.id
            $table->unsignedBigInteger('station_id');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Add foreign key separately (safe way when table already exists)
            $table->foreign('station_id')
                ->references('id')
                ->on('stations')
                ->onDelete('restrict');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
