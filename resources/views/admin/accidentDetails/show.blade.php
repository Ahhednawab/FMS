@extends('layouts.admin')

@section('title', 'Accident Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Accident Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.accidentDetails.index') }}" class="btn btn-primary"><span>View Accident Details <i class="icon-list ml-2"></i></span></a>
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
            <!-- Accident ID -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Accident ID </h5>
                <p>{{$accidentDetail->accident_id}}</p>
              </div>
            </div>

            <!-- Accident Type -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Accident Type</h5>
                <p>{{$accident_types[$accidentDetail->accident_type]}}</p>
              </div>
            </div>

            <!-- Location -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Location</h5>
                <p>{{$accidentDetail->location}}</p>
              </div>
            </div>

            <!-- Accident Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Accident Date</h5>
                <p>{{ \Carbon\Carbon::parse($accidentDetail->accident_date)->format('d-M-Y') }}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Accident Time -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Accident Time</h5>
                <p>{{ \Carbon\Carbon::parse($accidentDetail->accident_time)->format('h:i A') }}</p>
              </div>
            </div>

            <!-- Accident Description -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Accident Description</h5>
                <p>{{$accidentDetail->accident_description}}</p>
              </div>
            </div>

            <!-- Person Involved -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Person Involved</h5>
                <p>{{$users[$accidentDetail->person_involved]}}</p>
              </div>
            </div>

            <!-- Injury Type -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Injury Type</h5>
                <p>{{$injury_types[$accidentDetail->injury_type]}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Damage Type -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Damage Type</h5>
                <p>{{$damage_types[$accidentDetail->damage_type]}}</p>
              </div>
            </div>
          </div>

          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.accidentDetails.edit', $accidentDetail->id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.accidentDetails.index') }}" class="btn btn-secondary">Back</a>
                <form action="{{ route('admin.accidentDetails.destroy', $accidentDetail->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Accident Details?');">
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
