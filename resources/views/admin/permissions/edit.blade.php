@extends('layouts.admin')

@section('content')
    <div class="container">

        <h2>Edit Role: {{ $role->name }}</h2>

        <form action="{{ route('admin.role-permissions.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')


            <h4 class="mt-4">Permissions</h4>

            @foreach ($groupedPermissions as $module => $permissions)
                <div class="card mb-3">
                    <div class="card-header bg-light font-weight-bold">
                        {{ $module }}
                    </div>

                    <div class="card-body">
                        <div class="row">
                            @foreach ($permissions as $permission)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            class="form-check-input" id="perm_{{ $permission->id }}"
                                            {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}
                                            {{ $permission->name === 'dashboard' ? 'disabled' : '' }}>

                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                            {{ ucfirst(str_replace('_', ' ', $permission->label)) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <button class="btn btn-primary mt-3">Update Role</button>
        </form>
    </div>
@endsection
