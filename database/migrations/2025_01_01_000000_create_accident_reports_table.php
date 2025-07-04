<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('accident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('accident_id')->nullable();
            $table->string('accident_type')->nullable();
            $table->time('accident_time')->nullable();
            $table->string('accident_description')->nullable();
            $table->integer('order_quantity')->nullable();
            $table->decimal('order_price', 10, 2)->nullable();
            $table->string('location')->nullable();
            $table->date('accident_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('accident_reports');
    }
};
