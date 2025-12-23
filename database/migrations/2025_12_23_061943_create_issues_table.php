<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id(); // id
            $table->string('title'); // title
            $table->boolean('is_active')->default(true); // is_active
            $table->unsignedBigInteger('created_by'); // created_by
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
