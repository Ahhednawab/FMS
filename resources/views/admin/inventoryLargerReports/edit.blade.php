@extends('layouts.admin')

@section('title', 'Edit Inventory Larger Report')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Inventory Larger Report</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.inventoryLargerReports.index') }}" class="btn btn-primary">
            <span>View Inventory Larger Report <i class="icon-list ml-2"></i></span>
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
            <form method="POST" action="{{ route('admin.inventoryLargerReports.update', $inventoryLargerReport->id) }}" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Report ID</label>
                    <input value="{{$inventoryLargerReport->report_id}}" name="report_id" type="text" class="form-control" readonly>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Report Date</label>
                    <input type="date" class="form-control" name="report_date" value="{{ old('report_date', $inventoryLargerReport->report_date ?? '') }}">
                    @if ($errors->has('report_date'))
                      <label class="text-danger">{{ $errors->first('report_date') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Product Name</label>
                    <select class="custom-select" name="product_id">
                      <option value="">--Select--</option>
                      @foreach($products as $key => $value)
                        <option value="{{$key}}" {{ old('product_id', $inventoryLargerReport->product_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('product_id'))
                      <label class="text-danger">{{ $errors->first('product_id') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Warehouse</label>
                    <select class="custom-select" name="warehouse_id">
                      <option value="">--Select--</option>
                      @foreach($warehouse as $key => $value)
                        <option value="{{$key}}" {{ old('warehouse_id', $inventoryLargerReport->warehouse_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('warehouse_id'))
                      <label class="text-danger">{{ $errors->first('warehouse_id') }}</label>
                    @endif
                  </div>
                </div>            
              </div>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Category</label>
                    <select class="custom-select" name="category">
                      <option value="">--Select--</option>
                      @foreach($category as $key => $value)
                        <option value="{{$key}}" {{ old('category', $inventoryLargerReport->category ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('category'))
                      <label class="text-danger">{{ $errors->first('category') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Location</label>
                    <input type="text" class="form-control" name="location" value="{{ old('location', $inventoryLargerReport->location ?? '') }}">
                    @if ($errors->has('location'))
                      <label class="text-danger">{{ $errors->first('location') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Transaction Type</label>
                    <select class="custom-select" name="transaction_type">
                      <option value="">--Select--</option>
                      @foreach($transactionTypes as $key => $value)
                        <option value="{{$key}}" {{ old('transaction_type', $inventoryLargerReport->transaction_type ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('transaction_type'))
                      <label class="text-danger">{{ $errors->first('transaction_type') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Supplier</label>
                    <select class="custom-select" name="supplier">
                      <option value="">--Select--</option>
                      @foreach($suppliers as $key => $value)
                        <option value="{{$key}}" {{ old('supplier', $inventoryLargerReport->supplier ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('supplier'))
                      <label class="text-danger">{{ $errors->first('supplier') }}</label>
                    @endif
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order Quantity</label>
                    <input type="number" min="1" step="1" class="form-control" name="order_quantity" value="{{ old('order_quantity', $inventoryLargerReport->order_quantity ?? '') }}">
                    @if ($errors->has('order_quantity'))
                      <label class="text-danger">{{ $errors->first('order_quantity') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order Price</label>
                    <input type="number" min="1" step="1" class="form-control" name="order_price" value="{{ old('order_price', $inventoryLargerReport->warehouse_id ?? '') }}">
                    @if ($errors->has('order_price'))
                      <label class="text-danger">{{ $errors->first('order_price') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Status</label>
                    <select class="custom-select" name="status">
                      <option value="">--Select--</option>
                      @foreach($status as $key => $value)
                        <option value="{{$key}}" {{ old('status') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('status'))
                      <label class="text-danger">{{ $errors->first('status') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Delivery Date</label>
                    <input type="date" class="form-control" name="delievery_date" value="{{ old('delievery_date') }}">
                    @if ($errors->has('delievery_date'))
                      <label class="text-danger">{{ $errors->first('delievery_date') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-12">
                  <label for=""></label>
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.inventoryLargerReports.index') }}" class="btn btn-warning">Cancel</a>
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