@extends('layouts.admin')

@section('title', 'Vehicle Maintenance Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Maintenance Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.vehicleMaintenances.index') }}" class="btn btn-primary"><span>View Vehicle Maintenance <i class="icon-list ml-2"></i></span></a>
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
            <!-- Maintenance ID -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Maintenance ID</h5>
                <p>{{$vehicleMaintenance->maintenance_id}}</p>
              </div>
            </div>

            <!-- Vehicle No -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Vehicle No</h5>
                <p>{{$vehicleMaintenance->vehicle->vehicle_no}}</p>
              </div>
            </div>

            <!-- Model -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Model</h5>
                <p>{{$vehicleMaintenance->model}}</p>
              </div>
            </div>

            <!-- Odometer Reading -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Odometer Reading</h5>
                <p>{{$vehicleMaintenance->odometer_reading}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Fuel Type -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Fuel Type</h5>
                <p>{{$vehicleMaintenance->fuelType->fuel_type}}</p>
              </div>
            </div>

            <!-- Maintenance Category -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Maintenance Category</h5>
                <p>{{$vehicleMaintenance->maintenanceCategory->category}}</p>
              </div>
            </div>

            <!-- Service Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Service Date</h5>
                <p>{{ \Carbon\Carbon::parse($vehicleMaintenance->service_date)->format('d-M-Y') }}</p>
              </div>
            </div>

            <!-- Service Provider -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Service Provider</h5>
                <p>{{$vehicleMaintenance->serviceProvider->name}}</p>
              </div>
            </div>
          </div>
          
          <div class="row">
            <!-- Parts Replaced -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Parts Replaced</h5>
                <p>{{$vehicleMaintenance->parts->name}}</p>
              </div>
            </div>

            <!-- Cost of Service -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Cost of Service</h5>
                <p>{{$vehicleMaintenance->service_cost}}</p>
              </div>
            </div>

            <!-- Service Description -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Service Description</h5>
                <p>{{$vehicleMaintenance->service_description}}</p>
              </div>
            </div>
          </div>

          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.vehicleMaintenances.edit', $vehicleMaintenance->id) }}" class="btn btn-warning">Edit</a>
              <a href="{{ route('admin.vehicleMaintenances.index') }}" class="btn btn-secondary">Back</a>
              <form action="{{ route('admin.vehicleMaintenances.destroy', $vehicleMaintenance->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Vehicle Maintenance?');">
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
