@extends('layouts.admin')

@section('title', 'Add Warehouse Inventory')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Warehouse Inventory Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.inventoryWarehouses.index') }}" class="btn btn-primary">
            <span>View Warehouse Inventory <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.inventoryWarehouses.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
            <!-- Serial No -->
            <div class="col-md-3">
              <div class="form-group">
                <label><strong>Serial No</strong></label>
                <input value="{{$serial_no}}" name="serial_no" type="text" class="form-control" readonly>
              </div>
            </div>

            <!-- Warehouse Name -->
            <div class="col-md-3">
              <div class="form-group">
                <label><strong>Warehouse Name</strong></label>
                <input type="text" class="form-control" name="warehouse_name" value="{{ old('warehouse_name') }}">
                @if ($errors->has('warehouse_name'))
                  <label class="text-danger">{{ $errors->first('warehouse_name') }}</label>
                @endif
              </div>
            </div>

            <!-- City -->
            <div class="col-md-3">
              <div class="form-group">
                <label> <strong>City</strong></label>
                <select name="city_id" id="city_id" class="form-control">
                  <option value="">Select City</option>
                  @foreach($cities as $key => $value)
                    <option value="{{$key}}">{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('city_id'))
                  <label class="text-danger">{{ $errors->first('city_id') }}</label>
                @endif
              </div>
            </div>    

            <!-- Contact -->
            <div class="col-md-3">
              <div class="form-group">
                <label><strong>Contact</strong></label>
                <input type="text" class="form-control" name="contact" value="{{ old('contact') }}">
                @if ($errors->has('contact'))
                  <label class="text-danger">{{ $errors->first('contact') }}</label>
                @endif
              </div>
            </div>

            <!-- Wahehouse Manager -->
            <div class="col-md-3">
              <div class="form-group">
                <label><strong>Wahehouse Manager</strong></label>
                <select class="custom-select" name="warehouse_manager">
                  <option value="">--Select Manager--</option>
                  @foreach($warehouseManagers as $key => $value)
                    <option value="{{ $key }}" {{ old('warehouse_manager') == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
                @if ($errors->has('warehouse_manager'))
                  <label class="text-danger">{{ $errors->first('warehouse_manager') }}</label>
                @endif
              </div>
            </div>

            <!-- Wahehouse Type -->
            <div class="col-md-3">
              <div class="form-group">
                <label><strong>Wahehouse Type</strong></label>
                <select class="custom-select" name="warehouse_type">
                  <option value="">--Select Type--</option>
                  @foreach($warehouseTypes as $key => $value)
                    <option value="{{ $key }}" {{ old('warehouse_type') == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
                @if ($errors->has('warehouse_type'))
                  <label class="text-danger">{{ $errors->first('warehouse_type') }}</label>
                @endif
              </div>
            </div>

            <!-- Operating hours -->
            <div class="col-md-3">
              <div class="form-group">
                <label><strong>Operating hours</strong></label>
                <select class="custom-select" name="operating_hour">
                  <option value="">--Select--</option>
                  @foreach($operatingHours as $key => $value)
                    <option value="{{ $key }}" {{ old('operating_hour') == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
                @if ($errors->has('operating_hour'))
                  <label class="text-danger">{{ $errors->first('operating_hour') }}</label>
                @endif
              </div>
            </div>

            <!-- Handling Equipment -->
            <div class="col-md-3">
              <div class="form-group">
                <label><strong>Handling Equipment</strong></label>
                <select class="custom-select" name="handling_equipment">
                  <option value="">--Select--</option>
                  @foreach($handlingEquipments as $key => $value)
                    <option value="{{ $key }}" {{ old('handling_equipment') == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
                @if ($errors->has('handling_equipment'))
                  <label class="text-danger">{{ $errors->first('handling_equipment') }}</label>
                @endif
              </div>
            </div>

            <!-- Buttons -->
            <div class="col-md-12">
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

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const countrySelect = document.getElementById('country_id');
      const citySelect = document.getElementById('city_id');

      function filterCities() {
        const selectedCountry = countrySelect.value;
        [...citySelect.options].forEach(option => {
          if (!option.value) return; // keep default
          option.style.display = option.getAttribute('data-country') === selectedCountry ? 'block' : 'none';
        });
        citySelect.value = '';
      }

      countrySelect.addEventListener('change', filterCities);
      filterCities(); // run on load in case of edit
    });
  </script>
@endsection
