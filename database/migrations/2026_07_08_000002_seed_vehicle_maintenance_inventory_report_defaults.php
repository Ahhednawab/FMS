<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureLookup('inventory_larger_report_category', 'Vehicle Maintenance');
        $this->ensureLookup('transaction_types', 'Maintenance Issue');
        $this->ensureLookup('inventory_larger_report_status', 'Completed');
        $this->ensureLookup('suppliers', 'Own');
    }

    private function ensureLookup(string $table, string $name): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $exists = DB::table($table)->where('name', $name)->exists();

        if (!$exists) {
            DB::table($table)->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
