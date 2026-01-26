<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE notifications 
            MODIFY COLUMN type 
            ENUM('master_data', 'maintenance', 'driver')
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE notifications 
            MODIFY COLUMN type 
            ENUM('master_data', 'maintenance')
        ");
    }
};
