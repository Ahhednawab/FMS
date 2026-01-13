<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accident_details', function (Blueprint $table) {
            $table->id();
            $table->string('accident_id')->nullable();
            $table->string('accident_type')->nullable();
            $table->string('location')->nullable();
            $table->date('accident_date')->nullable();
            $table->time('accident_time')->nullable();
            $table->string('accident_description')->nullable();
            $table->string('person_involved')->nullable();
            $table->string('injury_type')->nullable();
            $table->string('damage_type')->nullable();
            $table->integer('order_quantity')->nullable();
            $table->decimal('order_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accident_details');
    }
};
