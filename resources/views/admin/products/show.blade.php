@extends('layouts.admin')

@section('title', 'Product Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Product Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.products.index') }}" class="btn btn-primary"><span>View Products <i class="icon-list ml-2"></i></span></a>
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
            <!-- Serial No -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Serial No</h5>
                <p>{{$product->serial_no}}</p>
              </div>
            </div>

            <!-- Product Name -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Product Name</h5>
                <p>{{$product->product->name}}</p>
              </div>
            </div>

            <!-- Category -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Category</h5>
                <p>{{$product->productCategory->name}}</p>
              </div>
            </div>

            <!-- Brands -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Brands</h5>
                <p>{{$product->brands}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Purchase Quantity -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Purchase Quantity</h5>
                <p>{{$product->quantity}} {{$product->product->unit}}</p>
              </div>
            </div>

            <!-- Available Quantity -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Available Quantity</h5>
                <p>{{$product->available}} {{$product->product->unit}}</p>
              </div>
            </div>

            <!-- Price -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Price</h5>
                <p>Rs. {{$product->price}}</p>
              </div>
            </div>

            <!-- Alarm Quantity -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Alarm Quantity</h5>
                <p>{{$product->restock_qty_alarm}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Supplier -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Supplier</h5>
                <p>{{$product->supplier->name}}</p>
              </div>
            </div>

            <!-- Warehouse -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Warehouse</h5>
                <p>{{$product->warehouse->name}}</p>
              </div>
            </div>

            <!-- Procured Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Procured Date</h5>
                <p>{{\Carbon\Carbon::parse($product->procured_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Expiry Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Expiry Date</h5>
                <p>{{\Carbon\Carbon::parse($product->expiry_date)->format('d-M-Y')}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Description -->
            <div class="col-md-6 text-center">
              <div class="card">
                <h5 class="m-0">Description</h5>
                <p>{{$product->description}}</p>
              </div>
            </div>
          </div>

          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning">Edit</a>
              <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back</a>
              <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Product?');">
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
