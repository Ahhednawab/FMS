<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->after('salary');
            $table->string('ke_card_serial')->nullable()->after('employee_code');
            $table->string('location')->nullable()->after('ke_card_serial');
            $table->string('designation')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['employee_code', 'ke_card_serial', 'location', 'designation']);
        });
    }
};
