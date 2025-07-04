@extends('layouts.admin')

@section('title', 'Country Detail')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Country Detail</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.countries.index') }}" class="btn btn-primary"><span>View Countries <i class="icon-list ml-2"></i></span></a>
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
                    <div class="card">
                        <h5 class="m-0">Serial no</h5>
                        <p>{{ $country->serial_no }}</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="card">
                        <h5 class="m-0">Country</h5>
                        <p>{{ $country->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mt-3">
            <div class="text-right">
                <a href="{{ route('admin.countries.edit', $country->id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.countries.index') }}" class="btn btn-secondary">Back</a>
                <form action="{{ route('admin.countries.destroy', $country->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </div>
        </div>
      </div>
    </div>
  </div>
@endsection
