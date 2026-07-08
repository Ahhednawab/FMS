@extends('layouts.admin') {{-- include your layout here --}}

@section('title', 'Warehouse Detail')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Warehouse Detail</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('warehouses.index') }}" class="btn btn-primary"><span>View Warehouse <i
                                class="icon-list ml-2"></i></span></a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div class="content">

        {{-- Master / Sub-Warehouse Role Banner --}}
        @if ($warehouse->is_master)
            <div class="alert alert-success" role="alert" style="border-left: 4px solid #28a745;">
                <i class="icon-star-full2 mr-2 text-warning"></i>
                <strong>This is the Master Warehouse</strong> — All inventory is received here first before distribution to Sub-Warehouses.
            </div>
        @else
            <div class="alert alert-secondary" role="alert" style="border-left: 4px solid #6c757d;">
                <i class="icon-warehouse mr-2"></i>
                <strong>Sub-Warehouse</strong> — Receives allocated stock from the Master Warehouse.
            </div>
        @endif

        <div class="card">
            <div class="container mt-3">
                <div class="row">
                    <!-- Serial No -->
                    <div class="col-md-3 text-center mb-3">
                        <div class="card p-3">
                            <h5 class="m-0">Serial No</h5>
                            <p class="mt-1 mb-0">{{ $warehouse->serial_no }}</p>
                        </div>
                    </div>

                    <!-- Warehouse Name -->
                    <div class="col-md-3 text-center mb-3">
                        <div class="card p-3">
                            <h5 class="m-0">Warehouse Name</h5>
                            <p class="mt-1 mb-0">{{ $warehouse->name }}</p>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="col-md-3 text-center mb-3">
                        <div class="card p-3">
                            <h5 class="m-0">Role</h5>
                            <p class="mt-1 mb-0">
                                @if ($warehouse->is_master)
                                    <span class="badge badge-success">
                                        <i class="icon-star-full2 mr-1"></i> Master Warehouse
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Sub-Warehouse</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Warehouse Manager -->
                    <div class="col-md-3 text-center mb-3">
                        <div class="card p-3">
                            <h5 class="m-0">Warehouse Manager</h5>
                            <p class="mt-1 mb-0">{{ $warehouse->manager?->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Station -->
                    <div class="col-md-3 text-center mb-3">
                        <div class="card p-3">
                            <h5 class="m-0">Station</h5>
                            <p class="mt-1 mb-0">{{ $warehouse->station?->area ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-3 text-center mb-3">
                        <div class="card p-3">
                            <h5 class="m-0">Status</h5>
                            <p class="mt-1 mb-0">
                                @if ($warehouse->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="text-right mb-3">
                    <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="btn btn-warning">Edit</a>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Back</a>
                    @if (!$warehouse->is_master)
                        <form action="{{ route('warehouses.setMaster', $warehouse->id) }}" method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('Set this warehouse as the Master Warehouse?');">
                            @csrf
                            <button class="btn btn-success">
                                <i class="icon-star-full2 mr-1"></i> Set as Master Warehouse
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('warehouses.destroy', $warehouse->id) }}"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to delete this warehouse?')">Delete</button>
                    </form>
                </div>
                <br>
            </div>
        </div>
    </div>
    <!-- /content area -->
@endsection
