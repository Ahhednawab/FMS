@extends('layouts.admin')

@section('title', 'Driver Attendance List')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Driver Attendance List</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.driverAttendances.create') }}" class="btn btn-primary">
            <span>Add Driver Attendance <i class="icon-plus3 ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- /page header -->

  <!-- Content area -->
  <div class="content">
    @if ($message = Session::get('success'))
      <div id="alert-message" class="alert alert-success alert-dismissible alert-dismissible-2" role="alert">
        {{ $message }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path class="heroicon-ui" d="M16.24 14.83a1 1 0 0 1-1.41 1.41L12 13.41l-2.83 2.83a1 1 0 0 1-1.41-1.41L10.59 12 7.76 9.17a1 1 0 0 1 1.41-1.41L12 10.59l2.83-2.83a1 1 0 0 1 1.41 1.41L13.41 12l2.83 2.83z"></path>
          </svg>
        </button>
      </div>
    @elseif ($message = Session::get('delete_msg'))
      <div id="alert-message" class="alert alert-danger alert-dismissible alert-dismissible-2" role="alert">
        {{ $message }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path class="heroicon-ui" d="M16.24 14.83a1 1 0 0 1-1.41 1.41L12 13.41l-2.83 2.83a1 1 0 0 1-1.41-1.41L10.59 12 7.76 9.17a1 1 0 0 1 1.41-1.41L12 10.59l2.83-2.83a1 1 0 0 1 1.41 1.41L13.41 12l2.83 2.83z"></path>
          </svg>
        </button>
      </div>
    @endif


    <!-- Basic datatable -->
    <div class="card">
      <div class="card-body">
        <table class="table datatable-colvis-basic dataTable">
          <thead>
            <tr>
              <th>Serial No </th>
              <th>Farther Name</th>
              <th>Shift Time</th>
              <th>Vehicle No</th>
              <th>Remarks</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>2365</td>
              <td>None</td>
              <td>Pakistan</td>
              <td>Suspended</td>
              <td>Karachi</td>   
              <td class="text-center">
                <div class="list-icons">
                  <div class="dropdown">
                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                      <i class="icon-menu9"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right">
                      <a href="{{ route('admin.driverAttendances.show', 1) }}" class="dropdown-item"><i class="icon-file-pdf"></i> View Details</a>
                      
                    </div>
                  </div>
                </div>
              </td> 
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- /basic datatable -->
  </div>
  <!-- /content area -->
  
  <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>
  <script src="{{ asset('assets/js/demo_pages/datatables_extension_colvis.js') }}"></script>

  <script>
    $(document).ready(function () {
      $('.datatable-colvis-basic').DataTable();
    });

    setTimeout(function () {
      let alertBox = document.getElementById('alert-message');
      if (alertBox) {
        alertBox.style.transition = 'opacity 0.5s ease';
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.remove(), 500);
      }
    }, 3000);
  </script>
@endsection
