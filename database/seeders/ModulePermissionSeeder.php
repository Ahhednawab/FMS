<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ModulePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'dashboard',
            'drafts',

            'users',
            'roles',

            'cities',
            'stations',
            'ibc_centers',

            'vehicles',
            'drivers',
            'vendors',
            'insurance_companies',

            'brands',
            'categories',
            'products',

            'daily_mileages',
            'daily_mileage_reports',
            'daily_fuels',
            'daily_fuel_reports',

            'warehouses',
            'assigned_inventory',
            'master_warehouse_inventory',
            'purchases',
            'suppliers',
            'issues',

            'accident_details',
            'accident_reports',

            'vehicle_maintenances',
            'vehicle_maintenance_reports',

            'driver_attendances',
            'vehicle_attendances',

            'salaries',
            'advances',
            'invoices',
        ];

        // Create permissions
        foreach ($modules as $module) {
            Permission::firstOrCreate([
                'name'  => str_replace("_", " ", $module),
                'label' => $module,
            ]);
        }

        // Assign all permissions to Admin (role_id = 1)
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $permissionIds = Permission::pluck('id');

        // Delete old mappings for admin
        DB::table('role_permissions')->where('role_id', $admin->id)->delete();

        $rows = [];
        foreach ($permissionIds as $permissionId) {
            $rows[] = [
                'role_id'       => $admin->id,
                'permission_id' => $permissionId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        DB::table('role_permissions')->insert($rows);

        // ------------------------------------------------------
        // Assign only 'dashboard' permission to all other roles
        // ------------------------------------------------------
        $dashboardPermission = Permission::where('label', 'dashboard')->first();

        if ($dashboardPermission) {
            $allRoles = Role::all();
            $rows = [];

            foreach ($allRoles as $role) {
                // Skip admin (already has all permissions)
                if ($role->name === 'Admin') continue;

                $rows[] = [
                    'role_id'       => $role->id,
                    'permission_id' => $dashboardPermission->id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('role_permissions')->insert($rows);
            }
        }
    }
}
