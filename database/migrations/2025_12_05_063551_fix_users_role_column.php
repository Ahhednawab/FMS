<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'Admin', 'slug' => 'admin', 'description' => 'System administrator', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'User', 'slug' => 'user', 'description' => 'Default user', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Manager', 'slug' => 'manager', 'description' => 'Manager role', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('users')->where('role', 'admin')->update(['role' => 1]);
        DB::table('users')->where('role', 'user')->update(['role' => 2]);
        DB::table('users')->where('role', 'manager')->update(['role' => 3]);

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role', 'role_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->default(1)->change();
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->renameColumn('role_id', 'role');
        });

        // Optional: revert numeric IDs to strings
        DB::table('users')->where('role', 1)->update(['role' => 'admin']);
        DB::table('users')->where('role', 2)->update(['role' => 'user']);
        DB::table('users')->where('role', 3)->update(['role' => 'manager']);
    }
};
