@extends('layouts.admin')

@section('title', 'Insurance Company Detail')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Insurance Company Detail</span>
                </h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('insurance-companies.index') }}" class="btn btn-primary">
                        <span>View Insurance Companies <i class="icon-list ml-2"></i></span>
                    </a>
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
                        <!-- Serial No -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Serial No</h5>
                                <p>{{ $insurance_company->serial_no }}</p>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="col-md-6 text-center">
                            <div class="card">
                                <h5 class="m-0">Name</h5>
                                <p>{{ $insurance_company->name }}</p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Status</h5>
                                <span class="badge badge-{{ $insurance_company->is_active ? 'success' : 'danger' }}">
                                    {{ $insurance_company->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="text-right">
                            <a href="{{ route('insurance-companies.edit', $insurance_company->id) }}"
                                class="btn btn-warning">
                                <i class="icon-pencil7 mr-1"></i> Edit
                            </a>
                            <a href="{{ route('insurance-companies.index') }}" class="btn btn-secondary">
                                <i class="icon-arrow-left16 mr-1"></i> Back
                            </a>
                            <form action="{{ route('insurance-companies.destroy', $insurance_company->id) }}" method="POST"
                                style="display:inline-block;"
                                onsubmit="return confirm('Are you sure you want to delete this Insurance Company?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="icon-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /content area -->
@endsection
