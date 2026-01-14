@extends('layouts.admin')

@section('title', 'Vendor Detail')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vendor Detail</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('vendors.index') }}" class="btn btn-primary"><span>View Vendors <i
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
                        <!-- Serial No -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Serial No</h5>
                                <p>{{ $vendor->serial_no }}</p>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Name</h5>
                                <p>{{ $vendor->name }}</p>
                            </div>
                        </div>

                        <!-- Phone No -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Phone No </h5>
                                <p>{{ $vendor->phone }}</p>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Type</h5>
                                <p>{{ $vendor->vendorType->name }}</p>
                            </div>
                        </div>

                        <!-- City -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">City</h5>
                                <p>{{ $vendor->city->name }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-6 text-center">
                            <div class="card">
                                <h5 class="m-0">Description</h5>
                                <p>{{ $vendor->description }}</p>
                            </div>
                        </div>
                    </div>


                </div>



                <div class="row">
                    <div class="col-md-12">
                        <label for=""></label>
                        <div class="text-right">
                            <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-warning">Edit</a>
                            <a href="{{ route('vendors.index') }}" class="btn btn-secondary">Back</a>
                            <form action="{{ route('vendors.destroy', 1) }}" method="POST" style="display:inline-block;"
                                onsubmit="return confirm('Are you sure you want to delete this Vendor?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /content area -->
@endsection
