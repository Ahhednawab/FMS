@extends('layouts.admin')

@section('title', 'Edit Vehicle Attendance')

@section('content')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Vehicle Attendance</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
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
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <form method="POST" action="{{ route('admin.vehicleAttendances.update', $vehicleAttendance->id) }}">
              @csrf
              @method('PUT')

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Station</strong>
                    <input type="text" class="form-control" name="station" value="{{ $vehicleAttendance->vehicle->station->area }}" readonly>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                    <strong>Vehicle No</strong>
                    <input type="text" class="form-control" name="vehicle_no" value="{{ $vehicleAttendance->vehicle->vehicle_no }}" readonly>
                  </div>
                </div>

                

                <div class="col-md-2">
                  <div class="form-group">
                    <strong>Shift</strong>
                    <input type="text" class="form-control" name="shiftHours" value="{{ $vehicleAttendance->vehicle->shiftHours->name }}" readonly>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                    <strong>Date</strong>
                    <input type="date" class="form-control" name="date" value="{{ old('date', $vehicleAttendance->date ?? '') }}" max="{{ date('Y-m-d') }}">
                    @if ($errors->has('date'))
                      <label class="text-danger">{{ $errors->first('date') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Attendance -->
                <div class="col-md-2">
                  <div class="form-group">
                    <strong>Attendance</strong>
                    <select class="custom-select" name="status">
                      <option value="">Select</option>
                      @foreach($attendanceStatus as $key => $value)
                        <option value="{{$key}}" {{ (string)old('status', $vehicleAttendance->status) === (string)$key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @error("status")
                      <label class="text-danger">{{ $message }}</label>
                    @enderror
                    
                  </div>
                </div>

              </div>

              <div class="row">
                <div class="col-md-12">
                  <label for=""></label>
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.vehicleAttendances.index') }}" class="btn btn-warning">Cancel</a>
                  </div>
                </div>
              </div>  
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
