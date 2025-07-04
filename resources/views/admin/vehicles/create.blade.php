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
                <input type="text" class="form-control" name="vehicle_no" value="{{ old('vehicle_no') }}">
                @if ($errors->has('vehicle_no'))
                  <label class="text-danger">{{ $errors->first('vehicle_no') }}</label>
                @endif
              </div>
            </div>

            <!-- Make (Manufacturer) -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Make (Manufacturer)</strong>
                <input type="text" class="form-control" name="make" value="{{ old('make') }}">
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
                    <option value="{{ $year }}" {{ old('model') == $year ? 'selected' : '' }}>{{ $year }}</option>
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
                <input type="text" class="form-control" name="chasis_no" value="{{ old('chasis_no') }}">
                @if ($errors->has('chasis_no'))
                  <label class="text-danger">{{ $errors->first('chasis_no') }}</label>
                @endif 
              </div>
            </div>

            <!-- Engine No -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Engine No</strong>
                <input type="text" class="form-control" name="engine_no" value="{{ old('engine_no') }}">
                @if ($errors->has('engine_no'))
                  <label class="text-danger">{{ $errors->first('engine_no') }}</label>
                @endif 
              </div>
            </div>

            <!-- Ownership -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Ownership</strong>
                <input type="text" class="form-control" name="ownership" value="{{ old('ownership') }}">
                @if ($errors->has('ownership'))
                  <label class="text-danger">{{ $errors->first('ownership') }}</label>
                @endif
              </div>
            </div>

            <!-- Cone -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Cone</strong>
                <input type="number" min="0" step="1" class="form-control" name="cone" value="{{ old('cone') }}">
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
                <input type="text" class="form-control" name="pso_card" value="{{ old('pso_card') }}">
                @if ($errors->has('pso_card'))
                  <label class="text-danger">{{ $errors->first('pso_card') }}</label>
                @endif 
              </div>
            </div>

            <!-- AKPL -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>AKPL</strong>
                <input type="text" class="form-control" name="akpl" value="{{ old('akpl') }}">
                @if ($errors->has('akpl'))
                  <label class="text-danger">{{ $errors->first('akpl') }}</label>
                @endif 
              </div>
            </div>

            <!-- Vehicle Type -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Vehicle Type</strong>
                <select class="custom-select" name="vehicle_type_id">
                  <option value="">-- Select Vehicle Type --</option>
                  @foreach($vehicleTypes as $key => $value)
                    <option value="{{ $key }}" {{ old('vehicle_type_id') == $key ? 'selected' : '' }}>{{ $value }}</option>
                  @endforeach
                </select>
                @if ($errors->has('vehicle_type_id'))
                  <label class="text-danger">{{ $errors->first('vehicle_type_id') }}</label>
                @endif
              </div>
            </div>

            <!-- Ladder Make -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Ladder Maker</strong>
                <select class="custom-select" name="ladder_maker_id">
                  <option value="">-- Select --</option>
                  @foreach($ladder_maker as $key => $value)
                    <option value="{{$key}}" {{ old('ladder_maker_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('ladder_maker_id'))
                  <label class="text-danger">{{ $errors->first('ladder_maker_id') }}</label>
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
                  @foreach($stations as $station)
                    <option value="{{ $station->id }}" {{ old('station_id') == $station->id ? 'selected' : '' }}>
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
                  @foreach($stations as $station)
                    @foreach($station->ibcCenter ?? [] as $center)
                      <option value="{{ $center->id }}"
                        data-station="{{ $station->id }}"
                        {{ old('ibc_center_id') == $center->id ? 'selected' : '' }}>
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
            <div class="col-md-2">
              <div class="form-group">
                <strong>Medical Box</strong>
                <select class="custom-select" name="medical_box">
                  <option value="">-- Select --</option>
                  @foreach($status as $key => $value)
                    <option value="{{$key}}" {{ old('medical_box') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('medical_box'))
                  <label class="text-danger">{{ $errors->first('medical_box') }}</label>
                @endif
              </div>
            </div>

            <!-- Seat Cover -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Seat Cover</strong>
                <select class="custom-select" name="seat_cover">
                  <option value="">-- Select --</option>
                  @foreach($status as $key => $value)
                    <option value="{{$key}}" {{ old('seat_cover') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('seat_cover'))
                  <label class="text-danger">{{ $errors->first('seat_cover') }}</label>
                @endif
              </div>
            </div>

            <!-- Fire Extinguisher -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Fire Extinguisher</strong>
                <select class="custom-select" name="fire_extenguisher">
                  <option value="">-- Select --</option>
                  @foreach($status as $key => $value)
                    <option value="{{$key}}" {{ old('fire_extenguisher') == $key ? 'selected' : '' }}>{{$value}}</option>
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
                <input type="date" class="form-control" name="tracker_installation_date" value="{{ old('tracker_installation_date') }}">
                @if ($errors->has('tracker_installation_date'))
                  <label class="text-danger">{{ $errors->first('tracker_installation_date') }}</label>
                @endif
              </div>
            </div>

            <!-- Inspection Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Inspection Date</strong>
                <input type="date" class="form-control" name="inspection_date" value="{{ old('inspection_date') }}">
                @if ($errors->has('inspection_date'))
                  <label class="text-danger">{{ $errors->first('inspection_date') }}</label>
                @endif

              </div>
            </div>

            <!-- Next Inspection Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Next Inspection Date</strong>
                <input type="date" class="form-control" name="next_inspection_date" value="{{ old('next_inspection_date') }}">
                @if ($errors->has('next_inspection_date'))
                  <label class="text-danger">{{ $errors->first('next_inspection_date') }}</label>
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
            <!-- Fitness Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Fitness Date</strong>
                <input type="date" class="form-control" name="fitness_date" value="{{ old('fitness_date') }}">
                @if ($errors->has('fitness_date'))
                  <label class="text-danger">{{ $errors->first('fitness_date') }}</label>
                @endif 
              </div>
            </div>

            <!-- Next Fitness Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Next fitness date</strong>
                <input type="date" class="form-control" name="next_fitness_date" value="{{ old('next_fitness_date') }}">
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
          </div>

          <div class="row">
            <!-- Insurance Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Insurance Date</strong>
                <input type="date" class="form-control" name="insurance_date" value="{{ old('insurance_date') }}">
                @if ($errors->has('insurance_date'))
                  <label class="text-danger">{{ $errors->first('insurance_date') }}</label>
                @endif
              </div>
            </div>

            <!-- Insurance Expiry Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Insurance Expiry Date</strong>
                <input type="date" class="form-control" name="insurance_expiry_date" value="{{ old('insurance_expiry_date') }}">
                @if ($errors->has('insurance_expiry_date'))
                  <label class="text-danger">{{ $errors->first('insurance_expiry_date') }}</label>
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
                <input type="date" class="form-control" name="route_permit_date" value="{{ old('route_permit_date') }}">
                @if ($errors->has('route_permit_date'))
                  <label class="text-danger">{{ $errors->first('route_permit_date') }}</label>
                @endif 
              </div>
            </div>

            <!-- Route Permit Expiry Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Route Permit Expiry Date</strong>
                <input type="date" class="form-control" name="route_permit_expiry_date" value="{{ old('route_permit_expiry_date') }}">
                @if ($errors->has('route_permit_expiry_date'))
                  <label class="text-danger">{{ $errors->first('route_permit_expiry_date') }}</label>
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
                <input type="date" class="form-control" name="tax_date" value="{{ old('tax_date') }}">
                @if ($errors->has('tax_date'))
                  <label class="text-danger">{{ $errors->first('tax_date') }}</label>
                @endif 
              </div>
            </div>

            <!-- Next Tax Date -->
            <div class="col-md-3">
              <div class="form-group">
                <strong>Next Tax Date</strong>
                <input type="date" class="form-control" name="next_tax_date" value="{{ old('next_tax_date') }}">
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
                <button type="submit" class="btn btn-primary">Save</button>
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


@endsection
