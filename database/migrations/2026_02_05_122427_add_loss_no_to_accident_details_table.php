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
        Schema::table('accident_details', function (Blueprint $table) {
             $table->string('loss_no')->nullable()->after('accident_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accident_details', function (Blueprint $table) {
            $table->dropColumn('loss_no');

        });
    }
};
