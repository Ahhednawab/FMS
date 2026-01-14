@extends('layouts.admin')

@section('title', 'Create Brand')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i>
                    <span class="font-weight-semibold">
                        Brand Management
                    </span>
                </h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>

            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('brands.index') }}" class="btn btn-primary">
                        <span>View Brands <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <!-- Basic layout -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('brands.store') }}">
                            @csrf

                            <div class="row">
                                <!-- Serial No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Serial NO</strong>
                                        <input type="text" name="serial_no" class="form-control"
                                            value="{{ $serial_no }}" readonly>
                                    </div>
                                </div>

                                <!-- Brand Name -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Brand Name</strong>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                            class="form-control">
                                        @error('name')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-6 mt-4 text-right">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <a href="{{ route('brands.index') }}" class="btn btn-warning">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /basic layout -->
            </div>
        </div>
    </div>
    <!-- /content area -->
@endsection
