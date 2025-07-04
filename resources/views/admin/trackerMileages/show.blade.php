@extends('layouts.admin')

@section('title', 'Tracker Mileage Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Tracker Mileage Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.trackerMileages.index') }}" class="btn btn-primary"><span>View Tracker Mileage <i class="icon-list ml-2"></i></span></a>
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
                <p>{{$trackerMilage->serial_no}}</p>
              </div>
            </div>

            <!-- Vehicle No -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Vehicle No</h5>
                <p>{{$trackerMilage->vehicle->vehicle_no}}</p>
              </div>
            </div>

            <!-- Day -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Day</h5>
                <p>{{$trackerMilage->days->name}}</p>
              </div>
            </div>

            <!-- Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Date</h5>
                <p>{{ \Carbon\Carbon::parse($trackerMilage->date)->format('d-M-Y') }}</p>
              </div>
            </div>

            <!-- AKPL -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">AKPL</h5>
                <p>{{$trackerMilage->akpl}}</p>
              </div>
            </div>

            <!-- IBC/Center -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">IBC/Center</h5>
                <p>{{$trackerMilage->ibcCenter->name}}</p>
              </div>
            </div>

            <!-- Before Peak 01 Hour -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Before Peak 01 Hour</h5>
                <p>{{$trackerMilage->before_peak_one_hour}}</p>
              </div>
            </div>

            <!-- Before Peak 02 Hour -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Before Peak 02 Hour</h5>
                <p>{{$trackerMilage->before_peak_two_hour}}</p>
              </div>
            </div>

            <!-- KMS Driven Peak -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">KMS Driven Peak</h5>
                <p>{{$trackerMilage->kms_driven_peak}}</p>
              </div>
            </div>

            <!-- KMS Driven Off Peak -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">KMS Driven Off Peak</h5>
                <p>{{$trackerMilage->kms_driven_off_peak}}</p>
              </div>
            </div>

            <!-- Total KMs In a day -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Total KMs In a day</h5>
                <p>{{$trackerMilage->total_kms_in_a_day}}</p>
              </div>
            </div>

            <!-- After Peak 01 Hour -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">After Peak 01 Hour</h5>
                <p>{{$trackerMilage->after_peak_one_hour}}</p>
              </div>
            </div>

            <!-- After Peak 02 Hour -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">After Peak 02 Hour</h5>
                <p>{{$trackerMilage->after_peak_two_hour}}</p>
              </div>
            </div>       

            <!-- Difference -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0"> Difference</h5>
                <p>{{$trackerMilage->difference}}</p>
              </div>
            </div>

            <!-- Odo Meter -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Odo Meter</h5>
                <p>{{$trackerMilage->odo_meter}}</p>
              </div>
            </div>
          </div>

          <!-- Buttons -->
          <div class="row">                        
            <div class="col-md-12">
              <label for=""></label>
              <div class="text-right">
                <a href="#" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.trackerMileages.index') }}" class="btn btn-secondary">Back</a>
                <form action="{{ route('admin.trackerMileages.destroy', $trackerMilage->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Tracker Mileage?');">
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
