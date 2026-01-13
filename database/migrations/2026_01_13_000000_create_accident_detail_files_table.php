<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accident_detail_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accident_detail_id')->constrained('accident_details')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->comment('image or document');
            $table->string('original_name');
            $table->bigInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accident_detail_files');
    }
};
