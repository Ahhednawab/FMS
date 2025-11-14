@extends('layouts.admin')

@section('title', 'Add Vehicle')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.vehicles.index') }}" class="btn btn-primary">
            <span>View Vehicles <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.vehicles.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          @if(isset($draftId))
            <input type="hidden" name="draft_id" value="{{ $draftId }}">
          @endif
            @php
                $isDraftMode = request()->has('draft_id');
            @endphp

          <div class="row">
            <!-- Serial NO -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Serial No</strong>
                <input value="{{$serial_no}}" name="serial_no" type="text" class="form-control" readonly>
              </div>
            </div>

            <!-- Vehicle No -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Vehicle No</strong>
                  <input type="text"
                         class="form-control
                {{ $isDraftMode && empty($draftData['vehicle_no'] ?? '') ? 'is-invalid' : '' }}
                @error('vehicle_no') is-invalid @enderror"
                         name="vehicle_no"
                         value="{{ $draftData['vehicle_no'] ?? old('vehicle_no') }}">

                  @if ($isDraftMode && empty($draftData['vehicle_no'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  @error('vehicle_no')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Make (Manufacturer) -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Make (Manufacturer)</strong>
                  <input type="text"
                         class="form-control
                {{ $isDraftMode && empty($draftData['make'] ?? '') ? 'is-invalid' : '' }}
                @error('make') is-invalid @enderror"
                         name="make"
                         value="{{ $draftData['make'] ?? old('make') }}">

                  @if ($isDraftMode && empty($draftData['make'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  @error('make')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Model (Year) -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Model (Year)</strong>
                  <select name="model"
                          class="custom-select
                {{ request()->has('draft_id') && empty($draftData['model'] ?? '') ? 'is-invalid' : '' }}
                @error('model') is-invalid @enderror">

                      <option value="">-- Select Model Year --</option>

                      @for ($year = date('Y'); $year >= 1980; $year--)
                          <option value="{{ $year }}"
                              {{ ($draftData['model'] ?? old('model')) == $year ? 'selected' : '' }}>
                              {{ $year }}
                          </option>
                      @endfor
                  </select>

                  {{-- Draft mode validation --}}
                  @if (request()->has('draft_id') && empty($draftData['model'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel validation --}}
                  @error('model')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Chasis No -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Chasis No</strong>
                  <input type="text"
                         class="form-control
                {{ $isDraftMode && empty($draftData['chasis_no'] ?? '') ? 'is-invalid' : '' }}
                @error('chasis_no') is-invalid @enderror"
                         name="chasis_no"
                         value="{{ $draftData['chasis_no'] ?? old('chasis_no') }}">

                  @if ($isDraftMode && empty($draftData['chasis_no'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  @error('chasis_no')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Engine No -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Engine No</strong>
                  <input type="text"
                         class="form-control
                {{ $isDraftMode && empty($draftData['engine_no'] ?? '') ? 'is-invalid' : '' }}
                @error('engine_no') is-invalid @enderror"
                         name="engine_no"
                         value="{{ $draftData['engine_no'] ?? old('engine_no') }}">

                  @if ($isDraftMode && empty($draftData['engine_no'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  @error('engine_no')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Ownership -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Ownership</strong>
                  <input type="text"
                         name="ownership"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['ownership'] ?? '') ? 'is-invalid' : '' }}
               @error('ownership') is-invalid @enderror"
                         value="{{ $draftData['ownership'] ?? old('ownership') }}">

                  {{-- Draft mode validation --}}
                  @if (request()->has('draft_id') && empty($draftData['ownership'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  @error('ownership')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>
            <!-- Pool Vehicle -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Pool Vehicle</strong>
                  @php
                      $draftMissing = request()->has('draft_id') && !isset($draftData['pool_vehicle']);
                  @endphp

                  <div class="d-flex align-items-center mt-2">

                      <div class="form-check mr-3">
                          <input class="form-check-input {{ $draftMissing ? 'border-danger' : '' }}"
                                 type="radio"
                                 name="pool_vehicle"
                                 id="pool_yes"
                                 value="1"
                              {{ ($draftData['pool_vehicle'] ?? old('pool_vehicle')) == '1' ? 'checked' : '' }}>
                          <label class="form-check-label {{ $draftMissing ? 'text-danger' : '' }}" for="pool_yes">Yes</label>
                      </div>

                      <div class="form-check">
                          <input class="form-check-input {{ $draftMissing ? 'border-danger' : '' }}"
                                 type="radio"
                                 name="pool_vehicle"
                                 id="pool_no"
                                 value="0"
                              {{ ($draftData['pool_vehicle'] ?? old('pool_vehicle')) == '0' ? 'checked' : '' }}>
                          <label class="form-check-label {{ $draftMissing ? 'text-danger' : '' }}" for="pool_no">No</label>
                      </div>
                  </div>

                  {{-- Draft Required Message --}}
                  @if ($draftMissing)
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('pool_vehicle')
                  <div class="mt-1">
                      <label class="text-danger">{{ $message }}</label>
                  </div>
                  @enderror
              </div>
            </div>

            <!-- Cone -->
            <div class="col-md-3 d-none">
              <div class="form-group">
                <strong>Cone</strong>
                <input type="number" min="0" step="1" class="form-control" name="cone" value="{{ $draftData['cone'] ?? old('cone') }}">
                @if ($errors->has('cone'))
                  <label class="text-danger">{{ $errors->first('cone') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- PSO Card Details -->
              @php
                  $isDraftMode = request()->has('draft_id');
              @endphp

              <div class="col-md-3">
                  <div class="form-group">
                      <strong>PSO Card Details</strong>

                      <input
                          type="text"
                          class="form-control
            {{ $isDraftMode && empty($draftData['pso_card'] ?? '') ? 'is-invalid' : '' }}"
                          name="pso_card"
                          value="{{ $draftData['pso_card'] ?? old('pso_card') }}"
                      >

                      @if ($errors->has('pso_card'))
                          <label class="text-danger">{{ $errors->first('pso_card') }}</label>
                      @endif
                  </div>
              </div>



              <!-- AKPL -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>AKPL</strong>
                  <input type="text"
                         name="akpl"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['akpl'] ?? '') ? 'is-invalid' : '' }}
               @error('akpl') is-invalid @enderror"
                         value="{{ $draftData['akpl'] ?? old('akpl') }}">

                  {{-- Draft mode validation --}}
                  @if (request()->has('draft_id') && empty($draftData['akpl'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel validation --}}
                  @error('akpl')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Shift Hours -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Shift Hours</strong>
                  <select name="shift_hour_id"
                          class="custom-select
                {{ request()->has('draft_id') && empty($draftData['shift_hour_id'] ?? '') ? 'is-invalid' : '' }}
                @error('shift_hour_id') is-invalid @enderror">

                      <option value="">-- Select --</option>

                      @foreach($shift_hours as $key => $value)
                          <option value="{{ $key }}"
                              {{ ($draftData['shift_hour_id'] ?? old('shift_hour_id')) == $key ? 'selected' : '' }}>
                              {{ $value }}
                          </option>
                      @endforeach
                  </select>

                  {{-- Draft mode validation --}}
                  @if (request()->has('draft_id') && empty($draftData['shift_hour_id'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel validation --}}
                  @error('shift_hour_id')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Vehicle Type -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Vehicle Type</strong>
                  <select name="vehicle_type_id"
                          class="custom-select
                {{ request()->has('draft_id') && empty($draftData['vehicle_type_id'] ?? '') ? 'is-invalid' : '' }}
                @error('vehicle_type_id') is-invalid @enderror">

                      <option value="">-- Select --</option>

                      @foreach($vehicleTypes as $key => $value)
                          <option value="{{ $key }}"
                              {{ ($draftData['vehicle_type_id'] ?? old('vehicle_type_id')) == $key ? 'selected' : '' }}>
                              {{ $value }}
                          </option>
                      @endforeach

                  </select>

                  {{-- Draft mode validation --}}
                  @if (request()->has('draft_id') && empty($draftData['vehicle_type_id'] ?? ''))
                      <span class="text-danger">Required in draft</span>
                  @endif

                  {{-- Laravel validation --}}
                  @error('vehicle_type_id')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Fabrication Vendor -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Fabrication Vendor</strong>
                  <select name="fabrication_vendor_id"
                          class="custom-select
                {{ request()->has('draft_id') && empty($draftData['fabrication_vendor_id'] ?? '') ? 'is-invalid' : '' }}
                @error('fabrication_vendor_id') is-invalid @enderror">

                      <option value="">-- Select --</option>

                      @foreach($vendors as $key => $value)
                          <option value="{{ $key }}"
                              {{ ($draftData['fabrication_vendor_id'] ?? old('fabrication_vendor_id')) == $key ? 'selected' : '' }}>
                              {{ $value }}
                          </option>
                      @endforeach

                  </select>

                  {{-- Draft mode validation --}}
                  @if (request()->has('draft_id') && empty($draftData['fabrication_vendor_id'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  @error('fabrication_vendor_id')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Station -->
            <div class="col-md-3">
              <div class="form-group">
                <strong for="station_id">Station</strong>
                  <select name="station_id" id="station_id"
                          class="form-control
                {{ request()->has('draft_id') && empty($draftData['station_id'] ?? '') ? 'is-invalid' : '' }}
                @error('station_id') is-invalid @enderror">

                      <option value="">-- Select Station --</option>

                      @foreach($stations as $station)
                          <option value="{{ $station->id }}"
                              {{ ($draftData['station_id'] ?? old('station_id')) == $station->id ? 'selected' : '' }}>
                              {{ $station->area }}
                          </option>
                      @endforeach

                  </select>

                  {{-- Draft mode validation --}}
                  @if (request()->has('draft_id') && empty($draftData['station_id'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel validation --}}
                  @error('station_id')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- IBC Center -->
            <div class="col-md-3">
              <div class="form-group">
                <strong for="ibc_center_id">IBC Center</strong>
                  <select name="ibc_center_id" id="ibc_center_id"
                          class="form-control
                {{ request()->has('draft_id') && empty($draftData['ibc_center_id'] ?? '') ? 'is-invalid' : '' }}
                @error('ibc_center_id') is-invalid @enderror">

                      <option value="">-- Select IBC Center --</option>

                      @foreach($stations as $station)
                          @foreach($station->ibcCenter ?? [] as $center)
                              <option value="{{ $center->id }}"
                                      data-station="{{ $station->id }}"
                                  {{ ($draftData['ibc_center_id'] ?? old('ibc_center_id')) == $center->id ? 'selected' : '' }}>
                                  {{ $center->name }}
                              </option>
                          @endforeach
                      @endforeach

                  </select>

                  {{-- Draft mode validation --}}
                  @if (request()->has('draft_id') && empty($draftData['ibc_center_id'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel validation --}}
                  @error('ibc_center_id')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Medical Box -->
{{--            <div class="col-md-2">--}}
{{--              <div class="form-group">--}}
{{--                <strong>Medical Box</strong>--}}
{{--                <select class="custom-select" name="medical_box">--}}
{{--                  <option value="">-- Select --</option>--}}
{{--                  @foreach($status as $key => $value)--}}
{{--                    <option value="{{$key}}" {{ ($draftData['medical_box'] ?? old('medical_box')) == $key ? 'selected' : '' }}>{{$value}}</option>--}}
{{--                  @endforeach--}}
{{--                </select>--}}
{{--                @if ($errors->has('medical_box'))--}}
{{--                  <label class="text-danger">{{ $errors->first('medical_box') }}</label>--}}
{{--                @endif--}}
{{--              </div>--}}
{{--            </div>--}}

            <!-- On Duty Status -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>On Duty Status</strong>
                  @php
                      $draftMissing = request()->has('draft_id') && !isset($draftData['on_duty_status']);
                  @endphp

                  <div class="d-flex align-items-center mt-2">

                      <div class="form-check mr-3">
                          <input class="form-check-input {{ $draftMissing ? 'border-danger' : '' }}"
                                 type="radio"
                                 name="on_duty_status"
                                 id="on_duty_status_yes"
                                 value="1"
                              {{ ($draftData['on_duty_status'] ?? old('on_duty_status')) == '1' ? 'checked' : '' }}>
                          <label class="form-check-label {{ $draftMissing ? 'text-danger' : '' }}" for="on_duty_status_yes">Yes</label>
                      </div>

                      <div class="form-check">
                          <input class="form-check-input {{ $draftMissing ? 'border-danger' : '' }}"
                                 type="radio"
                                 name="on_duty_status"
                                 id="on_duty_status_no"
                                 value="0"
                              {{ ($draftData['on_duty_status'] ?? old('on_duty_status')) == '0' ? 'checked' : '' }}>
                          <label class="form-check-label {{ $draftMissing ? 'text-danger' : '' }}" for="on_duty_status_no">No</label>
                      </div>
                  </div>

                  {{-- Draft Required Message --}}
                  @if ($draftMissing)
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('on_duty_status')
                  <div class="mt-1">
                      <label class="text-danger">{{ $message }}</label>
                  </div>
                  @enderror
              </div>
            </div>

            <!-- Seat Cover -->
            <div class="col-md-2 d-none">
              <div class="form-group">
                <strong>Seat Cover</strong>
                  <select name="seat_cover"
                          class="custom-select
                {{ request()->has('draft_id') && empty($draftData['seat_cover'] ?? '') ? 'is-invalid' : '' }}
                @error('seat_cover') is-invalid @enderror">

                      <option value="">-- Select --</option>

                      @foreach($status as $key => $value)
                          <option value="{{ $key }}"
                              {{ ($draftData['seat_cover'] ?? old('seat_cover')) == $key ? 'selected' : '' }}>
                              {{ $value }}
                          </option>
                      @endforeach

                  </select>

                  {{-- Draft mode validation --}}
                  @if (request()->has('draft_id') && empty($draftData['seat_cover'] ?? ''))
                      <span class="text-danger">Required in draft</span>
                  @endif

                  {{-- Laravel validation --}}
                  @error('seat_cover')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Fire Extinguisher -->
            <div class="col-md-2 d-none">
              <div class="form-group">
                <strong>Fire Extinguisher</strong>
                  <select name="fire_extenguisher"
                          class="custom-select
                {{ request()->has('draft_id') && empty($draftData['fire_extenguisher'] ?? '') ? 'is-invalid' : '' }}
                @error('fire_extenguisher') is-invalid @enderror">

                      <option value="">-- Select --</option>

                      @foreach($status as $key => $value)
                          <option value="{{ $key }}"
                              {{ ($draftData['fire_extenguisher'] ?? old('fire_extenguisher')) == $key ? 'selected' : '' }}>
                              {{ $value }}
                          </option>
                      @endforeach

                  </select>

                  {{-- Draft required message --}}
                  @if (request()->has('draft_id') && empty($draftData['fire_extenguisher'] ?? ''))
                      <span class="text-danger">Required in draft</span>
                  @endif

                  {{-- Laravel validation --}}
                  @error('fire_extenguisher')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Tracker Installation Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Tracker Installation Date </strong>
                  <input type="date"
                         name="tracker_installation_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['tracker_installation_date'] ?? '') ? 'is-invalid' : '' }}
               @error('tracker_installation_date') is-invalid @enderror"
                         value="{{ $draftData['tracker_installation_date'] ?? old('tracker_installation_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['tracker_installation_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Message --}}
                  @error('tracker_installation_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Inspection Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Inspection Date</strong>
                  <input type="date"
                         name="inspection_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['inspection_date'] ?? '') ? 'is-invalid' : '' }}
               @error('inspection_date') is-invalid @enderror"
                         value="{{ $draftData['inspection_date'] ?? old('inspection_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['inspection_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('inspection_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror

              </div>
            </div>

            <!-- Next Inspection Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Next Inspection Date</strong>
                  <input type="date"
                         name="next_inspection_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['next_inspection_date'] ?? '') ? 'is-invalid' : '' }}
               @error('next_inspection_date') is-invalid @enderror"
                         value="{{ $draftData['next_inspection_date'] ?? old('next_inspection_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['next_inspection_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('next_inspection_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Induction Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Induction Date</strong>
                  <input type="date"
                         name="induction_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['induction_date'] ?? '') ? 'is-invalid' : '' }}
               @error('induction_date') is-invalid @enderror"
                         value="{{ $draftData['induction_date'] ?? old('induction_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['induction_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('induction_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Fitness Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Fitness Date</strong>
                  <input type="date"
                         name="fitness_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['fitness_date'] ?? '') ? 'is-invalid' : '' }}
               @error('fitness_date') is-invalid @enderror"
                         value="{{ $draftData['fitness_date'] ?? old('fitness_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['fitness_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('fitness_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Next Fitness Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Next fitness date</strong>
                  <input type="date"
                         name="next_fitness_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['next_fitness_date'] ?? '') ? 'is-invalid' : '' }}
               @error('next_fitness_date') is-invalid @enderror"
                         value="{{ $draftData['next_fitness_date'] ?? old('next_fitness_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['next_fitness_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('next_fitness_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
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
                @if(isset($draftData['file_info']['fitness_file']))
                  <div class="mt-2 d-flex align-items-center">
                    <a href="{{ route('admin.drafts.view', base64_encode($draftData['file_info']['fitness_file']['path'])) }}"
                       target="_blank" class="text-success mr-2" title="View">
                      <i class="icon-file"></i> {{ $draftData['file_info']['fitness_file']['original_name'] }}
                    </a>
                    <span class="text-muted mr-2">({{ number_format(($draftData['file_info']['fitness_file']['size'] ?? 0) / 1024, 1) }} KB)</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Remove" onclick="removeDraftFile('fitness_file', this)">×</button>
                  </div>
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
                @if(isset($draftData['file_info']['registration_file']))
                  <div class="mt-2 d-flex align-items-center">
                    <a href="{{ route('admin.drafts.view', base64_encode($draftData['file_info']['registration_file']['path'])) }}"
                       target="_blank" class="text-success mr-2" title="View">
                      <i class="icon-file"></i> {{ $draftData['file_info']['registration_file']['original_name'] }}
                    </a>
                    <span class="text-muted mr-2">({{ number_format(($draftData['file_info']['registration_file']['size'] ?? 0) / 1024, 1) }} KB)</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Remove" onclick="removeDraftFile('registration_file', this)">×</button>
                  </div>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Insurance Company -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Insurance Company</strong>
                  <select class="custom-select
                {{ request()->has('draft_id') && empty($draftData['insurance_company_id'] ?? '') ? 'is-invalid' : '' }}
                @error('insurance_company_id') is-invalid @enderror"
                          name="insurance_company_id">

                      <option value="">Select Insurance Company</option>

                      @foreach($insurance_companies as $key => $value)
                          <option value="{{ $value->id }}"
                              {{ ($draftData['insurance_company_id'] ?? old('insurance_company_id', $vehicle->insurance_company_id ?? '')) == $value->id ? 'selected' : '' }}>
                              {{ $value->name }}
                          </option>
                      @endforeach
                  </select>

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['insurance_company_id'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Error --}}
                  @error('insurance_company_id')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>
            <!-- Insurance Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Insurance Date</strong>
                  <input type="date"
                         name="insurance_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['insurance_date'] ?? '') ? 'is-invalid' : '' }}
               @error('insurance_date') is-invalid @enderror"
                         value="{{ $draftData['insurance_date'] ?? old('insurance_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['insurance_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('insurance_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Insurance Expiry Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Insurance Expiry Date</strong>
                  <input type="date"
                         name="insurance_expiry_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['insurance_expiry_date'] ?? '') ? 'is-invalid' : '' }}
               @error('insurance_expiry_date') is-invalid @enderror"
                         value="{{ $draftData['insurance_expiry_date'] ?? old('insurance_expiry_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['insurance_expiry_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  @error('insurance_expiry_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
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
                @if(isset($draftData['file_info']['insurance_file']))
                  <div class="mt-2 d-flex align-items-center">
                    <a href="{{ route('admin.drafts.view', base64_encode($draftData['file_info']['insurance_file']['path'])) }}"
                       target="_blank" class="text-success mr-2" title="View">
                      <i class="icon-file"></i> {{ $draftData['file_info']['insurance_file']['original_name'] }}
                    </a>
                    <span class="text-muted mr-2">({{ number_format(($draftData['file_info']['insurance_file']['size'] ?? 0) / 1024, 1) }} KB)</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Remove" onclick="removeDraftFile('insurance_file', this)">×</button>
                  </div>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Route Permit Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Route Permit Date</strong>
                  <input type="date"
                         name="route_permit_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['route_permit_date'] ?? '') ? 'is-invalid' : '' }}
               @error('route_permit_date') is-invalid @enderror"
                         value="{{ $draftData['route_permit_date'] ?? old('route_permit_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['route_permit_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('route_permit_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Route Permit Expiry Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Route Permit Expiry Date</strong>
                  <input type="date"
                         name="route_permit_expiry_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['route_permit_expiry_date'] ?? '') ? 'is-invalid' : '' }}
               @error('route_permit_expiry_date') is-invalid @enderror"
                         value="{{ $draftData['route_permit_expiry_date'] ?? old('route_permit_expiry_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['route_permit_expiry_date'] ?? ''))
                      <span class="text-danger">Required in draft</span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('route_permit_expiry_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
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
                @if(isset($draftData['file_info']['route_permit_file']))
                  <div class="mt-2 d-flex align-items-center">
                    <a href="{{ route('admin.drafts.view', base64_encode($draftData['file_info']['route_permit_file']['path'])) }}"
                       target="_blank" class="text-success mr-2" title="View">
                      <i class="icon-file"></i> {{ $draftData['file_info']['route_permit_file']['original_name'] }}
                    </a>
                    <span class="text-muted mr-2">({{ number_format(($draftData['file_info']['route_permit_file']['size'] ?? 0) / 1024, 1) }} KB)</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Remove" onclick="removeDraftFile('route_permit_file', this)">×</button>
                  </div>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Tax Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Tax Date</strong>
                  <input type="date"
                         name="tax_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['tax_date'] ?? '') ? 'is-invalid' : '' }}
               @error('tax_date') is-invalid @enderror"
                         value="{{ $draftData['tax_date'] ?? old('tax_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['tax_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Validation Error --}}
                  @error('tax_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
              </div>
            </div>

            <!-- Next Tax Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Next Tax Date</strong>
                  <input type="date"
                         name="next_tax_date"
                         class="form-control
               {{ request()->has('draft_id') && empty($draftData['next_tax_date'] ?? '') ? 'is-invalid' : '' }}
               @error('next_tax_date') is-invalid @enderror"
                         value="{{ $draftData['next_tax_date'] ?? old('next_tax_date') }}"
                  >

                  {{-- Draft Required Message --}}
                  @if (request()->has('draft_id') && empty($draftData['next_tax_date'] ?? ''))
                      <span class="text-danger"></span>
                  @endif

                  {{-- Laravel Error --}}
                  @error('next_tax_date')
                  <label class="text-danger">{{ $message }}</label>
                  @enderror
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
                @if(isset($draftData['file_info']['tax_file']))
                  <div class="mt-2 d-flex align-items-center">
                    <a href="{{ route('admin.drafts.view', base64_encode($draftData['file_info']['tax_file']['path'])) }}"
                       target="_blank" class="text-success mr-2" title="View">
                      <i class="icon-file"></i> {{ $draftData['file_info']['tax_file']['original_name'] }}
                    </a>
                    <span class="text-muted mr-2">({{ number_format(($draftData['file_info']['tax_file']['size'] ?? 0) / 1024, 1) }} KB)</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Remove" onclick="removeDraftFile('tax_file', this)">×</button>
                  </div>
                @endif
              </div>
            </div>

            <!-- Buttons -->
            <div class="col-md-12">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                  <i class="icon-save"></i> Draft
                </button>
                <button type="submit" class="btn btn-primary">
                  <i class="icon-check"></i> Save
                </button>
                <a href="{{ route('admin.vehicles.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const stationSelect = document.getElementById('station_id');
      const ibcCenterSelect = document.getElementById('ibc_center_id');

      function filterIBCCenters() {
        const selectedStation = stationSelect.value;
        const currentIBC = ibcCenterSelect.value;

        let matchFound = false;

        Array.from(ibcCenterSelect.options).forEach(option => {
          if (!option.value) return; // Keep default option
          const matches = option.getAttribute('data-station') === selectedStation;
          option.style.display = matches ? 'block' : 'none';

          if (matches && option.value === currentIBC) {
            matchFound = true;
          }
        });

        // If selected IBC doesn't match filtered options, reset
        if (!matchFound) {
          ibcCenterSelect.value = '';
        }
      }

      stationSelect.addEventListener('change', filterIBCCenters);
      filterIBCCenters(); // Initial filter (e.g. in edit mode)
    });
</script>

<script>
  function removeDraftFile(field, btn){
    if(!confirm('Remove attached file?')) return;
    var container = btn.closest('.form-group');
    var draftIdInput = document.querySelector('input[name="draft_id"]');
    if(!draftIdInput){ return; }
    var draftId = draftIdInput.value;

    fetch("{{ url('admin/drafts') }}/" + draftId + "/remove-file", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
      },
      body: JSON.stringify({ field })
    }).then(r => r.json()).then(res => {
      if(res && res.field === field){
        // Remove the attached preview row
        var previewRow = btn.closest('.d-flex');
        if(previewRow){ previewRow.remove(); }
      }
    }).catch(() => {});
  }
</script>


@endsection
