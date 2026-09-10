@extends('layouts.admin')

@section('title', 'Add Daily Fuel')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />
    <style>
        .sticky-action-bar {
            position: sticky;
            bottom: 0;
            z-index: 1020;
            background: #fff;
            border-top: 1px solid #e5e5e5;
            box-shadow: 0 -6px 18px rgba(0, 0, 0, 0.08);
            margin-top: 1rem;
            padding: 0.875rem 0;
        }

        .select2-search--dropdown::after {
            content: '' !important;
            display: none !important;
            background: none !important;
        }

        /* Placeholder text for the multi-select filters (the bootstrap4 theme
           only styles it for single selects) */
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__placeholder {
            color: #6c757d;
            float: left;
            margin-top: 5px;
            margin-left: 6px;
            list-style: none;
        }

        /* Keep the field a comfortable height when empty */
        .select2-container--bootstrap4 .select2-selection--multiple {
            min-height: calc(1.5em + .75rem + 2px);
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
                                <select class="custom-select select2" name="station_id[]" id="station_id" multiple>
                                    @foreach ($stations as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ in_array((string) $key, $selectedStations ?? [], true) ? 'selected' : '' }}>
                                            {{ $value }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><strong>Vehicle No</strong></label>
                                <select class="custom-select select2" id="vehicle_no_filter" multiple>
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
                <div class="alert alert-info py-2 mb-3">
                    Enter Current KMs and Fuel Taken only for vehicles that took fuel. Vehicles left blank are saved
                    automatically with their Previous KMs carried forward (no fuel, no mileage).
                </div>

                <form action="{{ route('dailyFuels.store') }}" method="POST" id="fuelForm">
                    @csrf

                    <!-- Hidden report date field for form submission -->
                    <input type="hidden" name="report_date" id="report_date" value="{{ $selectedDate ?? date('Y-m-d') }}">
                    <!-- Row count lets the server detect a truncated submission (PHP max_input_vars) -->
                    <input type="hidden" name="row_count" id="row_count" value="{{ count($vehicleData) }}">

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
                                <div class="row kilometer" data-vehicle-id="{{ $value['vehicle_id'] }}"
                                    data-vehicle-no="{{ $value['vehicle_no'] }}"
                                    data-has-history="{{ !empty($value['has_history']) ? 1 : 0 }}">
                                    <input type="hidden" class="form-control vehicle_id_input" name="vehicle_id[{{ $globalIndex }}]"
                                        value="{{ $value['vehicle_id'] }}">

                                    <!-- Vehicle No -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <strong>Vehicle No</strong>
                                            <input type="text" class="form-control"
                                                value="{{ $value['vehicle_no'] }}" readonly tabindex="-1">
                                        </div>
                                    </div>

                                    <!-- Previous KMs -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <strong>Previous KMs</strong>
                                            <input type="number" min="0" step="1"
                                                class="form-control previous_km"
                                                value="{{ $value['previous_km'] }}" readonly tabindex="-1">
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
                                            @error('vehicle_id.' . $globalIndex)
                                                <label class="text-danger">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Mileage -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <strong>Mileage KM</strong>
                                            <input type="number" min="0" step="1" class="form-control mileage"
                                                readonly tabindex="-1">
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
                                            <input type="number" min="0" step="0.1" class="form-control fuel_average"
                                                readonly tabindex="-1">
                                        </div>
                                    </div>
                                </div>
                                @php $globalIndex++; @endphp
                            @endforeach
                        @endforeach
                    </div>

                    <div class="sticky-action-bar">
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                            <a href="{{ route('dailyFuels.index') }}" class="btn btn-warning">Cancel</a>
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
                $row.find('.mileage').val(mileage.toFixed(0));

                var fuel_taken = parseFloat($row.find('.fuel_taken').val()) || 0;
                var fuel_avg = fuel_taken > 0 ? (mileage / fuel_taken) : 0;
                $row.find('.fuel_average').val(fuel_avg.toFixed(1));
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

            // Initialize Select2 for filters (both are multi-select).
            // Stock multi-select moves the search box inline into the field;
            // compose the adapters explicitly so the search input stays inside
            // the dropdown (same UX as the old single-select) while selected
            // items render as chips in the field above. The dropdown stays
            // open while picking; click outside or press Esc to close it.
            $.fn.select2.amd.require([
                'select2/utils',
                'select2/selection/multiple',
                'select2/selection/placeholder',
                'select2/selection/allowClear',
                'select2/selection/eventRelay',
                'select2/dropdown',
                'select2/dropdown/search',
                'select2/dropdown/attachBody'
            ], function(Utils, MultipleSelection, Placeholder, AllowClear, EventRelay,
                Dropdown, DropdownSearch, AttachBody) {

                var SelectionAdapter = Utils.Decorate(MultipleSelection, Placeholder);
                SelectionAdapter = Utils.Decorate(SelectionAdapter, AllowClear);
                SelectionAdapter = Utils.Decorate(SelectionAdapter, EventRelay);

                var DropdownAdapter = Utils.Decorate(
                    Utils.Decorate(Dropdown, DropdownSearch),
                    AttachBody
                );

                function initFilter($el, placeholderText) {
                    $el.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: placeholderText,
                        allowClear: true,
                        selectionAdapter: SelectionAdapter,
                        dropdownAdapter: DropdownAdapter
                    });
                }

                initFilter($('#station_id'), 'All stations');
                initFilter($('#vehicle_no_filter'), 'All vehicles');
            });

            // Vehicle No filter logic (client-side show/hide, multi-select)
            $('#vehicle_no_filter').on('change', function() {
                var selectedVehicles = $(this).val() || [];

                $('.kilometer').each(function() {
                    var vehicleNo = $(this).data('vehicle-no');

                    if (selectedVehicles.length === 0 || selectedVehicles.indexOf(String(vehicleNo)) !== -1) {
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

            // Submit: validate only rows the user actually filled in.
            // Blank rows are fine — the server carries their Previous KMs forward.
            $('#fuelForm').on('submit', function(e) {
                var invalidCurrentKmVehicles = [];
                var missingFuelVehicles = [];
                var missingKmVehicles = [];
                var enteredCount = 0;
                var carriedCount = 0;

                $('.kilometer').each(function() {
                    var $row = $(this);
                    var vehicleNo = String($row.data('vehicle-no'));
                    var currentKm = $row.find('.current_km').val();
                    var fuelTaken = $row.find('.fuel_taken').val();

                    var hasCurr = currentKm !== '' && currentKm !== null;
                    var hasFuel = fuelTaken !== '' && fuelTaken !== null;

                    // Untouched row: nothing to validate (carried forward server-side)
                    if (!hasCurr && !hasFuel) {
                        return;
                    }

                    var previousKm = parseFloat($row.find('.previous_km').val()) || 0;
                    var currentKmNum = parseFloat(currentKm);

                    if (hasCurr && (isNaN(currentKmNum) || currentKmNum < previousKm)) {
                        invalidCurrentKmVehicles.push(vehicleNo);
                        return;
                    }
                    if (hasCurr && !hasFuel) {
                        missingFuelVehicles.push(vehicleNo);
                        return;
                    }
                    if (hasFuel && !hasCurr) {
                        missingKmVehicles.push(vehicleNo);
                        return;
                    }

                    enteredCount++;
                });

                if (invalidCurrentKmVehicles.length > 0) {
                    e.preventDefault();
                    alert('Current KMs must be greater than or equal to Previous KMs for: \n' +
                        invalidCurrentKmVehicles.join(', '));
                    return false;
                }
                if (missingFuelVehicles.length > 0) {
                    e.preventDefault();
                    alert('Fuel Taken is required when Current KMs is entered. Missing for: \n' +
                        missingFuelVehicles.join(', '));
                    return false;
                }
                if (missingKmVehicles.length > 0) {
                    e.preventDefault();
                    alert('Current KMs is required when Fuel Taken is entered. Missing for: \n' +
                        missingKmVehicles.join(', '));
                    return false;
                }

                // Rows hidden by the Vehicle No filter that were left EMPTY are
                // excluded from the save entirely (their inputs are disabled),
                // so filtering + saving only affects the vehicles on screen.
                // Hidden rows that were filled in before filtering still submit.
                $('.kilometer:hidden').each(function() {
                    var $row = $(this);
                    var hasData = $row.find('.current_km').val() || $row.find('.fuel_taken').val();
                    if (!hasData) {
                        $row.find('input').prop('disabled', true);
                    }
                });

                // Blank rows still submitting = carried forward (vehicles with
                // no fuel history yet are skipped by the server — not counted)
                $('.kilometer').each(function() {
                    var $row = $(this);
                    if ($row.find('.vehicle_id_input').prop('disabled')) return;
                    if (String($row.data('has-history')) !== '1') return;
                    var hasData = $row.find('.current_km').val() || $row.find('.fuel_taken').val();
                    if (!hasData) carriedCount++;
                });

                // Keep the server-side truncation guard in sync with the rows
                // that are actually being submitted.
                $('#row_count').val($('.kilometer').filter(function() {
                    return !$(this).find('.vehicle_id_input').prop('disabled');
                }).length);

                var summary = enteredCount + ' vehicle(s) entered, ' + carriedCount +
                    ' vehicle(s) will be saved with Previous KMs carried forward.\n\nContinue?';
                if (!confirm(summary)) {
                    e.preventDefault();
                    // Re-enable anything we disabled so the form stays editable
                    $('.kilometer input').prop('disabled', false);
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
