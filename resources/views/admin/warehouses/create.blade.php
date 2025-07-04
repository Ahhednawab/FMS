@extends('layouts.admin')

@section('title', 'Warehouse Management')

@section('content')
	<!-- Page header -->
	<div class="page-header page-header-light">
		<div class="page-header-content header-elements-lg-inline">
			<div class="page-title d-flex">
				<h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Warehouse Management</span></h4>
				<a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
			</div>
			<div class="header-elements d-none">
				<div class="d-flex justify-content-center">
					<a href="{{ route('admin.warehouses.index') }}" class="btn btn-primary">
						<span>View Warehouse <i class="icon-list ml-2"></i></span>
					</a>
				</div>
			</div>
		</div>
	</div>
	<!-- /page header -->

	<!-- Content area -->
	<div class="content">
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-body">
						<form action="{{ route('admin.warehouses.store') }}" method="POST">
							@csrf

							<div class="row">
								<!-- Serial No -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Serial No</strong>
                    <input type="text" name="serial_no" class="form-control" value="{{$serial_no}}" readonly>
                  </div>
                </div>

                <!-- warehouse name -->
                <div class="col-md-3">
									<div class="form-group">
										<strong>Warehouse Name</strong>
										<input type="text" name="name" class="form-control" value="{{ old('name') }}">
										@if ($errors->has('name'))
                      <label class="text-danger">{{ $errors->first('name') }}</label>
                    @endif
									</div>
								</div>

                <!-- Stations -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Stations</strong>
                    <select name="station_id" id="station_id" class="form-control">
                      <option value="">--Select--</option>
                      @foreach($stations as $key => $value)
                        <option value="{{ $key }}" {{ old('station_id') == $key ? 'selected' : '' }}>
                          {{ $value }}
                        </option>
                      @endforeach
                    </select>
                    @if ($errors->has('station_id'))
                      <label class="text-danger">{{ $errors->first('station_id') }}</label>
                    @endif
                  </div>
                </div>

								<!-- Warehouse Manager -->
                <div class="col-md-3">
									<div class="form-group">
										<strong>Warehouse Manager</strong>
										<select name="manager_id" id="manager_id" class="form-control">
                      <option value="">--Select--</option>
                      @foreach($managers as $key => $value)
                        <option value="{{ $key }}" {{ old('manager_id') == $key ? 'selected' : '' }}>
                          {{ $value }}
                        </option>
                      @endforeach
                    </select>
                    @if ($errors->has('manager_id'))
                      <label class="text-danger">{{ $errors->first('manager_id') }}</label>
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
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-warning">Cancel</a>
                  </div>
                </div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /content area -->

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
