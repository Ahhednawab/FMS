<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Ensure roles exist
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'Admin', 'slug' => 'admin', 'description' => 'System administrator', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Master Warehouse', 'slug' => 'master-warehouse', 'description' => 'Manages master warehouse', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Sub Warehouse', 'slug' => 'sub-warehouse', 'description' => 'Manages sub warehouse', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Maintainer', 'slug' => 'maintainer', 'description' => 'Maintaining inventory', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Step 2: Convert existing role strings in users table to numeric IDs
        // Make sure you map the strings that exist in your database correctly
        DB::table('users')->where('role', 'Admin')->update(['role' => 1]);
        DB::table('users')->where('role', 'Master Warehouse')->update(['role' => 2]);
        DB::table('users')->where('role', 'Sub Warehouse')->update(['role' => 3]);
        DB::table('users')->where('role', 'Maintainer')->update(['role' => 4]);

        // Step 3: Rename column
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role', 'role_id');
        });

        // Step 4: Change type and add foreign key
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->default(1)->change();
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Step 1: Drop foreign key and rename column back
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->renameColumn('role_id', 'role');
        });

        // Step 2: Optionally revert numeric IDs back to strings
        DB::table('users')->where('role', 1)->update(['role' => 'Admin']);
        DB::table('users')->where('role', 2)->update(['role' => 'Master Warehouse']);
        DB::table('users')->where('role', 3)->update(['role' => 'Sub Warehouse']);
        DB::table('users')->where('role', 4)->update(['role' => 'Maintainer']);
    }
};
