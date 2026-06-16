@extends('layouts.admin')

@section('title', 'Edit Vehicle')

@section('content')
    @php
        $dateValue = function ($value) {
            return $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '';
        };
    @endphp

    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Vehicle</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-primary">
                        <span>View Vehicles <i class="icon-list ml-2"></i></span>
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
                        <form method="POST" action="{{ route('vehicles.update', $vehicle->id) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Serial NO -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Serial No</strong>
                                        <input value="{{ $vehicle->serial_no }}" name="serial_no" type="text"
                                            class="form-control" readonly>
                                    </div>
                                </div>

                                <!-- Vehicle No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Vehicle No</strong>
                                        <input type="text" class="form-control" name="vehicle_no"
                                            value="{{ old('vehicle_no', $vehicle->vehicle_no ?? '') }}">
                                        @if ($errors->has('vehicle_no'))
                                            <label class="text-danger">{{ $errors->first('vehicle_no') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Make (Manufacturer) -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Make (Manufacturer)</strong>
                                        <input type="text" class="form-control" name="make"
                                            value="{{ old('make', $vehicle->make ?? '') }}">
                                        @if ($errors->has('make'))
                                            <label class="text-danger">{{ $errors->first('make') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Model (Year) -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Model (Year)</strong>
                                        <select class="custom-select" name="model">
                                            <option value="">-- Select Model Year --</option>
                                            @for ($year = date('Y'); $year >= 1980; $year--)
                                                <option value="{{ $year }}"
                                                    {{ old('model', $vehicle->model ?? '') == $year ? 'selected' : '' }}>
                                                    {{ $year }}</option>
                                            @endfor
                                        </select>
                                        @if ($errors->has('model'))
                                            <label class="text-danger">{{ $errors->first('model') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Chasis No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Chasis No</strong>
                                        <input type="text" class="form-control" name="chasis_no"
                                            value="{{ old('chasis_no', $vehicle->chasis_no ?? '') }}">
                                        @if ($errors->has('chasis_no'))
                                            <label class="text-danger">{{ $errors->first('chasis_no') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Engine No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Engine No</strong>
                                        <input type="text" class="form-control" name="engine_no"
                                            value="{{ old('engine_no', $vehicle->engine_no ?? '') }}">
                                        @if ($errors->has('engine_no'))
                                            <label class="text-danger">{{ $errors->first('engine_no') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Ownership -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Ownership</strong>
                                        <input type="text" class="form-control" name="ownership"
                                            value="{{ old('ownership', $vehicle->ownership ?? '') }}">
                                        @if ($errors->has('ownership'))
                                            <label class="text-danger">{{ $errors->first('ownership') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pool Vehicle -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Pool Vehicle</strong>
                                        <div class="d-flex align-items-center mt-2">
                                            <div class="form-check mr-3">
                                                <input class="form-check-input" type="radio" name="pool_vehicle"
                                                    id="pool_yes" value="1"
                                                    {{ old('pool_vehicle', $vehicle->pool_vehicle ?? '') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="pool_yes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pool_vehicle"
                                                    id="pool_no" value="0"
                                                    {{ old('pool_vehicle', $vehicle->pool_vehicle ?? '') == '0' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="pool_no">No</label>
                                            </div>
                                        </div>
                                        @if ($errors->has('pool_vehicle'))
                                            <div class="mt-1">
                                                <label class="text-danger">{{ $errors->first('pool_vehicle') }}</label>
                                            </div>
                                        @endif
                                    </div>
                                </div>



                                <!-- Cone -->
                                <div class="col-md-3 d-none">
                                    <div class="form-group">
                                        <strong>Cone</strong>
                                        <input type="number" min="0" step="1" class="form-control"
                                            name="cone" value="{{ old('cone', $vehicle->cone ?? '') }}">
                                        @if ($errors->has('cone'))
                                            <label class="text-danger">{{ $errors->first('cone') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- PSO Card Details -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>PSO Card Details</strong>
                                        <input type="text" class="form-control" name="pso_card"
                                            value="{{ old('pso_card', $vehicle->pso_card ?? '') }}">
                                        @if ($errors->has('pso_card'))
                                            <label class="text-danger">{{ $errors->first('pso_card') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- AKPL -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>AKPL</strong>
                                        <input type="text" class="form-control" name="akpl"
                                            value="{{ old('akpl', $vehicle->akpl ?? '') }}">
                                        @if ($errors->has('akpl'))
                                            <label class="text-danger">{{ $errors->first('akpl') }}</label>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Parking KM</strong>
                                        <input type="number" step="0.01" min="0" class="form-control"
                                            name="parking_km" value="{{ old('parking_km', $vehicle->parking_km ?? '') }}">
                                        @if ($errors->has('parking_km'))
                                            <label class="text-danger">{{ $errors->first('parking_km') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Shift Hours -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Shift Hours</strong>
                                        <select class="custom-select" name="shift_hour_id">
                                            <option value="">-- Select --</option>
                                            @foreach ($shift_hours as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('shift_hour_id', $vehicle->shift_hour_id ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('shift_hours'))
                                            <label class="text-danger">{{ $errors->first('shift_hours') }}</label>
                                        @endif
                                    </div>
                                </div>

                                @php
                                    $primaryAssignedDriver = $vehicle->drivers->firstWhere('id', $vehicle->primary_driver_id) ?? $vehicle->primaryDriver;
                                    $secondaryAssignedDriver = $vehicle->drivers->first(function ($assignedDriver) use ($vehicle) {
                                        return (int) $assignedDriver->id !== (int) $vehicle->primary_driver_id;
                                    });
                                @endphp

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Driver 1<span id="primary-driver-required-marker" style="color:red">*</span></strong>
                                        <select name="primary_driver_id" id="primary_driver_id"
                                            class="custom-select @error('primary_driver_id') is-invalid @enderror">
                                            <option value="">-- Select Driver --</option>
                                            @foreach ($regularDrivers as $driver)
                                                <option value="{{ $driver['id'] }}"
                                                    {{ (int) old('primary_driver_id', $vehicle->primary_driver_id) === (int) $driver['id'] ? 'selected' : '' }}>
                                                    {{ $driver['name'] }}{{ $driver['vehicle_no'] && (int) $driver['vehicle_id'] !== (int) $vehicle->id ? ' (Assigned: ' . $driver['vehicle_no'] . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('primary_driver_id')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Shift Timing 1<span style="color:red">*</span></strong>
                                        <select name="primary_shift_timing_id" id="primary_shift_timing_id"
                                            class="form-control @error('primary_shift_timing_id') is-invalid @enderror">
                                            <option value="">Select Shift Timing</option>
                                            @foreach ($shift_timings as $id => $name)
                                                <option value="{{ $id }}"
                                                    {{ (int) old('primary_shift_timing_id', optional($primaryAssignedDriver)->shift_timing_id) === (int) $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('primary_shift_timing_id')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3 secondary-driver-block">
                                    <div class="form-group">
                                        <strong>Driver 2</strong>
                                        <select name="secondary_driver_id" id="secondary_driver_id"
                                            class="custom-select @error('secondary_driver_id') is-invalid @enderror">
                                            <option value="">-- Select Driver --</option>
                                            @foreach ($regularDrivers as $driver)
                                                <option value="{{ $driver['id'] }}"
                                                    {{ (int) old('secondary_driver_id', optional($secondaryAssignedDriver)->id) === (int) $driver['id'] ? 'selected' : '' }}>
                                                    {{ $driver['name'] }}{{ $driver['vehicle_no'] && (int) $driver['vehicle_id'] !== (int) $vehicle->id ? ' (Assigned: ' . $driver['vehicle_no'] . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('secondary_driver_id')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3 secondary-driver-block">
                                    <div class="form-group">
                                        <strong>Shift Timing 2</strong>
                                        <select name="secondary_shift_timing_id" id="secondary_shift_timing_id"
                                            class="form-control @error('secondary_shift_timing_id') is-invalid @enderror">
                                            <option value="">Select Shift Timing</option>
                                            @foreach ($shift_timings as $id => $name)
                                                <option value="{{ $id }}"
                                                    {{ (int) old('secondary_shift_timing_id', optional($secondaryAssignedDriver)->shift_timing_id) === (int) $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('secondary_shift_timing_id')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <small id="assigned-driver-hint" class="form-text text-muted">
                                        Only 1 driver allowed for 12-hour vehicles.
                                    </small>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Pool Drivers</strong>
                                        @php
                                            $selectedPoolDrivers = old('pool_driver_ids', $vehicle->poolDrivers->pluck('id')->all());
                                        @endphp
                                        <select name="pool_driver_ids[]" id="pool_driver_ids" class="form-control" multiple size="4">
                                            @foreach ($poolDrivers as $driver)
                                                <option value="{{ $driver['id'] }}" data-station="{{ $driver['station_id'] }}"
                                                    {{ in_array((string) $driver['id'], array_map('strval', (array) $selectedPoolDrivers), true) ? 'selected' : '' }}>
                                                    {{ $driver['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('pool_driver_ids')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                        @error('pool_driver_ids.*')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>New Vehicle?<span style="color:red">*</span></strong>
                                        <select name="is_new_vehicle" class="custom-select @error('is_new_vehicle') is-invalid @enderror">
                                            <option value="0" {{ (string) old('is_new_vehicle', (int) $vehicle->is_new_vehicle) === '0' ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ (string) old('is_new_vehicle', (int) $vehicle->is_new_vehicle) === '1' ? 'selected' : '' }}>Yes</option>
                                        </select>
                                        @error('is_new_vehicle')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>


                                <!-- Vehicle Type -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Vehicle Type</strong>
                                        <select class="custom-select" name="vehicle_type_id">
                                            <option value="">-- Select Vehicle Type --</option>
                                            @foreach ($vehicleTypes as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('vehicle_type_id', $vehicle->vehicle_type_id ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('vehicle_type_id'))
                                            <label class="text-danger">{{ $errors->first('vehicle_type_id') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Fabrication Vendor -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Fabrication Vendor</strong>
                                        <select class="custom-select" name="fabrication_vendor_id">
                                            <option value="">-- Select --</option>
                                            @foreach ($vendors as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('fabrication_vendor_id', $vehicle->fabrication_vendor_id ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('fabrication_vendor_id'))
                                            <label
                                                class="text-danger">{{ $errors->first('fabrication_vendor_id') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Station -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong for="station_id">Station</strong>
                                        <select name="station_id" id="station_id" class="form-control">
                                            <option value="">-- Select Station --</option>
                                            @foreach ($stations as $station)
                                                <option value="{{ $station->id }}"
                                                    {{ old('station_id', $vehicle->station_id ?? '') == $station->id ? 'selected' : '' }}>
                                                    {{ $station->area }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('station_id'))
                                            <label class="text-danger">{{ $errors->first('station_id') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- IBC Center -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong for="ibc_center_id">IBC Center</strong>
                                        <select name="ibc_center_id" id="ibc_center_id" class="form-control">
                                            <option value="">-- Select IBC Center --</option>
                                            @foreach ($stations as $station)
                                                @foreach ($station->ibcCenter ?? [] as $center)
                                                    <option value="{{ $center->id }}"
                                                        data-station="{{ $station->id }}"
                                                        {{ old('ibc_center_id', $vehicle->ibc_center_id ?? '') == $center->id ? 'selected' : '' }}>
                                                        {{ $center->name }}
                                                    </option>
                                                @endforeach
                                            @endforeach
                                        </select>
                                        @if ($errors->has('ibc_center_id'))
                                            <label class="text-danger">{{ $errors->first('ibc_center_id') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Medical Box -->
                                {{--                <div class="col-md-2"> --}}
                                {{--                  <div class="form-group"> --}}
                                {{--                    <strong>Medical Box</strong> --}}
                                {{--                    <select class="custom-select" name="medical_box"> --}}
                                {{--                      <option value="">-- Select --</option> --}}
                                {{--                      @foreach ($status as $key => $value) --}}
                                {{--                        <option value="{{$key}}" {{ old('medical_box', $vehicle->medical_box ?? '') == $key ? 'selected' : '' }}>{{$value}}</option> --}}
                                {{--                      @endforeach --}}
                                {{--                    </select> --}}
                                {{--                    @if ($errors->has('medical_box')) --}}
                                {{--                      <label class="text-danger">{{ $errors->first('medical_box') }}</label> --}}
                                {{--                    @endif --}}
                                {{--                  </div> --}}
                                {{--                </div> --}}

                                <!-- On Duty Status -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>On Duty Status</strong>
                                        <div class="d-flex align-items-center mt-2">
                                            <div class="form-check mr-3">
                                                <input class="form-check-input" type="radio" name="on_duty_status"
                                                    id="on_duty_status_yes" value="1"
                                                    {{ old('on_duty_status', $vehicle->on_duty_status ?? '') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="on_duty_status_yes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="on_duty_status"
                                                    id="on_duty_status_no" value="0"
                                                    {{ old('on_duty_status', $vehicle->on_duty_status ?? '') == '0' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="on_duty_status_no">No</label>
                                            </div>
                                        </div>
                                        @if ($errors->has('on_duty_status'))
                                            <div class="mt-1">
                                                <label class="text-danger">{{ $errors->first('on_duty_status') }}</label>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Seat Cover -->
                                <div class="col-md-2 d-none">
                                    <div class="form-group">
                                        <strong>Seat Cover</strong>
                                        <select class="custom-select" name="seat_cover">
                                            <option value="">-- Select --</option>
                                            @foreach ($status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('seat_cover', $vehicle->seat_cover ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('seat_cover'))
                                            <label class="text-danger">{{ $errors->first('seat_cover') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Fire Extinguisher -->
                                <div class="col-md-2 d-none">
                                    <div class="form-group">
                                        <strong>Fire Extinguisher</strong>
                                        <select class="custom-select" name="fire_extenguisher">
                                            <option value="">-- Select --</option>
                                            @foreach ($status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('fire_extenguisher', $vehicle->fire_extenguisher ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('fire_extenguisher'))
                                            <label class="text-danger">{{ $errors->first('fire_extenguisher') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Tracker Installation Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Tracker Installation Date </strong>
                                        <input type="date" class="form-control" name="tracker_installation_date"
                                            value="{{ old('tracker_installation_date', $dateValue($vehicle->tracker_installation_date ?? null)) }}">
                                        @if ($errors->has('tracker_installation_date'))
                                            <label
                                                class="text-danger">{{ $errors->first('tracker_installation_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Inspection Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Inspection Date</strong>
                                        <input type="date" class="form-control" id="inspection_date" name="inspection_date"
                                            value="{{ old('inspection_date', $dateValue($vehicle->inspection_date ?? null)) }}">
                                        @if ($errors->has('inspection_date'))
                                            <label class="text-danger">{{ $errors->first('inspection_date') }}</label>
                                        @endif

                                    </div>
                                </div>

                                <!-- Next Inspection Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Next Inspection Date</strong>
                                        <input type="date" class="form-control" id="next_inspection_date" name="next_inspection_date" readonly
                                            value="{{ old('next_inspection_date', $dateValue($vehicle->next_inspection_date ?? null)) }}">
                                        @if ($errors->has('next_inspection_date'))
                                            <label
                                                class="text-danger">{{ $errors->first('next_inspection_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Induction Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Induction Date</strong>
                                        <input type="date" class="form-control" name="induction_date"
                                            value="{{ old('induction_date', $dateValue($vehicle->induction_date ?? null)) }}">
                                        @if ($errors->has('induction_date'))
                                            <label class="text-danger">{{ $errors->first('induction_date') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Fitness Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Fitness Date</strong>
                                        <input type="date" class="form-control" id="fitness_date" name="fitness_date"
                                            value="{{ old('fitness_date', $dateValue($vehicle->fitness_date ?? null)) }}">
                                        @if ($errors->has('fitness_date'))
                                            <label class="text-danger">{{ $errors->first('fitness_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Next Fitness Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Fitness Expiry Date</strong>
                                        <input type="date" class="form-control" id="next_fitness_date" name="next_fitness_date" readonly
                                            value="{{ old('next_fitness_date', $dateValue($vehicle->next_fitness_date ?? null)) }}">
                                        @if ($errors->has('next_fitness_date'))
                                            <label class="text-danger">{{ $errors->first('next_fitness_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Fitness Attachment -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Fitness Attachment</strong>
                                        <input type="file" class="form-control" name="fitness_file">
                                        @if ($errors->has('fitness_file'))
                                            <label class="text-danger">{{ $errors->first('fitness_file') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Registration Attachment -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Registration Attachment</strong>
                                        <input type="file" class="form-control" name="registration_file">
                                        @if ($errors->has('registration_file'))
                                            <label class="text-danger">{{ $errors->first('registration_file') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Insurance Company -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Insurance Company</strong>
                                        <select class="custom-select" name="insurance_company_id">
                                            <option value="">Select Insurance Company</option>
                                            @foreach ($insurance_companies as $key => $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('insurance_company_id', $vehicle->insurance_company_id ?? '') == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('insurance_company_id'))
                                            <label
                                                class="text-danger">{{ $errors->first('insurance_company_id') }}</label>
                                        @endif
                                    </div>
                                </div>
                                <!-- Insurance policy no -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Insurance Policy No.</strong>
                                        <input type="text" class="form-control" name="insurance_policy_no"
                                            value="{{ old('insurance_policy_no', $vehicle->insurance_policy_no ?? '') }}">
                                        @if ($errors->has('insurance_policy_no'))
                                            <label class="text-danger">{{ $errors->first('insurance_policy_no') }}</label>
                                        @endif
                                    </div>
                                </div>
                                <!-- Insurance Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Insurance Date</strong>
                                        <input type="date" class="form-control" id="insurance_date" name="insurance_date"
                                            value="{{ old('insurance_date', $dateValue($vehicle->insurance_date ?? null)) }}">
                                        @if ($errors->has('insurance_date'))
                                            <label class="text-danger">{{ $errors->first('insurance_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Insurance Expiry Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Insurance Expiry Date</strong>
                                        <input type="date" class="form-control" id="insurance_expiry_date" name="insurance_expiry_date" readonly
                                            value="{{ old('insurance_expiry_date', $dateValue($vehicle->insurance_expiry_date ?? null)) }}">
                                        @if ($errors->has('insurance_expiry_date'))
                                            <label
                                                class="text-danger">{{ $errors->first('insurance_expiry_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Insurance Attachment -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Insurance Attachment</strong>
                                        <input type="file" class="form-control" name="insurance_file">
                                        @if ($errors->has('insurance_file'))
                                            <label class="text-danger">{{ $errors->first('insurance_file') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Route Permit Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Route Permit Date</strong>
                                        <input type="date" class="form-control" id="route_permit_date" name="route_permit_date"
                                            value="{{ old('route_permit_date', $dateValue($vehicle->route_permit_date ?? null)) }}">
                                        @if ($errors->has('route_permit_date'))
                                            <label class="text-danger">{{ $errors->first('route_permit_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Route Permit Expiry Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Route Permit Expiry Date</strong>
                                        <input type="date" class="form-control" id="route_permit_expiry_date" name="route_permit_expiry_date" readonly
                                            value="{{ old('route_permit_expiry_date', $dateValue($vehicle->route_permit_expiry_date ?? null)) }}">
                                        @if ($errors->has('route_permit_expiry_date'))
                                            <label
                                                class="text-danger">{{ $errors->first('route_permit_expiry_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Route Permit Attachment -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Route Permit Attachment</strong>
                                        <input type="file" class="form-control" name="route_permit_file">
                                        @if ($errors->has('route_permit_file'))
                                            <label class="text-danger">{{ $errors->first('route_permit_file') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Tax Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Tax Date</strong>
                                        <input type="date" class="form-control" id="tax_date" name="tax_date"
                                            value="{{ old('tax_date', $dateValue($vehicle->tax_date ?? null)) }}">
                                        @if ($errors->has('tax_date'))
                                            <label class="text-danger">{{ $errors->first('tax_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Next Tax Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Next Tax Date</strong>
                                        <input type="date" class="form-control" id="next_tax_date" name="next_tax_date" readonly
                                            value="{{ old('next_tax_date', $dateValue($vehicle->next_tax_date ?? null)) }}">
                                        @if ($errors->has('next_tax_date'))
                                            <label class="text-danger">{{ $errors->first('next_tax_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Tax Attachment -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Tax Attachment</strong>
                                        <input type="file" class="form-control" name="tax_file">
                                        @if ($errors->has('tax_file'))
                                            <label class="text-danger">{{ $errors->first('tax_file') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-3">
                                    <label for=""></label>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <a href="{{ route('vehicles.index') }}" class="btn btn-warning">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stationSelect = document.getElementById('station_id');
            const ibcCenterSelect = document.getElementById('ibc_center_id');
            const poolDriverSelect = document.getElementById('pool_driver_ids');
            const shiftHourSelect = document.querySelector('select[name="shift_hour_id"]');
            const assignedDriverHint = document.getElementById('assigned-driver-hint');
            const secondaryDriverBlocks = document.querySelectorAll('.secondary-driver-block');
            const secondaryDriverSelect = document.getElementById('secondary_driver_id');
            const secondaryShiftTimingSelect = document.getElementById('secondary_shift_timing_id');
            const inspectionDateInput = document.getElementById('inspection_date');
            const nextInspectionDateInput = document.getElementById('next_inspection_date');
            const fitnessDateInput = document.getElementById('fitness_date');
            const nextFitnessDateInput = document.getElementById('next_fitness_date');
            const insuranceDateInput = document.getElementById('insurance_date');
            const insuranceExpiryDateInput = document.getElementById('insurance_expiry_date');
            const routePermitDateInput = document.getElementById('route_permit_date');
            const routePermitExpiryDateInput = document.getElementById('route_permit_expiry_date');
            const taxDateInput = document.getElementById('tax_date');
            const nextTaxDateInput = document.getElementById('next_tax_date');
            const vehicleConditionSelect = document.querySelector('select[name="is_new_vehicle"]');
            const poolVehicleInputs = document.querySelectorAll('input[name="pool_vehicle"]');
            const primaryDriverRequiredMarker = document.getElementById('primary-driver-required-marker');

            function formatDate(date) {
                if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function calculateNextInspectionDate() {
                if (!inspectionDateInput || !nextInspectionDateInput) return;
                if (!inspectionDateInput.value) {
                    nextInspectionDateInput.value = '';
                    return;
                }

                const inspectionDate = new Date(inspectionDateInput.value);
                inspectionDate.setMonth(inspectionDate.getMonth() + 8);
                nextInspectionDateInput.value = formatDate(inspectionDate);
            }

            function calculateFitnessExpiryDate() {
                if (!fitnessDateInput || !nextFitnessDateInput || !vehicleConditionSelect) return;
                if (!fitnessDateInput.value) {
                    nextFitnessDateInput.value = '';
                    return;
                }

                const fitnessDate = new Date(fitnessDateInput.value);
                fitnessDate.setMonth(fitnessDate.getMonth() + (vehicleConditionSelect.value === '1' ? 6 : 12));
                nextFitnessDateInput.value = formatDate(fitnessDate);
            }

            function calculateInsuranceExpiryDate() {
                if (!insuranceDateInput || !insuranceExpiryDateInput) return;
                if (!insuranceDateInput.value) {
                    insuranceExpiryDateInput.value = '';
                    return;
                }

                const insuranceDate = new Date(insuranceDateInput.value);
                insuranceDate.setFullYear(insuranceDate.getFullYear() + 1);
                insuranceExpiryDateInput.value = formatDate(insuranceDate);
            }

            function calculateRoutePermitExpiryDate() {
                if (!routePermitDateInput || !routePermitExpiryDateInput) return;
                if (!routePermitDateInput.value) {
                    routePermitExpiryDateInput.value = '';
                    return;
                }

                const routePermitDate = new Date(routePermitDateInput.value);
                routePermitDate.setFullYear(routePermitDate.getFullYear() + 1);
                routePermitExpiryDateInput.value = formatDate(routePermitDate);
            }

            function calculateNextTaxDate() {
                if (!taxDateInput || !nextTaxDateInput) return;
                if (!taxDateInput.value) {
                    nextTaxDateInput.value = '';
                    return;
                }

                const taxDate = new Date(taxDateInput.value);
                taxDate.setFullYear(taxDate.getFullYear() + 1);
                nextTaxDateInput.value = formatDate(taxDate);
            }

            function filterIBCCenters() {
                const selectedStation = stationSelect.value;
                const currentIBC = ibcCenterSelect.value;
                let matchFound = false;

                Array.from(ibcCenterSelect.options).forEach(option => {
                    if (!option.value) return;

                    const matches = option.getAttribute('data-station') === selectedStation;
                    option.style.display = matches ? 'block' : 'none';

                    if (matches && option.value === currentIBC) {
                        matchFound = true;
                    }
                });

                if (!matchFound) {
                    ibcCenterSelect.value = '';
                }
            }

            function filterPoolDrivers() {
                if (!poolDriverSelect) return;

                const selectedStation = stationSelect.value;

                Array.from(poolDriverSelect.options).forEach(option => {
                    const matches = !selectedStation || option.getAttribute('data-station') === selectedStation;
                    option.hidden = !matches;

                    if (!matches) {
                        option.selected = false;
                    }
                });
            }

            function isTwentyFourHourShift() {
                if (!shiftHourSelect) return false;
                const selectedText = shiftHourSelect.options[shiftHourSelect.selectedIndex]?.text?.toLowerCase() || '';
                return selectedText.includes('24');
            }

            function toggleSecondaryDriverFields() {
                const showSecondaryDriver = isTwentyFourHourShift();
                if (assignedDriverHint) {
                    assignedDriverHint.textContent = showSecondaryDriver
                        ? 'Maximum 2 drivers allowed for 24-hour vehicles.'
                        : 'Only 1 driver allowed for 12-hour vehicles.';
                }

                secondaryDriverBlocks.forEach(block => {
                    block.style.display = showSecondaryDriver ? '' : 'none';
                });

                if (!showSecondaryDriver) {
                    if (secondaryDriverSelect) {
                        secondaryDriverSelect.value = '';
                    }

                    if (secondaryShiftTimingSelect) {
                        secondaryShiftTimingSelect.value = '';
                    }
                }
            }

            function togglePrimaryDriverRequiredMarker() {
                if (!primaryDriverRequiredMarker) return;
                const selectedPoolVehicle = document.querySelector('input[name="pool_vehicle"]:checked')?.value;
                primaryDriverRequiredMarker.style.display = selectedPoolVehicle === '1' ? 'none' : '';
            }

            stationSelect.addEventListener('change', function() {
                filterIBCCenters();
                filterPoolDrivers();
            });
            poolVehicleInputs.forEach(input => {
                input.addEventListener('change', togglePrimaryDriverRequiredMarker);
            });
            shiftHourSelect?.addEventListener('change', toggleSecondaryDriverFields);
            inspectionDateInput?.addEventListener('change', calculateNextInspectionDate);
            fitnessDateInput?.addEventListener('change', calculateFitnessExpiryDate);
            insuranceDateInput?.addEventListener('change', calculateInsuranceExpiryDate);
            routePermitDateInput?.addEventListener('change', calculateRoutePermitExpiryDate);
            taxDateInput?.addEventListener('change', calculateNextTaxDate);
            vehicleConditionSelect?.addEventListener('change', calculateFitnessExpiryDate);

            filterIBCCenters();
            filterPoolDrivers();
            toggleSecondaryDriverFields();
            togglePrimaryDriverRequiredMarker();
            calculateNextInspectionDate();
            calculateFitnessExpiryDate();
            calculateInsuranceExpiryDate();
            calculateRoutePermitExpiryDate();
            calculateNextTaxDate();
        });
    </script>

@endsection
