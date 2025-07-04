@extends('layouts.admin')

@section('title', 'Add Tracker Mileage')

@section('content')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Tracker Mileage Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.trackerMileages.index') }}" class="btn btn-primary">
            <span>View Tracker Mileage <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.trackerMileages.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>Serial No</label>
                <input value="{{$serial_no}}" name="serial_no" type="text" class="form-control" readonly>
              </div>
            </div>

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

            <div class="col-md-3">
              <div class="form-group">
                <label>Day</label>
                <select class="custom-select" name="day">
                  <option value="">Select Day</option>
                  @foreach($days as $key => $value)
                    <option value="{{ $key }}" {{ old('day') == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
                @if ($errors->has('day'))
                  <label class="text-danger">{{ $errors->first('day') }}</label>
                @endif
              </div>
            </div>

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
            <div class="col-md-3">
              <div class="form-group">
                <label>AKPL</label>
                <input type="text" class="form-control" name="akpl" value="{{ old('akpl') }}">
                @if ($errors->has('akpl'))
                  <label class="text-danger">{{ $errors->first('akpl') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>IBC/Center</label>
                <select class="custom-select" name="ibc_center">
                  <option value="">Select</option>
                  @foreach($ibc_center as $key => $value)
                    <option value="{{ $key }}" {{ old('ibc_center') == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
                @if ($errors->has('ibc_center'))
                  <label class="text-danger">{{ $errors->first('ibc_center') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Before Peak 01 Hour</label>
                <input type="number" min="0" step="1" class="form-control" name="before_peak_one_hour" value="{{ old('before_peak_one_hour') }}">
                @if ($errors->has('before_peak_one_hour'))
                  <label class="text-danger">{{ $errors->first('before_peak_one_hour') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Before Peak 02 Hour</label>
                <input type="number" min="0" step="1" class="form-control" name="before_peak_two_hour" value="{{ old('before_peak_two_hour') }}">
                @if ($errors->has('before_peak_two_hour'))
                  <label class="text-danger">{{ $errors->first('before_peak_two_hour') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>KMS Driven Peak</label>
                <input type="number" min="0" step="1" class="form-control" name="kms_driven_peak" value="{{ old('kms_driven_peak') }}">
                @if ($errors->has('kms_driven_peak'))
                  <label class="text-danger">{{ $errors->first('kms_driven_peak') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>KMS Driven Off Peak</label>
                <input type="number" min="0" step="1" class="form-control" name="kms_driven_off_peak" value="{{ old('kms_driven_off_peak') }}">
                @if ($errors->has('kms_driven_off_peak'))
                  <label class="text-danger">{{ $errors->first('kms_driven_off_peak') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Total KMs In a day</label>
                <input type="number" min="0" step="1" class="form-control" name="total_kms_in_a_day" value="{{ old('total_kms_in_a_day') }}">
                @if ($errors->has('total_kms_in_a_day'))
                  <label class="text-danger">{{ $errors->first('total_kms_in_a_day') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>After Peak 01 Hour</label>
                <input type="number" min="0" step="1" class="form-control" name="after_peak_one_hour" value="{{ old('after_peak_one_hour') }}">
                @if ($errors->has('after_peak_one_hour'))
                  <label class="text-danger">{{ $errors->first('after_peak_one_hour') }}</label>
                @endif
              </div>
            </div>          
          </div>

          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>After Peak 02 Hour</label>
                <input type="number" min="0" step="1" class="form-control" name="after_peak_two_hour" value="{{ old('after_peak_two_hour') }}">
                @if ($errors->has('after_peak_two_hour'))
                  <label class="text-danger">{{ $errors->first('after_peak_two_hour') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label> Difference</label>
                <input type="number" min="0" step="1" class="form-control" name="difference" value="{{ old('difference') }}">
                @if ($errors->has('difference'))
                  <label class="text-danger">{{ $errors->first('difference') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Odo Meter</label>
                <input type="number" min="0" step="1" class="form-control" name="odo_meter" value="{{ old('odo_meter') }}">
                @if ($errors->has('odo_meter'))
                  <label class="text-danger">{{ $errors->first('odo_meter') }}</label>
                @endif
              </div>
            </div>

            <div class="col-md-3">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.trackerMileages.index') }}" class="btn btn-warning">Cancel</a>
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
