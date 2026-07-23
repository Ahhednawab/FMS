<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $label = 'vehicle_maintenance_configurations';

    /**
     * Register the "Vehicle Maintenance Configuration" module permission and
     * grant it to the Admin role, matching how ModulePermissionSeeder works.
     */
    public function up(): void
    {
        $permissionId = DB::table('permissions')->where('label', $this->label)->value('id');

        if (!$permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name'       => str_replace('_', ' ', $this->label),
                'label'      => $this->label,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminRoleId = DB::table('roles')->where('name', 'Admin')->value('id');

        if ($adminRoleId) {
            $alreadyGranted = DB::table('role_permissions')
                ->where('role_id', $adminRoleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$alreadyGranted) {
                DB::table('role_permissions')->insert([
                    'role_id'       => $adminRoleId,
                    'permission_id' => $permissionId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('label', $this->label)->value('id');

        if ($permissionId) {
            DB::table('role_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
