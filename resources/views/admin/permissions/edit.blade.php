@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">Edit Role: <strong>{{ $role->name }}</strong></h3>

        <form action="{{ route('admin.role-permissions.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- LEFT: Modules Grid -->
                <div class="col-md-4">
                    <div class="row">
                        @foreach ($groupedPermissions as $module => $permissions)
                            <div class="col-md-6 mb-3">
                                <div class="card module-card text-center" data-module="{{ Str::slug($module) }}">
                                    <div class="card-body">
                                        <i class="icon-stack2 icon-2x mb-2"></i>
                                        <h6 class="mb-0">{{ $module }}</h6>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- RIGHT: Permissions Panel -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header font-weight-bold">
                            Permissions
                        </div>
                        <div class="card-body">
                            @foreach ($groupedPermissions as $module => $permissions)
                                <div class="permission-group d-none" id="module-{{ Str::slug($module) }}">

                                    <h5 class="mb-3">{{ $module }}</h5>

                                    @foreach ($permissions as $permission)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>
                                                {{ ucfirst(str_replace('_', ' ', $permission->label)) }}
                                            </span>

                                            <label class="switch">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}
                                                    {{ $permission->name === 'dashboard' ? 'disabled' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            <div id="empty-state" class="text-muted text-center">
                                Select a module to view permissions
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn btn-primary mt-4">Update Role</button>
        </form>
    </div>

    <!-- Styles -->
    <style>
        .module-card {
            cursor: pointer;
            transition: 0.2s;
            height: 90px;

            /* uniform height */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .module-card .card-body {
            padding: 8px;
        }

        .module-card i {
            font-size: 22px;
            margin-bottom: 4px;
        }

        .module-card h6 {
            font-size: 13px;
            margin: 0;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .module-card:hover,
        .module-card.active {
            background: #f8f9fa;
            border-color: #007bff;
        }

        /* ------- Toggle Switch Styles ------- */
        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 22px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            background-color: #ccc;
            border-radius: 34px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            transition: .3s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            border-radius: 50%;
            transition: .3s;
        }

        input:checked+.slider {
            background-color: #28a745;
        }

        input:checked+.slider:before {
            transform: translateX(22px);
        }
    </style>

    <!-- Script -->
    <script>
        $('.module-card').on('click', function() {
            $('.module-card').removeClass('active');
            $(this).addClass('active');

            $('.permission-group').addClass('d-none');
            $('#empty-state').addClass('d-none');

            let module = $(this).data('module');
            $('#module-' + module).removeClass('d-none');
        });
    </script>
@endsection
