@extends('layouts.admin')

@section('title', 'Edit Vehicle Maintenance Report')

@section('content')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Vehicle Maintenance Report</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('vehicleMaintenanceReports.index') }}" class="btn btn-primary">
            <span>View Vehicle Maintenance Report <i class="icon-list ml-2"></i></span>
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
            <form method="POST" action="{{ route('vehicleMaintenanceReports.update', $vehicleMaintenanceReport->id) }}" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              
              <div class="row">
                <!-- Maintenance Report ID -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Maintenance Report ID</label>
                    <input value="{{$vehicleMaintenanceReport->maintenance_report_id}}" name="maintenance_report_id" type="text" class="form-control" readonly>
                  </div>
                </div>

                <!-- Vehicle No -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Vehicle No</label>
                    <select class="custom-select select2" id="vehicle_id" name="vehicle_id">
                      <option value="">--Select--</option>
                      @foreach($vehicles as $key => $value)
                        <option value="{{$key}}" {{ old('vehicle_id', $vehicleMaintenanceReport->vehicle_id ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('vehicle_id'))
                      <label class="text-danger">{{ $errors->first('vehicle_id') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Model -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Model</label>
                    <input type="text" class="form-control" name="model" value="{{ old('model', $vehicleMaintenanceReport->model ?? '') }}">
                    @if ($errors->has('model'))
                      <label class="text-danger">{{ $errors->first('model') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Odometer Reading -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Odometer Reading</label>
                    <input type="number" min="1" step="1" class="form-control" name="odo_meter_reading" value="{{ old('odo_meter_reading', $vehicleMaintenanceReport->odo_meter_reading ?? '') }}">
                    @if ($errors->has('odo_meter_reading'))
                      <label class="text-danger">{{ $errors->first('odo_meter_reading') }}</label>
                    @endif
                  </div>
                </div>            
              </div>

              <div class="row">
                <!-- Fuel Type -->
                <div class="col-md-3">              
                  <div class="form-group">
                    <label>Fuel Type</label>
                    <select class="custom-select" name="fuel_type">
                      <option value="">--Select--</option>
                      @foreach($fuel_types as $key => $value)
                        <option value="{{$key}}" {{ old('fuel_type', $vehicleMaintenanceReport->fuel_type ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('fuel_type'))
                      <label class="text-danger">{{ $errors->first('fuel_type') }}</label>
                    @endif
                  </div>              
                </div>

                <!-- Maintenance Category -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Maintenance Category</label>
                    <select class="custom-select" name="category">
                      <option value="">--Select--</option>
                      @foreach($category as $key => $value)
                        <option value="{{$key}}" {{ old('category', $vehicleMaintenanceReport->category ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('category'))
                      <label class="text-danger">{{ $errors->first('category') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Service Date -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Service Date</label>
                    <input type="date" class="form-control" name="service_date" value="{{ old('service_date', $vehicleMaintenanceReport->service_date ?? '') }}">
                    @if ($errors->has('service_date'))
                      <label class="text-danger">{{ $errors->first('service_date') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Service Provider -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Service Provider</label>
                    <select class="custom-select" name="service_provider">
                      <option value="">--Select--</option>
                      @foreach($service_provider as $key => $value)
                        <option value="{{$key}}" {{ old('service_provider', $vehicleMaintenanceReport->service_provider ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('service_provider'))
                      <label class="text-danger">{{ $errors->first('service_provider') }}</label>
                    @endif
                  </div>
                </div>
              </div>

              <div class="row">
                <!-- Parts Replaced -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Parts Replaced</label>
                    <select class="custom-select" name="parts_replaced">
                        <option value="">--Select--</option>
                        @foreach($parts as $key => $value)
                          <option value="{{$key}}" {{ old('parts_replaced', $vehicleMaintenanceReport->parts_replaced ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('parts_replaced'))
                        <label class="text-danger">{{ $errors->first('parts_replaced') }}</label>
                      @endif
                  </div>
                </div>
                
                <!-- Cost of Service -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Cost of Service</label>
                    <input type="number" min="1" step="1" class="form-control" name="service_cost" value="{{ old('service_cost', $vehicleMaintenanceReport->service_cost ?? '') }}">
                    @if ($errors->has('service_cost'))
                      <label class="text-danger">{{ $errors->first('service_cost') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Service Description -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Service Description</label>
                    <textarea class="form-control" name="service_description">{{ old('service_description', $vehicleMaintenanceReport->service_description ?? '') }}"</textarea>
                    @if ($errors->has('service_description'))
                      <label class="text-danger">{{ $errors->first('service_description') }}</label>
                    @endif
                  </div>
                </div> 

                <!-- Tyre Condition -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Tyre Condition</label>
                    <select class="custom-select" name="tyre_condition">
                        <option value="">--Select--</option>
                        @foreach($tyre_conditions as $key => $value)
                          <option value="{{$key}}" {{ old('tyre_condition', $vehicleMaintenanceReport->tyre_condition ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('tyre_condition'))
                        <label class="text-danger">{{ $errors->first('tyre_condition') }}</label>
                      @endif
                  </div>
                </div>
              </div>

              <div class="row">
                <!-- Brake Condition -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Brake Condition</label>
                    <select class="custom-select" name="brake_condition">
                        <option value="">--Select--</option>
                        @foreach($brake_conditions as $key => $value)
                          <option value="{{$key}}" {{ old('brake_condition', $vehicleMaintenanceReport->brake_condition ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('brake_condition'))
                        <label class="text-danger">{{ $errors->first('brake_condition') }}</label>
                      @endif
                  </div>
                </div>

                <!-- Engine Condition -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Engine Condition</label>
                    <select class="custom-select" name="engine_condition">
                      <option value="">--Select--</option>
                      @foreach($engine_conditions as $key => $value)
                        <option value="{{$key}}" {{ old('engine_condition', $vehicleMaintenanceReport->engine_condition ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('engine_condition'))
                      <label class="text-danger">{{ $errors->first('engine_condition') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Battery Condition -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Battery Condition</label>
                    <select class="custom-select" name="battery_condition">
                      <option value="">--Select--</option>
                      @foreach($battery_conditions as $key => $value)
                        <option value="{{$key}}" {{ old('battery_condition', $vehicleMaintenanceReport->battery_condition ?? '') == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('battery_condition'))
                      <label class="text-danger">{{ $errors->first('battery_condition') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Next Service Due -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Next Service Due</label>
                    <input type="date" class="form-control" name="next_service_date" value="{{ old('next_service_date', $vehicleMaintenanceReport->next_service_date ?? '') }}">
                    @if ($errors->has('next_service_date'))
                      <label class="text-danger">{{ $errors->first('next_service_date') }}</label>
                    @endif
                  </div>
                </div>
              </div>

              <div class="row">
                <!-- Buttons -->
                <div class="col-md-12">
                  <label for=""></label>
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('vehicleMaintenanceReports.index') }}" class="btn btn-warning">Cancel</a>
                  </div>
                </div>
              </div>  
            </form>
          </div>
        </div>
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