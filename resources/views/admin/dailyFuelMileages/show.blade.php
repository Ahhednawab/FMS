@extends('layouts.admin')

@section('title', 'Daily Fuel Mileage Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Fuel Mileage Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.dailyFuelMileages.index') }}" class="btn btn-primary"><span>Daily Fuel Mileage  <i class="icon-list ml-2"></i></span></a>
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
                <p>{{$dailyFuelMileage->serial_no}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Vehicle No</h5>
                <p>{{$dailyFuelMileage->vehicle->vehicle_no}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Current Reading</h5>
                <p>{{$dailyFuelMileage->current_reading}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Previous Reading</h5>
                <p>{{$dailyFuelMileage->previous_reading}}</p>
              </div>
            </div>
          </div>

          <div class="row">             
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Consumption</h5>
                <p>{{$dailyFuelMileage->consumption}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Fuel Station</h5>
                <p>{{$dailyFuelMileage->fuelStation->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Destination</h5>
                <p>{{$dailyFuelMileage->destination->name}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Date</h5>
                <p>{{\Carbon\Carbon::parse($dailyFuelMileage->date)->format('d-M-Y')}}</p>
              </div>
            </div>
          </div>
          <div class="row">             
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Location</h5>
                <p>{{$dailyFuelMileage->location}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Difference</h5>
                <p>{{$dailyFuelMileage->difference}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Fuel Taken</h5>
                <p>{{$dailyFuelMileage->fuel_taken}}</p>
              </div>
            </div>

            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Driver Name</h5>
                <p>{{$dailyFuelMileage->driver_name}}</p>
              </div>
            </div>              
          </div>
          <!-- /basic datatable -->
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.dailyFuelMileages.edit', $dailyFuelMileage->id) }}" class="btn btn-warning">Edit</a>
              <a href="{{ route('admin.dailyFuelMileages.index') }}" class="btn btn-secondary">Back</a>
              <form action="{{ route('admin.dailyFuelMileages.destroy', $dailyFuelMileage->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Fuel Mileage Report?');">
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
