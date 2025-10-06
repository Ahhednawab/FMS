@extends('layouts.admin')

@section('title', 'Daily Fuel List')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Fuel List</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.dailyFuels.create') }}" class="btn btn-primary">
            <span>Add Daily Fuel <i class="icon-plus3 ml-2"></i></span>
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

    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.dailyFuels.index') }}" method="get">
        <div class="row">
          <!-- Vehicle No -->
          <div class="col-md-3">
            <div class="form-group">
              <label class="form-label"><strong>Vehicle No</strong></label>
              <select class="custom-select select2 vehicle" name="vehicle_id" id="vehicle_no">
                <option value="">--Select--</option>
                @foreach($vehicles as $value)
                  <option value="{{$value->id}}" {{ request('vehicle_id') == $value->id ? 'selected' : '' }}>{{$value->vehicle_no}}</option>
                @endforeach
              </select>
            </div>
          </div>

          @php
            use Carbon\Carbon;
            
            $defaultFromDate = request('from_date') ?? Carbon::now()->startOfMonth()->toDateString();
            $defaultToDate = request('to_date') ?? Carbon::now()->today()->toDateString();
          @endphp
          
          <!--From Date -->
          <div class="col-md-3">
            <div class="form-group">
              <label><strong>From</strong></label>
              <input type="date" class="form-control" name="from_date" value="{{ $defaultFromDate }}" max="{{ date('Y-m-d') }}">
            </div>
          </div>   
          <!--To Date -->
          <div class="col-md-3">
            <div class="form-group">
              <label><strong>To</strong></label>
              <input type="date" class="form-control" name="to_date" value="{{ $defaultToDate }}" max="{{ date('Y-m-d') }}">
            </div>
          </div> 
          <div class="col-md-3 mt-4">
            <div class="form-group">
              <button type="submit" class="btn btn-primary">Filter</button>
              <a href="{{ route('admin.dailyFuels.index') }}" class="btn btn-primary">Reset</a>
            </div>
          </div>
          
        </div>
        </form>
        
      </div>
    </div>


    <!-- Basic datatable -->
    <div class="card">
      <div class="card-body">
        <table class="table datatable-colvis-basic dataTable">
          <thead>
            <tr>
              <th>Vehicle</th>
              <th>Station</th>
              <th>Report Date</th>
              <th>Previous KM</th>
              <th>Current KM</th>
              <th>Mileage</th>
              <th>Fuel Taken</th>
              <th>Fuel Avg.</th>
              <th>AKPL</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dailyFuels as $key => $value)
              <tr>
                <td>{{$value->vehicle->vehicle_no}}</td>
                <td>{{$value->vehicle->station->area}}</td>
                <td>{{ \Carbon\Carbon::parse($value->report_date)->format('d-M-Y') }}</td>
                <td class="text-center">{{$value->previous_km}} KM</td>
                <td class="text-center">{{$value->current_km}} KM</td>
                <td class="text-center">{{$value->mileage}} KM</td>
                <td class="text-center">{{$value->fuel_taken}} Ltr</td>
                <td class="text-center">{{$value->fuel_average}} KM/Ltr</td>
                <td class="text-center">{{$value->vehicle->akpl}} KM/Ltr</td>
                <td class="text-center">
                  <div class="list-icons">
                    <div class="dropdown">
                      <a href="#" class="list-icons-item" data-toggle="dropdown">
                        <i class="icon-menu9"></i>
                      </a>
                      <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ route('admin.dailyFuels.show', $value->id) }}" class="dropdown-item">
                          <i class="icon-eye"></i> View Details
                        </a>
                        <a href="{{ route('admin.dailyFuels.edit', $value->id) }}" class="dropdown-item">
                          <i class="icon-pencil7"></i> Edit
                        </a>
                        <form action="{{ route('admin.dailyFuels.destroy', $value->id) }}" method="POST">
                          @csrf
                          @method('DELETE')
                          <button class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete?')">
                            <i class="icon-trash"></i> Delete
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach     
          </tbody>
        </table>
      </div>
    </div>
    <!-- /basic datatable -->
  </div>
  <!-- /content area -->

  <!-- Select2 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  
  <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>
  <script src="{{ asset('assets/js/demo_pages/datatables_extension_colvis.js') }}"></script>

  <script>
    $(document).ready(function () {
      $('.datatable-colvis-basic').DataTable();

      $('.vehicle').select2({
        placeholder: "--Select--",
        allowClear: true,
        theme: 'bootstrap4'
      });
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
