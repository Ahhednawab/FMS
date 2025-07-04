@extends('layouts.admin')

@section('title', 'Edit Inventory Dispatch')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Inventory Dispatch</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.inventoryDispatchs.index') }}" class="btn btn-primary">
            <span>View Inventory Dispatch <i class="icon-list ml-2"></i></span>
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
            <form method="POST" action="{{ route('admin.inventoryDispatchs.update', $inventoryDispatch->id) }}" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Serial NO</label>
                    <input value="{{$inventoryDispatch->serial_no}}" name="serial_no" type="text" class="form-control" readonly>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Dispatch Date</label>
                    <input type="date" class="form-control" name="dispatch_date" value="{{ old('dispatch_date', $inventoryDispatch->dispatch_date ?? '') }}">
                    @if ($errors->has('dispatch_date'))
                      <label class="text-danger">{{ $errors->first('dispatch_date') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Dispatched By</label>
                    <select class="custom-select" name="dispatched_by">
                      <option value="">--Select--</option>
                      @foreach($users as $key => $value)
                        <option value="{{$key}}" {{ old('dispatched_by', $inventoryDispatch->dispatched_by ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('dispatched_by'))
                      <label class="text-danger">{{ $errors->first('dispatched_by') }}</label>
                    @endif
                  </div>
                </div>
                
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Department</label>
                    <select class="custom-select" name="department_id">
                      <option value="">--Select--</option>
                      @foreach($departments as $key => $value)
                        <option value="{{$key}}" {{ old('department_id', $inventoryDispatch->department_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('department_id'))
                      <label class="text-danger">{{ $errors->first('department_id') }}</label>
                    @endif
                  </div>
                </div>           
              </div>

              <div class="row">
                
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Location</label>
                    <input type="text" class="form-control" name="location" value="{{ old('location', $inventoryDispatch->location ?? '') }}">
                    @if ($errors->has('location'))
                      <label class="text-danger">{{ $errors->first('location') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Dispatch Type</label>
                    <select class="custom-select" name="dispatch_type">
                      <option value="">--Select--</option>
                      @foreach($dispatchTypes as $key => $value)
                        <option value="{{$key}}" {{ old('dispatch_type', $inventoryDispatch->dispatch_type ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('dispatch_type'))
                      <label class="text-danger">{{ $errors->first('dispatch_type') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Status</label>
                    <select class="custom-select" name="status">
                      <option value="">--Select--</option>
                      @foreach($status as $key => $value)
                        <option value="{{$key}}" {{ old('status', $inventoryDispatch->status ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('status'))
                      <label class="text-danger">{{ $errors->first('status') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Product Name</label>
                    <select class="custom-select" name="product_id">
                      <option value="">--Select--</option>
                      @foreach($products as $key => $value)
                        <option value="{{$key}}" {{ old('product_id', $inventoryDispatch->product_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('product_id'))
                      <label class="text-danger">{{ $errors->first('product_id') }}</label>
                    @endif
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Warehouse</label>
                    <select class="custom-select" name="warehouse_id">
                      <option value="">--Select--</option>
                      @foreach($warehouse as $key => $value)
                        <option value="{{$key}}" {{ old('warehouse_id', $inventoryDispatch->warehouse_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('warehouse_id'))
                      <label class="text-danger">{{ $errors->first('warehouse_id') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order Price</label>
                    <input type="number" min="1" step="1" class="form-control" name="order_price" value="{{ old('order_price', $inventoryDispatch->order_price ?? '') }}">
                    @if ($errors->has('order_price'))
                      <label class="text-danger">{{ $errors->first('order_price') }}</label>
                    @endif
                  </div>
                </div>
                
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Dispatched Quantity</label>
                    <input type="number" min="1" step="1" class="form-control" name="dispatched_qty" value="{{ old('dispatched_qty', $inventoryDispatch->dispatched_qty ?? '') }}">
                    @if ($errors->has('dispatched_qty'))
                      <label class="text-danger">{{ $errors->first('dispatched_qty') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <label for=""></label>
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('admin.inventoryDispatchs.index') }}" class="btn btn-warning">Cancel</a>
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