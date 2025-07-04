@extends('layouts.admin')

@section('title', 'Edit Vendor')

@section('content')

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Vendor</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.vendors.index') }}" class="btn btn-primary">
            <span>View Vendors <i class="icon-list ml-2"></i></span>
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
            <form method="POST" action="{{ route('admin.vendors.update', $vendor->id) }}">
              @csrf
              @method('PUT')

              <div class="row">
                <!-- Serial No -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Serial No.</strong>
                    <input value="{{$vendor->serial_no}}" name="serial_no" type="text" class="form-control" readonly>
                  </div>
                </div>

                <!-- Name -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Name</strong>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $vendor->name ?? '') }}">
                    @if ($errors->has('name'))
                      <label class="text-danger">{{ $errors->first('name') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Phone No -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Phone No</strong>
                    <input type="text" name="phone" id="phone" class="form-control"
                         value="{{ old('phone', $vendor->phone , '03' ?? '') }}"
                         maxlength="12" placeholder="03xx-xxxxxxx">
                    @if ($errors->has('phone'))
                      <label class="text-danger">{{ $errors->first('phone') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Type -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Type</strong>
                    <select class="custom-select" name="vendor_type_id">
                      <option value="">Select Type</option>
                      @foreach($vendor_types as $key => $value)
                        <option value="{{$key}}" {{ old('vendor_type_id', $vendor->vendor_type_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('vendor_type_id'))
                      <label class="text-danger">{{ $errors->first('vendor_type_id') }}</label>
                    @endif
                  </div>
                </div>
              </div>

              <div class="row">
                <!-- City -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>City</strong>
                    <select class="custom-select" name="city_id">
                      <option value="">Select City</option>
                      @foreach($cities as $key => $value)
                        <option value="{{$value->id}}" {{ old('city_id', $vendor->city_id ?? '') == $value->id ? 'selected' : '' }}>{{$value->name}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('city_id'))
                      <label class="text-danger">{{ $errors->first('city_id') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Description -->
                <div class="col-md-6">
                  <div class="form-group">
                    <strong>Description</strong>
                    <textarea class="form-control" name="description" rows="1">{{ old('description', $vendor->description ?? '') }}</textarea>
                    @if ($errors->has('description'))
                      <label class="text-danger">{{ $errors->first('description') }}</label>
                    @endif
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <label for=""></label>
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-warning">Cancel</a>
                  </div>
                </div>            
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
