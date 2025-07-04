@extends('layouts.admin')

@section('title', 'Create Country')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">{{ isset($country) ? 'Edit Country' : 'Add Country' }}</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.countries.index') }}" class="btn btn-primary">
            <span>View Countries <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <form method="POST" action="{{ route('admin.countries.store') }}">
              @csrf
              
              <div class="row">
                <!-- Serial No -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Serial NO</label>
                    <input type="text" name="serial_no" class="form-control" value="{{$serial_no}}" readonly>
                  </div>
                </div>

                <!-- Country Name -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Country Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                    @if ($errors->has('name'))
                      <label class="text-danger">{{ $errors->first('name') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Buttons -->
                <div class="col-md-6 mt-3 text-right">
                  <a href="{{ route('admin.countries.index') }}" class="btn btn-warning">Cancel</a>
                  <button type="submit" class="btn btn-primary">Save</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
