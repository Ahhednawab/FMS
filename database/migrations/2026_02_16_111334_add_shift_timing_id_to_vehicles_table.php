<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedInteger('shift_timing_id')->nullable()->after('pool_vehicle');

            $table->foreign('shift_timing_id')
                ->references('id')
                ->on('shift_timing')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['shift_timing_id']);
            $table->dropColumn('shift_timing_id');
        });
    }
};