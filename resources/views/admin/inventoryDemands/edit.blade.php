@extends('layouts.admin')

@section('title', 'Edit Inventory Demand')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Inventory Demand</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.inventoryDemands.index') }}" class="btn btn-primary">
            <span>View Inventory Demand <i class="icon-list ml-2"></i></span>
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
            <form method="POST" action="{{ route('admin.inventoryDemands.update', $inventoryDemand->id) }}" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Serial NO</label>
                    <input value="{{$inventoryDemand->serial_no}}" name="serial_no" type="text" class="form-control" readonly>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Requested Date</label>
                    <input type="date" class="form-control" name="request_date" value="{{ old('request_date', $inventoryDemand->request_date ?? '') }}">
                    @if ($errors->has('request_date'))
                      <label class="text-danger">{{ $errors->first('request_date') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Requested By</label>
                    <select class="custom-select" name="requested_by">
                      <option value="">--Select--</option>
                      @foreach($users as $key => $value)
                        <option value="{{$key}}" {{ old('requested_by', $inventoryDemand->requested_by ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('requested_by'))
                      <label class="text-danger">{{ $errors->first('requested_by') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Department</label>
                    <select class="custom-select" name="department_id">
                      <option value="">--Select--</option>
                      @foreach($departments as $key => $value)
                        <option value="{{$key}}" {{ old('department_id', $inventoryDemand->department_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
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
                    <label>Priority</label>
                    <select class="custom-select" name="priority_id">
                      <option value="">--Select--</option>
                      @foreach($priority as $key => $value)
                        <option value="{{$key}}" {{ old('priority_id', $inventoryDemand->priority_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('priority_id'))
                      <label class="text-danger">{{ $errors->first('priority_id') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Status</label>
                    <select class="custom-select" name="status">
                      <option value="">--Select--</option>
                      @foreach($status as $key => $value)
                        <option value="{{$key}}" {{ old('status', $inventoryDemand->status ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
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
                        <option value="{{$key}}" {{ old('product_id', $inventoryDemand->product_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('product_id'))
                      <label class="text-danger">{{ $errors->first('product_id') }}</label>
                    @endif
                  </div>
                </div>
                

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Requested Quantity</label>
                    <input type="number" min="1" step="1" class="form-control" name="requested_qty" value="{{ old('requested_qty', $inventoryDemand->requested_qty ?? '') }}">
                    @if ($errors->has('requested_qty'))
                      <label class="text-danger">{{ $errors->first('requested_qty') }}</label>
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
                        <option value="{{$key}}" {{ old('warehouse_id', $inventoryDemand->warehouse_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('warehouse_id'))
                      <label class="text-danger">{{ $errors->first('warehouse_id') }}</label>
                    @endif
                  </div>
                </div>
                
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Expected Delivery Date</label>
                    <input type="date" class="form-control" name="expected_delivery_date" value="{{ old('expected_delivery_date', $inventoryDemand->expected_delivery_date ?? '') }}">
                    @if ($errors->has('expected_delivery_date'))
                      <label class="text-danger">{{ $errors->first('expected_delivery_date') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-6">
                  <label for=""></label>
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.inventoryDemands.index') }}" class="btn btn-warning">Cancel</a>
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