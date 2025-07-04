@extends('layouts.admin')

@section('title', 'IBC Centers Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">IBC Centers Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.ibcCenters.index') }}" class="btn btn-primary"><span>View IBC Centers <i class="icon-list ml-2"></i></span></a>
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
            <!-- Serial NO -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Serial No</h5>
                <p>{{ $ibcCenter->serial_no }}</p>
              </div>
            </div>

            <!-- Station -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Station</h5>
                <p>{{ $ibcCenter->station->area }}</p>
              </div>
            </div>

            <!-- IBC Center Name -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">IBC Center Name</h5>
                <p>{{ $ibcCenter->name }}</p>
              </div>
            </div>
          </div>
         
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.ibcCenters.edit', $ibcCenter->id) }}" class="btn btn-warning">Edit</a>
              <a href="{{ route('admin.ibcCenters.index') }}" class="btn btn-secondary">Back</a>
              <form action="{{ route('admin.ibcCenters.destroy', $ibcCenter->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this driver?');">
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
