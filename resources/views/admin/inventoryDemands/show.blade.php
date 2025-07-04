@extends('layouts.admin')

@section('title', 'Inventory Demand Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Inventory Demand Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.inventoryDemands.index') }}" class="btn btn-primary"><span>View Inventory Demand <i class="icon-list ml-2"></i></span></a>
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
                <p>{{$inventoryDemand->serial_no}}</p>
              </div>
            </div>

            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Requested Date</h5>
                <p>{{ \Carbon\Carbon::parse($inventoryDemand->request_date)->format('d-M-Y') }}</p>
              </div>
            </div>

            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Priority</h5>
                <p>{{$inventoryDemand->priority->name}}</p>
              </div>
            </div>

            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Status</h5>
                <p>{{$inventoryDemand->inventoryStatus->name}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Warehouse</h5>
                <p>{{$inventoryDemand->warehouse->name}}</p>
              </div>
            </div>

            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Requested Quantity</h5>
                <p>{{$inventoryDemand->requested_qty}}</p>
              </div>
            </div>

            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Requested By</h5>
                <p>{{$inventoryDemand->requestedBy->name}}</p>
              </div>
            </div>

            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Department</h5>
                <p>{{$inventoryDemand->department->name}}</p>
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Product Name</h5>
                <p>{{$inventoryDemand->product->product_name}}</p>
              </div>
            </div>

            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Product Price</h5>
                <p>{{$inventoryDemand->product_price}}</p>
              </div>
            </div>

            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Expected Delivery Date</h5>
                <p>{{ \Carbon\Carbon::parse($inventoryDemand->expected_delivery_date)->format('d-M-Y') }}</p>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.inventoryDemands.edit', $inventoryDemand->id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.inventoryDemands.index') }}" class="btn btn-secondary">Back</a>
                <form action="{{ route('admin.inventoryDemands.destroy', $inventoryDemand->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Inventorty Demand?');">
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
