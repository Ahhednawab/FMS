@extends('layouts.admin')

@section('title', 'View Role')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">{{ $role->name }}</span></h4>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Role Information</h5>
                    </div>

                    <div class="card-body">
                        <!-- Role Name -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Role Name</label>
                            <p class="form-control-plaintext">{{ $role->name }}</p>
                        </div>

                        <!-- Description -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Description</label>
                            <p class="form-control-plaintext">{{ $role->description ?? 'No description provided.' }}</p>
                        </div>


                        <!-- Actions -->
                        <div class="text-right">
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                <i class="icon-arrow-left2 mr-1"></i> Back
                            </a>
                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-warning">
                                <i class="icon-pencil7 mr-1"></i> Edit
                            </a>
                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this role?')">
                                    <i class="icon-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
