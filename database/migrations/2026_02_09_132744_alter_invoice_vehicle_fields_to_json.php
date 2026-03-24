<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('vehicle_qty')->nullable()->change();
            $table->json('days')->nullable()->change();
            $table->json('vehicle_rent')->nullable()->change();
            $table->json('monthly_rent')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('vehicle_qty')->nullable()->change();
            $table->integer('days')->nullable()->change();
            $table->decimal('vehicle_rent', 15, 2)->nullable()->change();
            $table->decimal('monthly_rent', 15, 2)->nullable()->change();
        });
    }
};