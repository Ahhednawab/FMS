<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\UserPermission;

class UserPermissionController extends Controller
{
    public function index()
    {
        // $userPermissions = UserPermission::with(['user', 'permission'])->get();
        // return view('admin.userPermissions.index', compact('userPermissions'));
    }

    public function create()
    {
        $users = User::pluck('name', 'id');
        $permissions = Permission::pluck('name', 'id');

        return view('user_permissions.create', compact('users', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_id' => 'required|exists:permissions,id'
        ]);

        UserPermission::updateOrCreate([
            'user_id' => $request->user_id,
            'permission_id' => $request->permission_id
        ]);

        return redirect()->route('user-permissions.index')
            ->with('success', 'Permission assigned to user');
    }

    public function destroy(UserPermission $userPermission)
    {
        $userPermission->delete();

        return redirect()->route('user-permissions.index')
            ->with('success', 'Permission removed');
    }
}
