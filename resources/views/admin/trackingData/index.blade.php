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

                <form action="{{ route('trackingData.index') }}" method="GET" id="tracking_report_form">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label><strong>Month</strong></label>
                            <input type="month" name="month" id="tracking_month_filter" class="form-control"
                                value="{{ $selectedMonth ?? now()->format('Y-m') }}"
                                max="{{ now()->format('Y-m') }}">
                        </div>
                        <div class="col-md-3" id="tracking_date_filter_group"
                            style="{{ !empty($isMonthlyMode) ? 'display: none;' : '' }}">
                            <label><strong>Date</strong></label>
                            <input type="date" name="report_date" id="tracking_date_filter" class="form-control" value="{{ $reportDate }}"
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
                            <button type="submit" formaction="{{ route('trackingData.monthly.export') }}"
                                class="btn btn-success">
                                Export Excel
                            </button>
                            <a href="{{ route('trackingData.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
                <div id="tracking_month_progress" class="alert alert-info mt-3 mb-0" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong id="tracking_month_progress_text">Preparing monthly sync...</strong>
                        <span id="tracking_month_progress_count"></span>
                    </div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div id="tracking_month_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 0%;"></div>
                    </div>
                </div>
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
            const currentMonth = "{{ now()->format('Y-m') }}";

            $('#tracking_vehicle_filter').select2({
                placeholder: 'Select vehicle(s)',
                closeOnSelect: false,
                width: '100%'
            });

            function isMonthlyMode() {
                return String($('#tracking_month_filter').val() || '') < currentMonth;
            }

            function toggleDateFilter() {
                $('#tracking_date_filter_group').toggle(!isMonthlyMode());
            }

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
                updateTrackingTotals();
            }

            function updateTrackingTotals() {
                const totals = {
                    offPeak: 0,
                    misPeak: 0,
                    ams: 0,
                    parking: 0,
                    totalKms: 0,
                    odoKms: 0,
                    diff: 0
                };

                $('.tracking-data-row:visible').each(function() {
                    totals.offPeak += Number($(this).data('off-peak')) || 0;
                    totals.misPeak += Number($(this).data('mis-peak')) || 0;
                    totals.ams += Number($(this).data('ams')) || 0;
                    totals.parking += Number($(this).data('parking')) || 0;
                    totals.totalKms += Number($(this).data('total-kms')) || 0;
                    totals.odoKms += Number($(this).data('odo-kms')) || 0;
                    totals.diff += Number($(this).data('diff')) || 0;
                });

                $('#tracking-total-off-peak').text(totals.offPeak.toFixed(1));
                $('#tracking-total-mis-peak').text(totals.misPeak.toFixed(1));
                $('#tracking-total-ams').text(totals.ams.toFixed(1));
                $('#tracking-total-parking').text(totals.parking.toFixed(1));
                $('#tracking-total-total-kms').text(totals.totalKms.toFixed(1));
                $('#tracking-total-odo-kms').text(totals.odoKms.toFixed(1));
                $('#tracking-total-diff').text(totals.diff.toFixed(1));
            }

            function setProgress(index, total, date, skipped) {
                const percent = total > 0 ? Math.round((index / total) * 100) : 100;
                const action = skipped ? 'Already cached' : 'Processing';
                $('#tracking_month_progress').show();
                $('#tracking_month_progress_text').text(`${action} date ${index} of ${total}: ${date}`);
                $('#tracking_month_progress_count').text(`${percent}%`);
                $('#tracking_month_progress_bar').css('width', `${percent}%`);
            }

            function delay(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            async function syncMonthlyData(month) {
                $('#tracking_month_progress').removeClass('alert-danger').addClass('alert-info').show();
                $('#tracking_month_progress_text').text('Checking cached dates...');
                $('#tracking_month_progress_count').text('');
                $('#tracking_month_progress_bar').addClass('progress-bar-animated').css('width', '0%');

                const datesUrl = "{{ route('trackingData.monthly.dates') }}" + '?month=' + encodeURIComponent(month);
                const datesResponse = await fetch(datesUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!datesResponse.ok) {
                    throw new Error('Unable to prepare monthly dates.');
                }

                const payload = await datesResponse.json();
                const dates = payload.dates || [];

                for (let index = 0; index < dates.length; index++) {
                    const current = dates[index];
                    setProgress(index + 1, dates.length, current.date, current.exists);

                    if (!current.exists) {
                        const syncResponse = await fetch("{{ route('trackingData.monthly.syncDate') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                report_date: current.date
                            })
                        });

                        if (!syncResponse.ok) {
                            throw new Error(`Unable to sync ${current.date}.`);
                        }

                        await delay(350);
                    }
                }

                $('#tracking_month_progress_text').text('Monthly synchronization completed.');
                $('#tracking_month_progress_count').text('100%');
                $('#tracking_month_progress_bar').css('width', '100%');
            }

            $('#tracking_report_form').on('submit', async function(event) {
                const submitter = event.originalEvent?.submitter;
                if (submitter && submitter.getAttribute('formaction')) {
                    return;
                }

                if (!isMonthlyMode()) {
                    return;
                }

                event.preventDefault();
                const form = this;
                const month = $('#tracking_month_filter').val();

                try {
                    await syncMonthlyData(month);
                    form.submit();
                } catch (error) {
                    $('#tracking_month_progress').removeClass('alert-info').addClass('alert-danger').show();
                    $('#tracking_month_progress_text').text(error.message || 'Monthly synchronization failed.');
                    $('#tracking_month_progress_count').text('');
                    $('#tracking_month_progress_bar').removeClass('progress-bar-animated').css('width', '100%');
                }
            });

            $('#tracking_month_filter').on('change', toggleDateFilter);
            $('#tracking_vehicle_filter').on('change', applyTrackingVehicleFilter);
            toggleDateFilter();
            applyTrackingVehicleFilter();
        });
    </script>
@endpush
