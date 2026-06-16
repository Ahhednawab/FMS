<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('drivers', 'station_id')) {
            return;
        }

        if (Schema::hasTable('stations')) {
            DB::statement('ALTER TABLE stations ENGINE=InnoDB');
        }

        $this->dropForeignKeyIfExists('drivers', 'drivers_station_id_foreign');
        $this->dropIndexIfExists('drivers', 'drivers_station_id_foreign');

        DB::statement('ALTER TABLE drivers MODIFY station_id INT NULL');
        DB::statement('ALTER TABLE drivers ADD CONSTRAINT drivers_station_id_foreign FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('drivers', 'station_id')) {
            return;
        }

        $this->dropForeignKeyIfExists('drivers', 'drivers_station_id_foreign');
        $this->dropIndexIfExists('drivers', 'drivers_station_id_foreign');

        DB::statement('ALTER TABLE drivers MODIFY station_id BIGINT UNSIGNED NULL');
    }

    private function dropForeignKeyIfExists(string $table, string $constraint): void
    {
        $exists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if ($exists) {
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$constraint}");
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();

        if ($exists) {
            DB::statement("ALTER TABLE {$table} DROP INDEX {$index}");
        }
    }
};
