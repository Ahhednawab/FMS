@extends('layouts.admin')

@section('title', 'Warehouse Inventory Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Warehouse Inventory Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.inventoryWarehouses.index') }}" class="btn btn-primary"><span>View Warehouse Inventory <i class="icon-list ml-2"></i></span></a>
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
            <!-- Warehouse Code -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Warehouse Code</h5>
                <p>{{$inventoryWarehouse->serial_no}}</p>
              </div>
            </div>

            <!-- Warehouse Name -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Warehouse Name</h5>
                <p>{{$inventoryWarehouse->warehouse_name}}</p>
              </div>
            </div>

            <!-- Country -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Country</h5>
                <p>{{$inventoryWarehouse->country->name}}</p>
              </div>
            </div>

            <!-- City -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">City</h5>
                <p>{{$inventoryWarehouse->city->name}}</p>
              </div>
            </div>

            <!-- Contact -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Contact</h5>
                <p>{{$inventoryWarehouse->contact}}</p>
              </div>
            </div>

            <!-- Wahehouse Manager -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Wahehouse Manager</h5>
                <p>{{$inventoryWarehouse->warehouseManager->name}}</p>
              </div>
            </div>  

            <!-- Wahehouse type -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Wahehouse type</h5>
                <p>{{$inventoryWarehouse->warehouseType->name}}</p>
              </div>
            </div>  

            <!-- Operating hours -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Operating hours</h5>
                <p>{{ \Carbon\Carbon::parse($inventoryWarehouse->operatingHours->start)->format('h:i A') }} - {{ \Carbon\Carbon::parse($inventoryWarehouse->operatingHours->end)->format('h:i A') }}</p>
              </div>
            </div>

            <!-- Handling Equipment -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Handling Equipment</h5>
                <p>{{$inventoryWarehouse->handlingEquipment->name}}</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.inventoryWarehouses.edit', $inventoryWarehouse->id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.inventoryWarehouses.index') }}" class="btn btn-secondary">Back</a>
                <form action="{{ route('admin.inventoryWarehouses.destroy', $inventoryWarehouse->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Warehouse?');">
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
