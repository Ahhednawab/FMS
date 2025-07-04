@extends('layouts.admin')

@section('title', 'Edit Daily Fuel Mileage')

@section('content')

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Daily Fuel Mileage</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
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
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <form method="POST" action="{{ route('admin.dailyFuelMileages.update', $dailyFuelMileage->id) }}">
              @csrf
              @method('PUT')

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Serial NO</label>
                    <input value="{{$dailyFuelMileage->serial_no}}" name="serial_no" type="text" class="form-control" readonly>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Vehicle No</label>
                    <input type="text" class="form-control" name="vehicle_no" value="{{ old('vehicle_no', $dailyFuelMileage->vehicle_no ?? '') }}">
                    @if ($errors->has('vehicle_no'))
                      <label class="text-danger">{{ $errors->first('vehicle_no') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Destination</label>
                    <select class="custom-select" name="destination">
                      <option value="">--Select--</option>
                      @foreach($destinations as $key => $value)
                        <option value="{{ $key }}" {{ old('destination', $dailyFuelMileage->destination ?? '') == $key ? 'selected' : '' }}>
                          {{ $value }}
                        </option>
                      @endforeach
                    </select>
                    @if ($errors->has('destination'))
                      <label class="text-danger">{{ $errors->first('destination') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control" name="date" value="{{ old('date', $dailyFuelMileage->date ?? '') }}">
                    @if ($errors->has('date'))
                      <label class="text-danger">{{ $errors->first('date') }}</label>
                    @endif
                  </div>
                </div>            
              </div>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Current Reading</label>
                    <input type="number" min="0" step="1" class="form-control" name="current_reading" value="{{ old('current_reading', $dailyFuelMileage->current_reading ?? '') }}">
                    @if ($errors->has('current_reading'))
                      <label class="text-danger">{{ $errors->first('current_reading') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Previous Reading</label>
                    <input type="number" min="0" step="1" class="form-control" name="previous_reading" value="{{ old('previous_reading', $dailyFuelMileage->previous_reading ?? '') }}">
                    @if ($errors->has('previous_reading'))
                      <label class="text-danger">{{ $errors->first('previous_reading') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Difference</label>
                    <input type="number" min="0" step="1" class="form-control" name="difference" value="{{ old('difference', $dailyFuelMileage->difference ?? '') }}">
                    @if ($errors->has('difference'))
                      <label class="text-danger">{{ $errors->first('difference') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Fuel Taken</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="fuel_taken" value="{{ old('fuel_taken', $dailyFuelMileage->fuel_taken ?? '') }}">
                    @if ($errors->has('fuel_taken'))
                      <label class="text-danger">{{ $errors->first('fuel_taken') }}</label>
                    @endif
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Consumption</label>
                    <input type="number" min="0" step="1" class="form-control" name="consumption" value="{{ old('consumption', $dailyFuelMileage->consumption ?? '') }}">
                    @if ($errors->has('consumption'))
                      <label class="text-danger">{{ $errors->first('consumption') }}</label>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Fuel Station</label>
                    <select class="custom-select" name="fuel_station">
                      <option value="">--Select--</option>
                      @foreach($fuelStations as $key => $value)
                        <option value="{{ $key }}" {{ old('fuel_station', $dailyFuelMileage->fuel_station ?? '') == $key ? 'selected' : '' }}>
                          {{ $value }}
                        </option>
                      @endforeach
                    </select>
                    @if ($errors->has('fuel_station'))
                      <label class="text-danger">{{ $errors->first('fuel_station') }}</label>
                    @endif
                  </div>
                </div>
                
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Driver Name</label>
                    <input type="text" class="form-control" name="driver_name" value="{{ old('driver_name', $dailyFuelMileage->driver_name ?? '') }}">
                    @if ($errors->has('driver_name'))
                      <label class="text-danger">{{ $errors->first('driver_name') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Location</label>
                    <input type="text" class="form-control" name="location" value="{{ old('location', $dailyFuelMileage->location ?? '') }}">
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
    </div>
  </div>
@endsection
