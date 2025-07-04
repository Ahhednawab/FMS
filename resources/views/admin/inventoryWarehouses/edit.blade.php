@extends('layouts.admin')

@section('title', 'Edit Warehouse Inventory')

@section('content')

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Warehouse Inventory</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.inventoryWarehouses.index') }}" class="btn btn-primary">
            <span>View Warehouse Inventries <i class="icon-list ml-2"></i></span>
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
            <form method="POST" action="{{ route('admin.inventoryWarehouses.update', $inventoryWarehouse->id) }}">
              @csrf
              @method('PUT')

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Warehouse Code</label>
                    <input value="{{$inventoryWarehouse->serial_no}}" name="serial_no" type="text" class="form-control" readonly>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Warehouse Name</label>
                    <input type="text" class="form-control" name="warehouse_name" value="{{ old('warehouse_name', $inventoryWarehouse->warehouse_name ?? '') }}">
                    @if ($errors->has('warehouse_name'))
                      <label class="text-danger">{{ $errors->first('warehouse_name') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="country">Country</label>
                    <select name="country_id" id="country_id" class="form-control">
                      <option value="">Select Country</option>
                      @foreach($countries as $country)
                        <option value="{{ $country->id }}"
                          {{ old('country_id', $inventoryWarehouse->country_id ?? '') == $country->id ? 'selected' : '' }}>
                          {{ $country->name }}
                        </option>
                      @endforeach
                    </select>
                    @if ($errors->has('country_id'))
                      <label class="text-danger">{{ $errors->first('country_id') }}</label>
                    @endif
                  </div>
                </div>    

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="city">City</label>
                    <select name="city_id" id="city_id" class="form-control"
                      data-selected="{{ old('city_id') ?? $inventoryWarehouse->city_id }}">
                      <option value="">Select City</option>
                      @foreach($countries as $country)
                        @foreach($country->cities as $city)
                          <option value="{{ $city->id }}"
                            data-country="{{ $country->id }}">
                            {{ $city->name }}
                          </option>
                        @endforeach
                      @endforeach
                    </select>
                    @if ($errors->has('city_id'))
                      <label class="text-danger">{{ $errors->first('city_id') }}</label>
                    @endif
                  </div>
                </div>      
              </div>

              <div class="row">
                

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Contact</label>
                    <input type="text" class="form-control" name="contact" value="{{ old('contact', $inventoryWarehouse->contact ?? '') }}">
                    @if ($errors->has('contact'))
                      <label class="text-danger">{{ $errors->first('contact') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Wahehouse Manager</label>
                    <select class="custom-select" name="warehouse_manager">
                      <option value="">--Select Manager--</option>
                      @foreach($warehouseManagers as $key => $value)
                        <option value="{{ $key }}" {{ old('warehouse_manager', $inventoryWarehouse->warehouse_manager ?? '') == $key ? 'selected' : '' }}>
                          {{ $value }}
                        </option>
                      @endforeach
                    </select>
                    @if ($errors->has('warehouse_manager'))
                      <label class="text-danger">{{ $errors->first('warehouse_manager') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Wahehouse Type</label>
                    <select class="custom-select" name="warehouse_type">
                      <option value="">--Select Type--</option>
                      @foreach($warehouseTypes as $key => $value)
                        <option value="{{ $key }}" {{ old('warehouse_type', $inventoryWarehouse->warehouse_type ?? '') == $key ? 'selected' : '' }}>
                          {{ $value }}
                        </option>
                      @endforeach
                    </select>
                    @if ($errors->has('warehouse_type'))
                      <label class="text-danger">{{ $errors->first('warehouse_type') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Operating hours</label>
                    <select class="custom-select" name="operating_hour">
                      <option value="">--Select--</option>
                      @foreach($operatingHours as $key => $value)
                        <option value="{{ $key }}" {{ old('operating_hour', $inventoryWarehouse->operating_hour ?? '') == $key ? 'selected' : '' }}>
                          {{ $value }}
                        </option>
                      @endforeach
                    </select>
                    @if ($errors->has('operating_hour'))
                      <label class="text-danger">{{ $errors->first('operating_hour') }}</label>
                    @endif
                  </div>
                </div>
              </div>

              <div class="row">
                

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Handling Equipment</label>
                    <select class="custom-select" name="handling_equipment">
                      <option value="">--Select--</option>
                      @foreach($handlingEquipments as $key => $value)
                        <option value="{{ $key }}" {{ old('handling_equipment', $inventoryWarehouse->handling_equipment ?? '') == $key ? 'selected' : '' }}>
                          {{ $value }}
                        </option>
                      @endforeach
                    </select>
                    @if ($errors->has('handling_equipment'))
                      <label class="text-danger">{{ $errors->first('handling_equipment') }}</label>
                    @endif
                  </div>
                </div>

                <div class="col-md-9">
                  <label for=""></label>
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('admin.inventoryWarehouses.index') }}" class="btn btn-warning">Cancel</a>
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
    document.addEventListener('DOMContentLoaded', function () {
      const countrySelect = document.getElementById('country_id');
      const citySelect = document.getElementById('city_id');

      function filterCities() {
        const selectedCountry = countrySelect.value;
        const selectedCity = citySelect.getAttribute('data-selected');

        let found = false;

        [...citySelect.options].forEach(option => {
          if (!option.value) return; // Skip default
          const matchesCountry = option.getAttribute('data-country') === selectedCountry;
          option.style.display = matchesCountry ? 'block' : 'none';

          if (matchesCountry && option.value === selectedCity) {
            found = true;
          }
        });

        // Set city value if valid
        if (found) {
          citySelect.value = selectedCity;
        } else {
          citySelect.value = '';
        }
      }

      countrySelect.addEventListener('change', function () {
        // Clear selected city on country change
        citySelect.setAttribute('data-selected', '');
        filterCities();
      });

      filterCities(); // Initial run on load (edit mode)
    });
  </script>
@endsection
