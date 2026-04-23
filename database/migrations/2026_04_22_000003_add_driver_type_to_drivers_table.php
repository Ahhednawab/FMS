<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (! Schema::hasColumn('drivers', 'driver_type')) {
                $table->string('driver_type')->default('regular')->after('driver_status_id');
                $table->index('driver_type');
            }
        });

        DB::table('drivers')
            ->whereNull('driver_type')
            ->orWhere('driver_type', '')
            ->update(['driver_type' => 'regular']);
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'driver_type')) {
                $table->dropColumn('driver_type');
            }
        });
    }
};
