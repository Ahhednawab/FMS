@extends('layouts.admin')

@section('title', 'Inventory Larger Report Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Inventory Larger Report Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.inventoryLargerReports.index') }}" class="btn btn-primary"><span>View Inventory Larger Report <i class="icon-list ml-2"></i></span></a>
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
                <h5 class="m-0">Report ID</h5>
                <p>{{$inventoryLargerReport->report_id}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Report Date</h5>
                <p>{{ \Carbon\Carbon::parse($inventoryLargerReport->report_date)->format('d-M-Y') }}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Product Name</h5>
                <p>{{$inventoryLargerReport->product->product_name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Warehouse</h5>
                <p>{{$inventoryLargerReport->warehouse->name}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Category</h5>
                <p>{{$inventoryLargerReport->inventoryLargerReportCategory->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Location</h5>
                <p>{{$inventoryLargerReport->location}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Transaction Type</h5>
                <p>{{$inventoryLargerReport->transactionType->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Supplier</h5>
                <p>{{$inventoryLargerReport->supplier->name}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Order Quantity</h5>
                <p>{{$inventoryLargerReport->order_quantity}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Order Price</h5>
                <p>{{$inventoryLargerReport->order_price}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Status</h5>
                <p>{{$inventoryLargerReport->inventoryLargerReportStatus->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Delivery Date</h5>
                <p>{{ \Carbon\Carbon::parse($inventoryLargerReport->delivery_date)->format('d-M-Y') }}</p>
              </div>
            </div>
          </div>

          <!-- /basic datatable -->
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.inventoryLargerReports.edit', $inventoryLargerReport->id) }}" class="btn btn-warning">Edit</a>
              <a href="{{ route('admin.inventoryLargerReports.index') }}" class="btn btn-secondary">Back</a>
              <form action="{{ route('admin.inventoryLargerReports.destroy', $inventoryLargerReport->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Inventory Larger Report?');">
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
