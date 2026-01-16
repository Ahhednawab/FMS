@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Create Role</span></h4>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">New Role Information</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('roles.store') }}">
                            @csrf

                            <!-- Role Name -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Role Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Enter role name (e.g., Manager, Editor)" value="{{ old('name') }}"
                                    required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Enter role description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>


                            <!-- Actions -->
                            <div class="text-right">
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Create Role
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
