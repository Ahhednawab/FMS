@extends('layouts.admin')

@section('title', 'Daily Mileage Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Mileage Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.dailyMileages.index') }}" class="btn btn-primary"><span>View Daily Mileage <i class="icon-list ml-2"></i></span></a>
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
                <p>{{$dailyMileage->serial_no}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Vehicle No</h5>
                <p>{{$dailyMileage->vehicle->vehicle_no}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Date</h5>
                <p>{{\Carbon\Carbon::parse($dailyMileage->date)->format('d-M-Y')}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Month</h5>
                <p>month</p>
              </div>
            </div>
          </div>

          <div class="row">             
            <div class="col-md-6 text-center">
              <div class="card">
                <h5 class="m-0">Current Month Total KMs (March)</h5>
                <p>{{$dailyMileage->last_third_month_km}}</p>
              </div>
            </div>
            <div class="col-md-6 text-center">
              <div class="card">
                <h5 class="m-0">Current Month Total KMs (April)</h5>
                <p>{{$dailyMileage->last_second_month_km}}</p>
              </div>
            </div>
            
          </div>

          <div class="row">
            
            <div class="col-md-6 text-center">
              <div class="card">
                <h5 class="m-0">Current Month Total KMs (May)</h5>
                <p>{{$dailyMileage->last_month_km}}</p>
              </div>
            </div>
            <div class="col-md-6 text-center">
              <div class="card">
                <h5 class="m-0">Current Month Total KMs (Particular Month)</h5>
                <p>{{$dailyMileage->current_month_km}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Location</h5>
                <p>{{$dailyMileage->location}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Remarks</h5>
                <p>{{$dailyMileage->remarks}}</p>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Milage</h5>
                <p>{{$dailyMileage->mileageStatus->name}}</p>
              </div>
            </div>
          </div>
                          
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.dailyMileages.edit', $dailyMileage->id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.dailyMileages.index') }}" class="btn btn-secondary">Back</a>
                <form action="{{ route('admin.dailyMileages.destroy', $dailyMileage->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Daily Mileage?');">
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
