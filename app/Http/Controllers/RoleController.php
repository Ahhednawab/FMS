<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        // Create the role
        $role = Role::create([
            'name' => trim($validated['name']),
            'slug' => trim(str_replace(' ', '-', $validated['name'])),
            'description' => $validated['description'] ?? null,
        ]);



        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);

        // Update the role
        $role->update([
            'name' => trim($validated['name']),
            'slug' => trim(str_replace(' ', '-', $validated['name'])),
            'description' => $validated['description'] ?? null,
        ]);



        return redirect()->route('roles.show', $role->id)
            ->with('success', "Role '{$role->name}' updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of Admin role
        if (strtolower($role->name) === 'admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete the Admin role.');
        }

        if ($role->users()->exists()) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'This role is assigned to users. Please make sure nobody is assigned to this role and try again.');
        }

        $roleName = $role->name;

        // Detach all permissions
        $role->permissions()->detach();

        // Delete the role
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Role '{$roleName}' deleted successfully.");
    }
}
