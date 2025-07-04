@extends('layouts.admin')

@section('title', 'Add Vehicle Attendance')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Attendance Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.vehicleAttendances.index') }}" class="btn btn-primary">
            <span>View Vehicle Attendance <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.vehicleAttendances.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Serial No</label>
                          <input value="654412364" type="text" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Vehicle No</label>
                          <input type="date" class="form-control">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Date</label>
                          <input type="date" class="form-control">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <label for=""></label>
                        <div class="text-right">
                          <button type="submit" class="btn btn-primary">Save</button>
                          <a href="{{ route('admin.vehicleAttendances.index') }}" class="btn btn-warning">Cancel</a>
                        </div>
                      </div>
                      
                    </div>
                    
        </form>
      </div>
    </div>
  </div>
@endsection
