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
        \DB::table('attendance_status')->insert([
            ['name' => 'Off Day', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Inspection', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('attendance_status')
        ->whereIn('name', ['Off Day', 'Inspection'])
        ->delete();
    }
};
