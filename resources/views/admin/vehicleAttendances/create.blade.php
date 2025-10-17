@extends('layouts.admin')

@section('title', 'Add Vehicle Attendance')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Attendance Management</span></h4>
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
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.vehicleAttendances.filter') }}" method="POST">
          @csrf
          <div class="row">
            <!-- Vehicle No -->
            <div class="col-md-3">
              <div class="form-group">
                <label class="form-label"><strong>Station</strong></label>
                <select class="custom-select select2" name="station_id" id="station_id">
                  <option value="">ALL</option>
                  @foreach($stations as $key => $value)
                    <option value="{{ $key }}" {{ (isset($selectedStation) && (string)$selectedStation === (string)$key) ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
              </div>
            </div>
            
            <div class="col-md-3 mt-4">
              <div class="form-group">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.vehicleAttendances.create') }}" class="btn btn-primary">Reset</a>
              </div>
            </div>
            
          </div>
        </form>        
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.vehicleAttendances.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
            <!-- Date -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Date </strong>
                <input type="date" class="form-control @error('date') is-invalid @enderror" name="date" value="{{ old('date') }}" max="{{ date('Y-m-d') }}">
                @error('date')
                  <label class="text-danger">{{ $message }}</label>
                @enderror
              </div>
            </div>
          </div>

          @php
            $groupedByStation = collect($vehicleData)->groupBy('station');
            $globalIndex = 0;
          @endphp

          @foreach($groupedByStation as $station => $vehicles)
            <div class="row">
              <!-- Station -->
              <div class="col-md-12">
                <h5 class="mt-3 mb-2">{{ $station }}</h5>
                <hr>
              </div>
            </div>

            @foreach($vehicles as $i => $value)
              <div class="row">
                <input type="hidden" class="form-control" name="vehicle_id[]" value="{{ $value['vehicle_id'] }}">
                
                <!-- Vehicle No -->
                <div class="col-md-2">
                  <div class="form-group">
                    <strong>Vehicle No</strong>
                    <input type="text" class="form-control" name="vehicle_no" value="{{ $value['vehicle_no'] }}" readonly>
                  </div>
                </div>

                

                <!-- Make (Manufacturer) -->
                <div class="col-md-2">
                  <div class="form-group">
                    <strong>Make (Manufacturer)</strong>
                    <input type="text" class="form-control" name="make" value="{{ $value['make'] }}" readonly>
                  </div>
                </div>

                <!-- Shift -->
                <div class="col-md-2">
                  <div class="form-group">
                    <strong>Shift</strong>
                    <input type="text" class="form-control" name="shift" value="{{ $value['shift'] }}" readonly>
                  </div>
                </div>

                

                <!-- IBC Center -->
                <div class="col-md-2">
                  <div class="form-group">
                    <strong>IBC Center</strong>
                    <input type="text" class="form-control" name="ibcCenter" value="{{ $value['ibcCenter'] }}" readonly>
                  </div>
                </div>

                <!-- Attendance -->
                <div class="col-md-2">
                  <div class="form-group">
                    <strong>Attendance</strong>
                    <select class="custom-select @error('status.' . $value['vehicle_id']) is-invalid @enderror" name="status[{{ $value['vehicle_id'] }}]">
                      <option value="">Select</option>
                      @foreach($attendanceStatus as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" 
                          {{ old('status.' . $value['vehicle_id']) == (string) $statusKey ? 'selected' : '' }}>
                          {{ $statusLabel }}
                        </option>
                      @endforeach
                    </select>
                    @error('status.' . $value['vehicle_id'])
                      <label class="text-danger">{{ $message }}</label>
                    @enderror
                    
                  </div>
                </div>
              </div>
              @php $globalIndex++; @endphp
            @endforeach
          @endforeach

          <div class="row">            
            <div class="col-md-12">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.vehicleAttendances.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>
                    
        </form>
      </div>
    </div>
  </div>
@endsection
