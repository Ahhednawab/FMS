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
                    <a href="{{ route('admin.dailyMileages.index') }}" class="btn btn-primary">
                        <span>View Daily Mileage <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.dailyMileages.create') }}" method="get">
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
                                <a href="{{ route('admin.dailyMileages.create') }}" class="btn btn-primary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.dailyMileages.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <strong>Report Date</strong>
                                <input type="date" class="form-control" name="report_date"
                                    value="{{ old('report_date', isset($draftData->data['report_date ']) ? $draftData->data['report_date '] : '') }}"
                                    max="{{ date('Y-m-d') }}">
                                @error('report_date')
                                    <label class="text-danger">{{ $message }}</label>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="station_id" value="{{ $selectedStation }}">
                    @foreach ($vehicleData as $key => $value)
                        <div class="row kilometer">
                            <input type="hidden" class="form-control" name="vehicle_id[]"
                                value="{{ $value['vehicle_id'] }}">

                            <!-- Station -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <strong>Station</strong>
                                    <input type="text" class="form-control" name="station[]"
                                        value="{{ old('station.' . $key, isset($draftData->data['station'][$key]) ? $draftData->data['station'][$key] : $value['station']) }}"
                                        readonly>
                                </div>
                            </div>

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
                                    <input type="number" min="0" step="1" class="form-control previous_km"
                                        name="previous_km[]"
                                        value="{{ old('previous_km.' . $key, isset($draftData->data['previous_kms'][$key]) ? $draftData->data['previous_kms'][$key] : $value['previous_km']) }}"
                                        readonly>
                                </div>
                            </div>

                            <!-- Current KMs -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <strong>Current KMs</strong>
                                    <input type="number" min="0" step="1"
                                        class="form-control current_km
            @if ($draftData && !isset($draftData->data['current_kms'][$key])) is-invalid @endif"
                                        name="current_km[{{ $key }}]"
                                        value="{{ old('current_km.' . $key, isset($draftData->data['current_kms'][$key]) ? $draftData->data['current_kms'][$key] : '') }}">

                                    @error('current_km.' . $key)
                                        <label class="text-danger">{{ $message }}</label>
                                    @enderror
                                </div>
                            </div>

                            <!-- Mileage -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <strong>Mileage</strong>
                                    {{--                                    <input type="number" min="0" step="1" class="form-control" name="mileage[]" value="{{ old('mileage.' . $key) }}" readonly> --}}

                                    <input type="number" min="0" step="1" class="form-control"
                                        name="mileage[]"
                                        value="{{ old('mileages.' . $key, isset($draftData->data['mileages'][$key]) ? $draftData->data['mileages'][$key] : '') }}"
                                        readonly>

                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="row">
                        <div class="col-md-12">
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('admin.dailyMileages.index') }}" class="btn btn-warning">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            function calculateMileage() {
                $('.kilometer').each(function() {
                    var previous_km = $(this).find('.previous_km').val() || 0;
                    var current_km = $(this).find('.current_km').val() || 0;
                    var mileage = current_km - previous_km;
                    if (mileage < 0) mileage = 0;
                    $(this).find('input[name="mileage[]"]').val(mileage.toFixed(0))
                });
            }

            $('.current_km').on('input', function() {
                calculateMileage();
            });
        });
    </script>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for Vehicle No filter
            $('#vehicle_no_filter').select2({
                theme: 'bootstrap4',
                placeholder: "Select Vehicle No",
                allowClear: true
            });

            function calculateMileage() {
                $('.kilometer').each(function() {
                    var previous_km = $(this).find('.previous_km').val() || 0;
                    var current_km = $(this).find('.current_km').val() || 0;
                    var mileage = current_km - previous_km;
                    if (mileage < 0) mileage = 0;
                    $(this).find('input[name="mileage[]"]').val(mileage.toFixed(0))
                });
            }

            $('.current_km').on('input', function() {
                calculateMileage();
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
        });
    </script>
@endpush
