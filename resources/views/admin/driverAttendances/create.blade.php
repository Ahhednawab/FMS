@extends('layouts.admin')

@section('title', 'Add Driver Attendance')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Driver Attendance Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.driverAttendances.index') }}" class="btn btn-primary">
            <span>View Driver Attendance <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.driverAttendances.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
            <!-- Serial No -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Serial No</label>
                <input value="654412364" type="text" class="form-control" readonly>
              </div>
            </div>

            <!-- Name -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Name</label>
                <input type="date" class="form-control">
              </div>
            </div>

            <!-- Farther Name -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Farther Name</label>
                <input type="text" class="form-control">
              </div>
            </div>

            <!-- Shift Time -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Shift Time</label>
                <select class="custom-select">
                  <option value="1">Day</option>
                  <option value="2">Night</option>
                </select>
              </div>
            </div>            
          </div>

          <div class="row">
            <!-- Vehicle No -->
            <div class="col-md-3">
              <div class="form-group">
                <div class="form-group">
                  <label>Vehicle No</label>
                  <input type="text" class="form-control">
                </div>
              </div>
            </div>

            <!-- Remarks -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Remarks</label>
              <input type="text"  class="form-control">
              </div>
            </div>

            <!-- Date -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Date</label>
                <input type="date" class="form-control">
              </div>
            </div>

            <!-- Status -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Status</label>
                <select class="custom-select">
                  <option value="1">Present</option>
                  <option value="2">Absent</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">            
            <div class="col-md-12">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.driverAttendances.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>                    
        </form>
      </div>
    </div>
  </div>
@endsection
