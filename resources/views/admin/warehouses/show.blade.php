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
          <a href="{{ route('admin.warehouses.index') }}" class="btn btn-primary"><span>View Warehouse <i class="icon-list ml-2"></i></span></a>
        </div>
      </div>
    </div>
  </div>
  <!-- /page header -->

  <!-- Content area -->
  <div class="content">
    
    <div class="card">
      <div class="container mt-3">
        <div class="row">
          <!-- Serial No -->
          <div class="col-md-3 text-center">
            <div class="card">
              <h5 class="m-0">Serial No</h5>
              <p>{{ $warehouse->serial_no }}</p>
            </div>
          </div>

          <!-- Warehouse Name -->
          <div class="col-md-3 text-center">
            <div class="card">
              <h5 class="m-0">Warehouse Name</h5>
              <p>{{ $warehouse->name }}</p>
            </div>
          </div>

          <!-- Supervisor Name -->
          <div class="col-md-3 text-center">
            <div class="card">
              <h5 class="m-0">Supervisor Name</h5>
              <p>{{ $warehouse->supervisor->name }}</p>
            </div>
          </div>

          <!-- Country -->
          <div class="col-md-3 text-center">
            <div class="card">
              <h5 class="m-0">Country</h5>
              <p>{{ $warehouse->country->name ?? 'N/A' }}</p>
            </div>
          </div>

          <!-- City -->
          <div class="col-md-3 text-center">
            <div class="card">
              <h5 class="m-0">City</h5>
              <p>{{ $warehouse->city->name ?? 'N/A' }}</p>
            </div>
          </div>

          <!-- Station -->
          <div class="col-md-3 text-center">
            <div class="card">
              <h5 class="m-0">Station</h5>
              <p>{{ $warehouse->station }}</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <div class="text-right">
          <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-warning">Edit</a>
          <a href="{{ route('admin.warehouses.index') }}" class="btn btn-secondary">Back</a>
          <form method="POST" action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" style="display:inline;">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Delete</button>
          </form>
        </div>
        <br>
      </div>
    </div>
  </div>
  <!-- /content area -->
@endsection
