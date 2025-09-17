@extends('layouts.admin')

@section('title', 'Add Daily Fuel')

@section('content')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
  <style>
    .select2-search--dropdown::after {
      content: '' !important;
      display: none !important;
      background: none !important;
    }
  </style>

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Fuel Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.dailyFuels.index') }}" class="btn btn-primary">
            <span>View Daily Fuel <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.dailyFuels.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row">
            <!-- Serial No -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Serial No</strong>
                <input value="{{$serial_no}}" name="serial_no" type="text" class="form-control" readonly>
              </div>
            </div>

            <!-- Vehicle No -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Vehicle No</strong>
                <select class="custom-select select2" id="vehicle_id" name="vehicle_id">
                  <option value="">--Select--</option>
                  @foreach($vehicles as $key => $value)
                  <option value="{{$key}}" {{ old('vehicle_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('vehicle_id'))
                <label class="text-danger">{{ $errors->first('vehicle_id') }}</label>
                @endif
              </div>
            </div>

            <!-- Date -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Date</strong>
                <input type="date" class="form-control" name="date" value="{{ old('date') }}">
                @if ($errors->has('date'))
                <label class="text-danger">{{ $errors->first('date') }}</label>
                @endif
              </div>
            </div>

            <!-- Current Km -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Current Km</strong>
                <input type="number" min="0" step="1" class="form-control" name="current_km" value="{{ old('current_km') }}">
                @if ($errors->has('current_km'))
                <label class="text-danger">{{ $errors->first('current_km') }}</label>
                @endif
              </div>
            </div>

            <!-- Fuel Taken -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Fuel Taken</strong>
                <input type="number" min="0" step="1" class="form-control" name="fuel_taken" value="{{ old('fuel_taken') }}">
                @if ($errors->has('fuel_taken'))
                <label class="text-danger">{{ $errors->first('fuel_taken') }}</label>
                @endif
              </div>
            </div>

            <div class="col-md-2 mt-3">
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.dailyFuels.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Select2 JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#vehicle_id').select2({
        placeholder: "--Select--",
        allowClear: true,
        theme: 'bootstrap4'
      });
    });
  </script>
@endsection