<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AccidentDetailController::destroy() soft-deletes by setting is_active = 0,
     * but the column was never added to accident_details — deleting a record
     * failed with "Unknown column 'is_active'". Existing rows default to active.
     */
    public function up(): void
    {
        Schema::table('accident_details', function (Blueprint $table) {
            if (!Schema::hasColumn('accident_details', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accident_details', function (Blueprint $table) {
            if (Schema::hasColumn('accident_details', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
