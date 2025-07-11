@extends('layouts.admin')

@section('title', 'Driver Attendance Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Driver Attendance Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.driverAttendances.index') }}" class="btn btn-primary"><span>View Driver Attendance <i class="icon-list ml-2"></i></span></a>
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
                                    <h5 class="m-0">Serial No</h5>
                                    <p>13364</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Farther Name</h5>
                                    <p>Name</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Shift Time</h5>
                                    <p>21/12/2024</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Vehicle No</h5>
                                    <p>Driver</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                               
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Remarks</h5>
                                    <p>21/12/2024</p>
                                    </div>
                                </div>

                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Date</h5>
                                    <p>21/12/2024</p>
                                    </div>
                                </div>


                              
                                <div class="col-md-3 text-center">
                            

                            </div>
          <!-- /basic datatable -->
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="#" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.driverAttendances.index') }}" class="btn btn-secondary">Back</a>
                <form action="{{ route('admin.driverAttendances.destroy', 1) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Driver Attendance?');">
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
