@extends('layouts.admin')

@section('title', 'Add Daily Fuel')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />
    <style>
        .select2-search--dropdown::after {
            content: '' !important;
            display: none !important;
            background: none !important;
        }
    </style>

    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Fuel Management</span>
                </h4>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('dailyFuels.index') }}" class="btn btn-primary">
                        <span>View Daily Fuel <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dailyFuels.create') }}" method="get" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><strong>Report Date</strong></label>
                                <input type="date" class="form-control" name="report_date" id="filter_report_date"
                                    value="{{ $selectedDate ?? date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><strong>Station</strong></label>
                                <select class="custom-select select2" name="station_id" id="station_id">
                                    <option value="">ALL</option>
                                    @foreach ($stations as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ (string) $selectedStation === (string) $key ? 'selected' : '' }}>
                                            {{ $value }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><strong>Vehicle No</strong></label>
                                <select class="custom-select select2" id="vehicle_no_filter">
                                    <option value="">ALL</option>
                                    @foreach ($vehicleData as $vehicle)
                                        <option value="{{ $vehicle['vehicle_no'] }}">{{ $vehicle['vehicle_no'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mt-4">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('dailyFuels.create') }}" class="btn btn-primary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('dailyFuels.store') }}" method="POST">
                    @csrf

                    <!-- Hidden report date field for form submission -->
                    <input type="hidden" name="report_date" id="report_date" value="{{ $selectedDate ?? date('Y-m-d') }}">

                    @php
                        $groupedByStation = collect($vehicleData)->groupBy('station');
                        $globalIndex = 0;
                    @endphp

                    <div class="vehicle-container">
                        @foreach ($groupedByStation as $station => $vehicles)
                            <div class="row">
                                <!-- Station -->
                                <div class="col-md-12">
                                    <h5 class="mt-3 mb-2">{{ $station }}</h5>
                                    <hr>
                                </div>
                            </div>

                            @foreach ($vehicles as $value)
                                <div class="row kilometer" data-vehicle-id="{{ $value['vehicle_id'] }}">
                                    <input type="hidden" class="form-control" name="vehicle_id[]"
                                        value="{{ $value['vehicle_id'] }}">

                                    <!-- Vehicle No -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <strong>Vehicle No</strong>
                                            <input type="text" class="form-control" name="vehicle_no"
                                                value="{{ $value['vehicle_no'] }}" readonly>
                                        </div>
                                    </div>

                                    <!-- Previous KMs -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <strong>Previous KMs</strong>
                                            <input type="number" min="0" step="1"
                                                class="form-control previous_km" name="previous_km[]"
                                                value="{{ $value['previous_km'] }}" readonly>
                                        </div>
                                    </div>

                                    <!-- Current KMs -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <strong>Current KMs</strong>
                                            <input type="number" min="0" step="1"
                                                class="form-control current_km" name="current_km[{{ $globalIndex }}]"
                                                value="{{ old('current_km.' . $globalIndex) }}">
                                            @error('current_km.' . $globalIndex)
                                                <label class="text-danger">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Mileage -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <strong>Mileage KM</strong>
                                            <input type="number" min="0" step="1" class="form-control"
                                                name="mileage[]" value="{{ old('mileage.' . $globalIndex) }}" readonly>
                                        </div>
                                    </div>

                                    <!-- Fuel Taken -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <strong>Fuel Taken (Ltr.)</strong>
                                            <input type="number" min="0" step="0.1"
                                                class="form-control fuel_taken" name="fuel_taken[{{ $globalIndex }}]"
                                                value="{{ old('fuel_taken.' . $globalIndex) }}">
                                            @error('fuel_taken.' . $globalIndex)
                                                <label class="text-danger">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Fuel Avg. -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <strong>Fuel Avg. (KM/Ltr.)</strong>
                                            <input type="number" min="0" step="0.1" class="form-control"
                                                name="fuel_average[]" value="{{ old('fuel_average.' . $globalIndex) }}"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                                @php $globalIndex++; @endphp
                            @endforeach
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for=""></label>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                                <a href="{{ route('dailyFuels.index') }}" class="btn btn-warning">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            function recalcRow($row) {
                var previous_km = parseFloat($row.find('.previous_km').val()) || 0;
                var current_km = parseFloat($row.find('.current_km').val()) || 0;
                var mileage = current_km - previous_km;
                if (mileage < 0) mileage = 0;
                $row.find('input[name="mileage[]"]').val(mileage.toFixed(0));

                var fuel_taken = parseFloat($row.find('.fuel_taken').val()) || 0;
                var fuel_avg = fuel_taken > 0 ? (mileage / fuel_taken) : 0;
                $row.find('input[name="fuel_average[]"]').val(fuel_avg.toFixed(1));
            }

            // Initialize all rows on page load
            $('.kilometer').each(function() {
                recalcRow($(this));
            });

            // Recalculate when either current_km or fuel_taken changes (per row)
            $(document).on('input', '.current_km, .fuel_taken', function() {
                var $row = $(this).closest('.kilometer');
                recalcRow($row);
            });

            // Initialize Select2 for filters
            $('#station_id, #vehicle_no_filter').select2({
                theme: 'bootstrap4',
                placeholder: "Select option",
                allowClear: true
            });

            // Vehicle No filter logic
            $('#vehicle_no_filter').on('change', function() {
                var selectedVehicle = $(this).val();

                $('.kilometer').each(function() {
                    var vehicleNo = $(this).find('input[name="vehicle_no"]').val();

                    if (!selectedVehicle || vehicleNo === selectedVehicle) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Update hidden report date when filter date changes
            $('#filter_report_date').on('change', function() {
                $('#report_date').val($(this).val());
            });

            // AJAX: update previous km when report date changes (without page reload)
            $('#filter_report_date').on('change', function() {
                var selectedDate = $(this).val();

                if (!selectedDate) return;

                $.ajax({
                    url: "{{ route('dailyFuels.fetchPreviousKmByDate') }}",
                    type: 'GET',
                    data: {
                        report_date: selectedDate
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update each row with the new previous KM value
                            response.data.forEach(function(vehicle) {
                                var $row = $('.kilometer[data-vehicle-id="' + vehicle
                                    .vehicle_id + '"]');
                                if ($row.length) {
                                    $row.find('.previous_km').val(vehicle.previous_km);
                                    // Clear current_km and fuel_taken when date changes
                                    $row.find('.current_km').val('');
                                    $row.find('.fuel_taken').val('');
                                    recalcRow($row);
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log('AJAX Error:', xhr.responseText);
                    }
                });
            });

            // Form submit validation for duplicates
            $('form[action="{{ route('dailyFuels.store') }}"]').on('submit', function(e) {
                var vehicleNos = [];
                var duplicates = [];
                var invalidCurrentKmVehicles = [];

                $('.kilometer:visible').each(function() {
                    var vehicleNo = $(this).find('input[name="vehicle_no"]').val();
                    var currentKm = $(this).find('.current_km').val();
                    var fuelTaken = $(this).find('.fuel_taken').val();

                    // Check if currentKm is empty or less than or equal to previous km (optional)
                    var previousKm = parseFloat($(this).find('.previous_km').val()) || 0;
                    var currentKmNum = parseFloat(currentKm);

                    // Validate currentKm: must be a positive number greater than previous km
                    if (!currentKm || isNaN(currentKmNum) || currentKmNum <= previousKm) {
                        invalidCurrentKmVehicles.push(vehicleNo);
                    }

                    // Only check vehicles that have data entered for duplicate validation
                    if (currentKm || fuelTaken) {
                        if (vehicleNos.includes(vehicleNo)) {
                            duplicates.push(vehicleNo);
                        } else {
                            vehicleNos.push(vehicleNo);
                        }
                    }
                });

                if (invalidCurrentKmVehicles.length > 0) {
                    e.preventDefault();
                    alert(
                        'Please enter a valid Current KM greater than Previous KM for these vehicles before saving: \n' +
                        invalidCurrentKmVehicles.join(', ')
                    );
                    $('#saveBtn').prop('disabled', false).text('Save');
                    return false;
                }

                if (duplicates.length > 0) {
                    e.preventDefault();
                    alert('Duplicate vehicle entries found for: ' + duplicates.join(', ') +
                        '\nEach vehicle can only be entered once per date.');
                    $('#saveBtn').prop('disabled', false).text('Save');
                    return false;
                }

                $('#saveBtn')
                    .prop('disabled', true)
                    .text('Saving...');

                return true;
            });

        });
    </script>
@endsection
