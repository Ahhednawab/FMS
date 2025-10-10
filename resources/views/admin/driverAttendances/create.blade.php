@extends('layouts.admin')

@section('title', 'Add Driver Attendance')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Driver Attendance Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.driverAttendances.index') }}" class="btn btn-primary">
            <span>View Driver Attendance <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.driverAttendances.filter') }}" method="POST">
          @csrf
          <div class="row">
            <!-- Vehicle No -->
            <div class="col-md-3">
              <div class="form-group">
                <label class="form-label"><strong>Driver Status</strong></label>
                <select class="custom-select select2" name="driver_status_id" id="driver_status_id">
                  <option value="" {{ empty($selected_driver_status_id) ? 'selected' : '' }}>ALL</option>
                  @foreach($driver_status as $key => $value)
                    <option value="{{ $key }}" {{ (isset($selected_driver_status_id) && (string)$selected_driver_status_id === (string)$key) ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
              </div>
            </div>
            
            <div class="col-md-3 mt-4">
              <div class="form-group">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.driverAttendances.create') }}" class="btn btn-primary">Reset</a>
              </div>
            </div>
            
          </div>
        </form>        
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.driverAttendances.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          

          <div class="row">
            <!-- Date -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Date </strong>
                <input type="date" class="form-control" name="date" value="{{ old('date') }}" max="{{ date('Y-m-d') }}">
                @error('date')
                  <label class="text-danger">{{ $message }}</label>
                @enderror
              </div>
            </div>
          </div>
          
          @foreach($drivers as $i => $value)
            <div class="row">
              <input type="hidden" class="form-control" name="driver_id[]" value="{{ $value->id }}">

              <!-- Driver -->
              <div class="col-md-3">
                <div class="form-group">
                  <strong>Driver</strong>
                  <input type="text" class="form-control" name="full_name[]" value="{{ $value->full_name }}" readonly>
                </div>
              </div>

              

              <!-- Shift -->
              <div class="col-md-3">
                <div class="form-group">
                  <strong>Shift</strong>
                  <input type="text" class="form-control" name="shift[]" value="{{ $value->shiftTiming ? $value->shiftTiming->name . ' (' . \Carbon\Carbon::parse($value->shiftTiming->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($value->shiftTiming->end_time)->format('h:i A') . ')' : 'N/A' }}" readonly>
                </div>
              </div>

              <!-- Status -->
              <div class="col-md-2">
                <div class="form-group">
                  <strong>Status</strong>
                  <input type="text" class="form-control" name="driverStatus[]" value="{{ $value->driverStatus->name }}" readonly>
                </div>
              </div>

              <!-- Attendance -->
              <div class="col-md-2">
                <div class="form-group">
                  <strong>Attendance</strong>
                  <select class="custom-select" name="status[]">
                    <option value="">Select</option>
                    @foreach($driver_attendance_status as $statusKey => $statusLabel)
                      <option value="{{$statusKey}}" {{ old('status.'.$i) == (string)$statusKey ? 'selected' : '' }}>{{$statusLabel}}</option>
                    @endforeach
                  </select>
                  @error("status.$i")
                    <label class="text-danger">{{ $message }}</label>
                  @enderror
                  
                </div>
              </div>
            </div>
          @endforeach

          <div class="row">            
            <div class="col-md-12">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.driverAttendances.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>                    
        </form>
      </div>
    </div>
  </div>
@endsection
