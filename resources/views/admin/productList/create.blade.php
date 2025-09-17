@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Product Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.productList.index') }}" class="btn btn-primary">
            <span>View Products <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.productList.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
            <!-- Serial No -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Serial No</strong>
                <input value="{{$serial_no}}" name="serial_no" value="{{ old('serial_no') }}" type="text" class="form-control" readonly>
              </div>
            </div>

            <!-- Product Name -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Product Name</strong>
                <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                @if ($errors->has('name'))
                  <label class="text-danger">{{ $errors->first('name') }}</label>
                @endif
              </div>
            </div>

            <!-- Category -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Category</strong>
                <select class="custom-select" name="product_category_id">
                  <option value="">-- Select --</option>
                  @foreach($productCategory as $key => $value)
                    <option value="{{$key}}" {{ old('product_category_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('product_category_id'))
                  <label class="text-danger">{{ $errors->first('product_category_id') }}</label>
                @endif
              </div>
            </div>

            <!-- Brands -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Brands</strong>
                <select class="custom-select" name="brand_id">
                  <option value="">-- Select --</option>
                  @foreach($brands as $key => $value)
                    <option value="{{$key}}" {{ old('brand_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('brand_id'))
                  <label class="text-danger">{{ $errors->first('brand_id') }}</label>
                @endif
              </div>
            </div>

            <!-- Unit -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Unit</strong>
                <select class="custom-select" name="unit_id">
                  <option value="">-- Select --</option>
                  @foreach($units as $key => $value)
                    <option value="{{$key}}" {{ old('unit_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('unit_id'))
                  <label class="text-danger">{{ $errors->first('unit_id') }}</label>
                @endif
              </div>
            </div>
          </div>

          

          <div class="row">
            

            <!-- Buttons -->
            <div class="col-md-12">
              <label></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.productList.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>                    
        </form>
      </div>
    </div>
  </div>

  
@endsection
