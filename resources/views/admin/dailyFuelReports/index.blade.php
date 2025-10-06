@extends('layouts.admin')

@section('title', 'Daily Fuel Report List')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Fuel Report List</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
    </div>
  </div>
  <!-- /page header -->

  <!-- Content area -->
  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.dailyFuelReports.index') }}" method="get">
        <div class="row">
          <!-- Vehicle No -->
          <div class="col-md-3">
            <div class="form-group">
                <label class="form-label"><strong>Vehicle No</strong></label>
                <select class="custom-select select2 vehicle" name="vehicle_id" id="vehicle_no">
                <option value="">--Select--</option>
                @foreach($vehicles as $value)
                    <option value="{{$value->vehicle_no}}" {{ request('vehicle_id') == $value->vehicle_no ? 'selected' : '' }}>{{$value->vehicle_no}}</option>
                @endforeach
                </select>
            </div>
          </div>
          
          <!--From Date -->
          <div class="col-md-3">
            <div class="form-group">
              <label><strong>From</strong></label>
              <input type="date" class="form-control" name="from_date" value="{{ request('from_date')}}" max="{{ date('Y-m-d') }}">
            </div>
          </div>   
          <!--To Date -->
          <div class="col-md-3">
            <div class="form-group">
              <label><strong>To</strong></label>
              <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}" max="{{ date('Y-m-d') }}">
            </div>
          </div> 
          <div class="col-md-3 mt-4">
            <div class="form-group">
              <button type="submit" class="btn btn-primary">Filter</button>
              <a href="{{ route('admin.dailyFuelReports.index') }}" class="btn btn-primary">Reset</a>
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
              <th>From</th>
              <th>To</th>
              <th>Start KM</th>
              <th>End KM</th>
              <th>Mileage</th>
              <th>Fuel Taken</th>
              <th>Fuel Avg</th>
              <th>AKPL</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dailyFuelReports as $key => $value)
              <tr>
                <td>{{$value->vehicle_no}}</td>
                <td>{{$value->station}}</td>
                <td>{{\Carbon\Carbon::parse($value->start_date)->format('d-M-Y')}}</td>
                <td>{{\Carbon\Carbon::parse($value->end_date)->format('d-M-Y')}}</td>
                <td>{{$value->start_km}} KM</td>
                <td>{{$value->end_km}} KM</td>
                <td>{{$value->mileage}} KM</td>
                <td>{{$value->fuel_taken}} Ltr</td>
                <td>{{round(($value->mileage / $value->fuel_taken),2)}} KM/Ltr</td>
                <td>{{$value->akpl}} KM/Ltr</td>
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
