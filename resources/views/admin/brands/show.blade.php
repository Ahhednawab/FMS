@extends('layouts.admin')

@section('title', 'Brand Details')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Brand Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.brands.index') }}" class="btn btn-primary"><span>View Brands <i class="icon-list ml-2"></i></span></a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <div class="container mt-3">
          <div class="row">
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Serial No</h5>
                <p>{{ $brand->serial_no }}</p>
              </div>
            </div>
            
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Brand</h5>
                <p>{{ $brand->name }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="text-right mt-3">
            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Back</a>
            <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this city?')">Delete</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
