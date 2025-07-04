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
          <a href="{{ route('admin.products.index') }}" class="btn btn-primary">
            <span>View Products <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
            <!-- Serial No -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Serial No</strong>
                <input value="{{$serial_no}}" name="serial_no" value="{{ old('serial_no') }}" type="text" class="form-control" readonly>
              </div>
            </div>

            <!-- Product Name -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Product Name</strong>
                <select class="custom-select" name="product_id">
                  <option value="">-- Select --</option>
                  @foreach($productList as $key => $value)
                    <option value="{{$key}}" {{ old('product_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('product_id'))
                  <label class="text-danger">{{ $errors->first('product_id') }}</label>
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
            <div class="col-md-3">
              <div class="form-group">
                <strong>Brands</strong>
                <input type="text" class="form-control" name="brands" value="{{ old('brands') }}">
                @if ($errors->has('brands'))
                  <label class="text-danger">{{ $errors->first('brands') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Quantity -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Quantity</strong>
                <input type="number" min="1" step="1" class="form-control" name="quantity" value="{{ old('quantity') }}">
                @if ($errors->has('quantity'))
                  <label class="text-danger">{{ $errors->first('quantity') }}</label>
                @endif
              </div>
            </div>

            <!-- Price -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Price</strong>
                <input type="number" step="1" min="0" class="form-control" name="price" value="{{ old('price') }}">
                @if ($errors->has('price'))
                  <label class="text-danger">{{ $errors->first('price') }}</label>
                @endif
              </div>
            </div>

            <!-- Restock Alarm Quantity -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Restock Alarm Quantity</strong>
                <input type="number" min="1" step="1" class="form-control" name="restock_qty_alarm" value="{{ old('restock_qty_alarm') }}">
                @if ($errors->has('restock_qty_alarm'))
                  <label class="text-danger">{{ $errors->first('restock_qty_alarm') }}</label>
                @endif
              </div>
            </div>

            <!-- Suppliers -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Suppliers</strong>
                <select class="custom-select" name="supplier_id">
                  <option value="">-- Select --</option>
                  @foreach($suppliers as $key => $value)
                    <option value="{{$key}}" {{ old('supplier_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('supplier_id'))
                  <label class="text-danger">{{ $errors->first('supplier_id') }}</label>
                @endif
              </div>
            </div>

            <!-- Warehouse -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Warehouse</strong>
                <select class="custom-select" id="warehouse_id" name="warehouse_id">
                  <option value="">--Select--</option>
                  @foreach($warehouse as $key => $value)
                    <option value="{{$key}}" {{ old('warehouse_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('warehouse_id'))
                  <label class="text-danger">{{ $errors->first('warehouse_id') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Procured Date -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Procured Date</strong>
                <input type="date" class="form-control" name="procured_date" value="{{ old('procured_date') }}">
                @if ($errors->has('procured_date'))
                  <label class="text-danger">{{ $errors->first('procured_date') }}</label>
                @endif
              </div>
            </div>

            <!-- Expiry Date -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Expiry Date</strong>
                <input type="date" class="form-control" name="expiry_date" value="{{ old('expiry_date') }}">
                @if ($errors->has('expiry_date'))
                  <label class="text-danger">{{ $errors->first('expiry_date') }}</label>
                @endif
              </div>
            </div>

            <!-- Product Type -->
            <div class="col-md-6">
              <div class="form-group">
                <strong>Description</strong>
                <textarea class="form-control" name="description" rows="1">{{ old('description') }}</textarea>
                @if ($errors->has('description'))
                  <label class="text-danger">{{ $errors->first('description') }}</label>
                @endif
              </div>
            </div>

            <!-- Buttons -->
            <div class="col-md-2">
              <label></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>                    
        </form>
      </div>
    </div>
  </div>

  
@endsection
