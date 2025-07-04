@extends('layouts.admin')

@section('title', 'Inventory Dispatch Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Inventory Dispatch Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.inventoryDispatchs.index') }}" class="btn btn-primary"><span>View Inventory Dispatch <i class="icon-list ml-2"></i></span></a>
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
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Serial no</h5>
                <p>{{$inventoryDispatch->serial_no}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Dispatch Date</h5>
                <p>{{ \Carbon\Carbon::parse($inventoryDispatch->dispatch_date)->format('d-M-Y') }}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Department</h5>
                <p>{{$inventoryDispatch->department->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Location</h5>
                <p>{{$inventoryDispatch->location}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Product Name</h5>
                <p>{{$inventoryDispatch->product->product_name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Order Price</h5>
                <p>{{$inventoryDispatch->order_price}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Dispatched By</h5>
                <p>{{$inventoryDispatch->dispatchBy->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Designation</h5>
                <p>{{$inventoryDispatch->dispatchBy->designation->designation}}</p>
              </div>
            </div>
          </div>

          <div class="row">            
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Dispatch Type</h5>
                <p>{{$inventoryDispatch->dispatchType->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Status</h5>
                <p>{{$inventoryDispatch->inventoryDispatchStatus->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Warehouse</h5>
                <p>{{$inventoryDispatch->warehouse->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Dispatched Quantity</h5>
                <p>{{$inventoryDispatch->dispatched_qty}}</p>
              </div>
            </div>
          </div>

          <!-- /basic datatable -->
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.inventoryDispatchs.edit', $inventoryDispatch->id) }}" class="btn btn-warning">Edit</a>
              <a href="{{ route('admin.inventoryDispatchs.index') }}" class="btn btn-secondary">Back</a>
              <form action="{{ route('admin.inventoryDispatchs.destroy', $inventoryDispatch->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Inventory Dispatch?');">
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
