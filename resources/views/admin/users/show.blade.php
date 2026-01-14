@extends('layouts.admin')

@section('title', 'User Detail')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">User Detail</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('users.index') }}" class="btn btn-primary"><span>View Users <i
                                class="icon-list ml-2"></i></span></a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div class="content">
        <div class="card">
            <div class="card-body">
                <div class="container mt-3">
                    <div class="row">
                        <!-- Serial no -->
                        <div class="col-md-3 text-center">
                            <div class="card p-2">
                                <h5 class="m-0">Serial No</h5>
                                <p>{{ $user->serial_no }}</p>
                            </div>
                        </div>

                        <!-- Full Name -->
                        <div class="col-md-3 text-center">
                            <div class="card p-2">
                                <h5 class="m-0">Full Name</h5>
                                <p>{{ $user->name }}</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-3 text-center">
                            <div class="card p-2">
                                <h5 class="m-0">Email</h5>
                                <p>{{ $user->email }}</p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-3 text-center">
                            <div class="card p-2">
                                <h5 class="m-0">Phone</h5>
                                <p>{{ $user->phone }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <!-- Role -->
                        <div class="col-md-3 text-center">
                            <div class="card p-2">
                                <h5 class="m-0">Role</h5>
                                <p>{{ $user->role->name }}</p>
                            </div>
                        </div>

                        <!-- Designation -->
                        <div class="col-md-3 text-center">
                            <div class="card p-2">
                                <h5 class="m-0">Designation</h5>
                                <p>{{ $user->designation->designation }}</p>
                            </div>
                        </div>

                        <!-- Full Address -->
                        <div class="col-md-3 text-center">
                            <div class="card p-2">
                                <h5 class="m-0">Full Address</h5>
                                <p>{{ $user->address }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 text-right ">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                            style="display:inline-block;"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /content area -->
@endsection
