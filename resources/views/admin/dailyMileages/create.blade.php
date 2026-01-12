@extends('layouts.admin')

@section('title', 'Add Daily Mileage')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />

    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Mileage Management</span>
                </h4>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('dailyMileages.index') }}" class="btn btn-primary">
                        <span>View Daily Mileage <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dailyMileages.create') }}" method="get">
                    <div class="row">
                        <!-- Station Filter -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><strong>Station</strong></label>
                                <select class="custom-select select2" name="station_id" id="station_id">
                                    <option value="">ALL</option>
                                    @foreach ($stations as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ (string) $selectedStation === (string) $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
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
                                <a href="{{ route('dailyMileages.create') }}" class="btn btn-primary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('dailyMileages.store') }}" method="POST">
                    @csrf
                    {{-- Report Date --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label><strong>Report Date</strong></label>
                            <input type="date" name="report_date" id="report_date" class="form-control"
                                max="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    {{-- Vehicle Rows --}}
                    @foreach ($vehicleData as $value)
                        @php $vid = $value['vehicle_id']; @endphp

                        <div class="row kilometer border-bottom py-2">

                            {{-- Hidden Vehicle ID --}}
                            <input type="hidden" name="vehicles[{{ $vid }}][vehicle_id]"
                                value="{{ $vid }}">

                            {{-- Station --}}
                            <div class="col-md-3">
                                <label>Station</label>
                                <input type="text" class="form-control" value="{{ $value['station'] }}" readonly>
                            </div>

                            {{-- Vehicle No --}}
                            <div class="col-md-2">
                                <label>Vehicle No</label>
                                <input type="text" class="form-control" value="{{ $value['vehicle_no'] }}" readonly>
                            </div>

                            {{-- Previous KM --}}
                            <div class="col-md-2">
                                <label>Previous KM</label>
                                <input type="number" class="form-control previous_km"
                                    name="vehicles[{{ $vid }}][previous_km]" value="{{ $value['previous_km'] }}"
                                    readonly>
                            </div>

                            {{-- Current KM --}}
                            <div class="col-md-2">
                                <label>Current KM</label>
                                <input type="number" class="form-control current_km"
                                    name="vehicles[{{ $vid }}][current_km]" min="0">
                            </div>

                            {{-- Mileage --}}
                            <div class="col-md-2">
                                <label>Mileage</label>
                                <input type="number" class="form-control mileage"
                                    name="vehicles[{{ $vid }}][mileage]" readonly>
                            </div>

                        </div>
                    @endforeach

                    {{-- Buttons --}}
                    <div class="row mt-3">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('dailyMileages.index') }}" class="btn btn-warning">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


@endsection
@push('scripts')
    <script>
        function fillForm() {
            $('.kilometer').each(function() {
                let $row = $(this);

                let prevKm = parseInt($row.find('.previous_km').val(), 10) || 0;
                let $currentKmInput = $row.find('.current_km');

                // Only auto-fill if empty
                if ($currentKmInput.val() === '') {
                    $currentKmInput.val(prevKm + 1);
                }
            });
            $('.current_km').trigger('change');
        }
        let mileageMap = {};

        $(document).ready(function() {

            // ================= Mileage Calculation =================
            function calculateMileage(row) {
                let prevKm = parseInt(row.find('.previous_km').val(), 10) || 0;
                let currKm = parseInt(row.find('.current_km').val(), 10);

                if (isNaN(currKm)) {
                    row.find('.mileage').val('');
                    return;
                }

                let mileage = currKm - prevKm;
                row.find('.mileage').val(mileage >= 0 ? mileage : 0);
            }

            // When user types Current KM
            $('body').on('change', '.current_km', function() {
                calculateMileage($(this).closest('.kilometer'));
            });

            // ================= Vehicle Filter =================
            $('#vehicle_no_filter').select2({
                theme: 'bootstrap4',
                placeholder: "Select Vehicle No",
                allowClear: true
            });

            $('#vehicle_no_filter').on('change', function() {
                let selectedVehicle = $(this).val();

                $('.kilometer').each(function() {
                    let vehicleNo = $(this).find('input[readonly]').eq(1).val();

                    if (!selectedVehicle || vehicleNo === selectedVehicle) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // ================= Date Change AJAX =================
            $('#report_date').on('change', function() {

                let reportDate = $(this).val();
                if (!reportDate) return;

                $.ajax({
                    url: "{{ route('fetchDailyMilages') }}",
                    type: "GET",
                    data: {
                        report_date: reportDate
                    },
                    dataType: "json",
                    success: function(response) {

                        mileageMap = {};

                        if (!response.success) return;

                        response.data.forEach(item => {
                            mileageMap[item.vehicle_id] = item;
                        });

                        let $container = $('.kilometer').parent();
                        let filledRows = [];
                        let unfilledRows = [];

                        $('.kilometer').each(function() {
                            let row = $(this);
                            let vehicleId = row.find('input[name$="[vehicle_id]"]')
                            .val();

                            // Reset bootstrap classes
                            row.find('.current_km, .mileage')
                                .removeClass('border border-danger is-invalid');

                            if (mileageMap[vehicleId]) {
                                let data = mileageMap[vehicleId];

                                row.find('.previous_km').val(data.previous_km);
                                row.find('.current_km').val(data.current_km);
                                row.find('.mileage').val(data.mileage);

                                if (!data.current_km) {
                                    row.find('.current_km, .mileage')
                                        .addClass('border border-danger is-invalid');
                                    unfilledRows.push(row);
                                } else {
                                    filledRows.push(row);
                                }

                            } else {
                                row.find('.current_km').val('');
                                row.find('.mileage').val('');
                                row.find('.current_km, .mileage')
                                    .addClass('border border-danger is-invalid');
                                unfilledRows.push(row);
                            }
                        });

                        // Unfilled vehicles on top
                        $container.append(unfilledRows);
                        $container.append(filledRows);
                    },

                    error: function() {
                        alert('Failed to fetch mileage data');
                    }
                });
            });

        });
    </script>
@endpush
