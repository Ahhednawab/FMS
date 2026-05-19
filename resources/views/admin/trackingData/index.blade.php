@extends('layouts.admin')

@section('title', 'Daily Mileage Report')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Mileage Report</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
        </div>
    </div>

    <div class="content">
        @include('admin.dailyMileages.partials.module-tabs')

        <div class="card">
            <div class="card-body">
                @if (!empty($apiError))
                    <div class="alert alert-danger mb-3">
                        {{ $apiError }}
                    </div>
                @endif

                <form action="{{ route('trackingData.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label><strong>Date</strong></label>
                            <input type="date" name="report_date" class="form-control" value="{{ $reportDate }}"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <label><strong>Vehicle No</strong></label>
                            <select name="vehicle_no[]" class="form-control select2" id="tracking_vehicle_filter" multiple>
                                @foreach ($vehicles as $vehicle)
                                    @php $vehicleNo = strtoupper(trim($vehicle->vehicle_no)); @endphp
                                    <option value="{{ $vehicleNo }}"
                                        {{ collect($selectedVehicles)->contains($vehicleNo) ? 'selected' : '' }}>
                                        {{ $vehicle->vehicle_no }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Load Report</button>
                            <a href="{{ route('trackingData.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @include('admin.trackingData.partials.results', [
            'trackingData' => $trackingData,
        ])
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#tracking_vehicle_filter').select2({
                placeholder: 'Select vehicle(s)',
                closeOnSelect: false,
                width: '100%'
            });

            function applyTrackingVehicleFilter() {
                const selectedVehicles = ($('#tracking_vehicle_filter').val() || []).map(function(vehicleNo) {
                    return String(vehicleNo).trim().toUpperCase();
                });

                let visibleRows = 0;

                $('.tracking-data-row').each(function() {
                    const rowVehicle = String($(this).data('vehicle') || '').trim().toUpperCase();
                    const vehicleMatch = selectedVehicles.length === 0 || selectedVehicles.includes(rowVehicle);

                    $(this).toggle(vehicleMatch);

                    if (vehicleMatch) {
                        visibleRows++;
                    }
                });

                $('#tracking-data-filter-empty').toggle(visibleRows === 0);
                $('#tracking-data-original-empty').toggle($('.tracking-data-row').length === 0);
            }

            $('#tracking_vehicle_filter').on('change', applyTrackingVehicleFilter);
            applyTrackingVehicleFilter();
        });
    </script>
@endpush
