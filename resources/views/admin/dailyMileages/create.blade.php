@extends('layouts.admin')

@section('title', 'Add Daily Mileage')

@section('content')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Mileage Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.dailyMileages.index') }}" class="btn btn-primary">
            <span>View Daily Mileage <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.dailyMileages.store') }}" method="POST" enctype="multipart/form-data">
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

            <!-- Remarks -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Remarks</label>
                <input type="text" class="form-control" name="remarks" value="{{ old('remarks') }}">
                @if ($errors->has('remarks'))
                  <label class="text-danger">{{ $errors->first('remarks') }}</label>
                @endif
              </div>
            </div>            
          </div>

          <div class="row">
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

            <!-- Mileage -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Mileage</label>
                <select class="custom-select" name="mileage">
                  <option value="">--Select--</option>
                  @foreach($mileages as $key => $value)
                    <option value="{{ $key }}" {{ old('mileage') == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
                @if ($errors->has('mileage'))
                  <label class="text-danger">{{ $errors->first('mileage') }}</label>
                @endif
              </div>
            </div>

            <!-- Current Month Total KMs ({{$months[3]}}) -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Current Month Total KMs ({{$months[3]}})</label>
                <input type="number" min="0" step="1" class="form-control" name="last_third_month_km" value="{{ old('last_third_month_km') }}">
                @if ($errors->has('last_third_month_km'))
                  <label class="text-danger">{{ $errors->first('last_third_month_km') }}</label>
                @endif
              </div>
            </div>

            <!-- Current Month Total KMs ({{$months[2]}}) -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Current Month Total KMs ({{$months[2]}})</label>
                <input type="number" min="0" step="1" class="form-control" name="last_second_month_km" value="{{ old('last_second_month_km') }}">
                @if ($errors->has('last_second_month_km'))
                  <label class="text-danger">{{ $errors->first('last_second_month_km') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Current Month Total KMs ({{$months[1]}}) -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Current Month Total KMs ({{$months[1]}})</label>
                <input type="number" min="0" step="1" class="form-control" name="last_month_km" value="{{ old('last_month_km') }}">
                @if ($errors->has('last_month_km'))
                  <label class="text-danger">{{ $errors->first('last_month_km') }}</label>
                @endif               
              </div>
            </div>
            
            <!-- Current Month Total KMs (Particular Month) -->
            <div class="col-md-4">
              <div class="form-group">
                <label>Current Month Total KMs (Particular Month)</label>
                <input type="number" min="0" step="1" class="form-control" name="current_month_km" value="{{ old('current_month_km') }}">
                @if ($errors->has('current_month_km'))
                  <label class="text-danger">{{ $errors->first('current_month_km') }}</label>
                @endif
              </div>
            </div>
            
            <div class="col-md-5">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.dailyMileages.index') }}" class="btn btn-warning">Cancel</a>
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
