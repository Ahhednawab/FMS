@extends('layouts.admin')

@section('title', 'Add Daily Fuel Mileage')

@section('content')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Fuel Mileage Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.dailyFuelMileages.index') }}" class="btn btn-primary">
            <span>View Daily Fuel Mileage <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.dailyFuelMileages.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
            <!-- Serial No -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Serial No</label>
                <input value="{{$serial_no}}" name="serial_no" type="text" class="form-control" readonly>
              </div>
            </div>

            <!-- Vehicle No -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Vehicle No</label>
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

            <!-- Destination -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Destination</label>
                <select class="custom-select" name="destination">
                  <option value="">--Select--</option>
                  @foreach($destinations as $key => $value)
                    <option value="{{ $key }}" {{ old('destination') == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
                @if ($errors->has('destination'))
                  <label class="text-danger">{{ $errors->first('destination') }}</label>
                @endif
              </div>
            </div>

            <!-- Date -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Date</label>
                <input type="date" class="form-control" name="date" value="{{ old('date') }}">
                @if ($errors->has('date'))
                  <label class="text-danger">{{ $errors->first('date') }}</label>
                @endif
              </div>
            </div>            
          </div>

          <div class="row">
            <!-- Current Reading -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Current Reading</label>
                <input type="number" min="0" step="1" class="form-control" name="current_reading" value="{{ old('current_reading') }}">
                @if ($errors->has('current_reading'))
                  <label class="text-danger">{{ $errors->first('current_reading') }}</label>
                @endif
              </div>
            </div>

            <!-- Previous Reading -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Previous Reading</label>
                <input type="number" min="0" step="1" class="form-control" name="previous_reading" value="{{ old('previous_reading') }}">
                @if ($errors->has('previous_reading'))
                  <label class="text-danger">{{ $errors->first('previous_reading') }}</label>
                @endif
              </div>
            </div>

            <!-- Difference -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Difference</label>
                <input type="number" min="0" step="1" class="form-control" name="difference" value="{{ old('difference') }}">
                @if ($errors->has('difference'))
                  <label class="text-danger">{{ $errors->first('difference') }}</label>
                @endif
              </div>
            </div>

            <!-- Fuel Taken -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Fuel Taken</label>
                <input type="number" min="0" step="1" class="form-control" name="fuel_taken" value="{{ old('fuel_taken') }}">
                @if ($errors->has('fuel_taken'))
                  <label class="text-danger">{{ $errors->first('fuel_taken') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Consumption -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Consumption</label>
                <input type="number" min="0" step="1" class="form-control" name="consumption" value="{{ old('consumption') }}">
                @if ($errors->has('consumption'))
                  <label class="text-danger">{{ $errors->first('consumption') }}</label>
                @endif
              </div>
            </div>

            <!-- Fuel Station -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Fuel Station</label>
                <select class="custom-select" name="fuel_station">
                  <option value="">--Select--</option>
                  @foreach($fuelStations as $key => $value)
                    <option value="{{ $key }}" {{ old('fuel_station') == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
                @if ($errors->has('fuel_station'))
                  <label class="text-danger">{{ $errors->first('fuel_station') }}</label>
                @endif
              </div>
            </div>
            
            <!-- Driver Name -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Driver Name</label>
                <input type="text" class="form-control" name="driver_name" value="{{ old('driver_name') }}">
                @if ($errors->has('driver_name'))
                  <label class="text-danger">{{ $errors->first('driver_name') }}</label>
                @endif
              </div>
            </div>

            <!-- Location -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Location</label>
                <input type="text" class="form-control" name="location" value="{{ old('location') }}">
                @if ($errors->has('location'))
                  <label class="text-danger">{{ $errors->first('location') }}</label>
                @endif
              </div>
            </div>
          </div>  
          
          <div class="row">
            <div class="col-md-12">
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.dailyFuelMileages.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>          
        </form>
      </div>
    </div>
  </div>

  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function () {
        $('#vehicle_id').select2({
            placeholder: "--Select--",
            allowClear: true,
            theme: 'bootstrap4'
        });
    });
  </script>
@endsection
