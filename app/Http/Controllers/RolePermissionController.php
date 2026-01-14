<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissionMap = config('permissions');

        $permissionNames = collect($permissionMap)
            ->flatMap(fn($perms) => array_keys($perms))
            ->values();

        $permissions = Permission::whereIn('label', $permissionNames)
            ->get()
            ->keyBy('label');

        $groupedPermissions = [];

        foreach ($permissionMap as $module => $perms) {
            foreach ($perms as $name => $label) {
                if (isset($permissions[$name])) {
                    $groupedPermissions[$module][] = $permissions[$name];
                }
            }
        }

        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('admin.permissions.edit', compact(
            'role',
            'groupedPermissions',
            'rolePermissionIds'
        ));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $roleId = $id;
        $permissionIds = $request->permissions ?? []; // Selected permissions from form

        // Get the dashboard permission ID
        $dashboardPermission = Permission::where('name', 'dashboard')->first();
        $dashboardId = $dashboardPermission ? $dashboardPermission->id : null;

        // Delete all role_permissions except dashboard
        DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->when($dashboardId, function ($query) use ($dashboardId) {
                $query->where('permission_id', '!=', $dashboardId);
            })
            ->delete();

        // Insert selected permissions
        $rows = [];
        $now = now();
        foreach ($permissionIds as $permissionId) {
            if ($permissionId == $dashboardId) continue; // skip dashboard
            $rows[] = [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('role_permissions')->insert($rows);
        }

        return redirect()->route('admin.role-permissions.edit', $roleId)
            ->with('success', 'Role permissions updated successfully!');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
