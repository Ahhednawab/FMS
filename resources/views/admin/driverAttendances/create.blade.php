@extends('layouts.admin')
@section('title', 'Add Driver Attendance')
@section('content')
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
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Driver Attendance
                        Management</span></h4>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('driverAttendances.index') }}" class="btn btn-primary">
                        <span>View Driver Attendance <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label"><strong>Driver Name</strong></label>
                            <select class="custom-select select2" id="driver_name_filter">
                                <option value="">All</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label"><strong>Driver Status</strong></label>
                            <select class="custom-select select2" id="driver_status_filter" multiple
                                data-placeholder="Select Driver Status">
                                @foreach ($driver_status as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label"><strong>Station</strong></label>
                            <select class="custom-select select2" id="station_filter" multiple
                                data-placeholder="Select Station">
                                @foreach ($stations as $station)
                                    <option value="{{ $station->id }}">{{ $station->area }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1 mt-4">
                        <div class="form-group">
                            <button type="button" id="reset-driver-filters" class="btn btn-primary">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('driverAttendances.store') }}" method="POST" enctype="multipart/form-data">
                    {{-- How many rows the browser submitted; the server compares this
                         against what actually arrived to detect a truncated POST. --}}
                    <input type="hidden" name="marked_row_count" id="marked_row_count" value="">
                    @csrf
                    <div class="row mb-3">
                        <!-- Date -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <strong>Date </strong>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                    name="date" value="{{ old('date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                                @error('date')
                                    <label class="text-danger">{{ $message }}</label>
                                @enderror
                            </div>
                        </div>
                        <!-- Bulk Actions -->
                        <div class="col-md-10">
                            <div class="form-group">
                                <div class="mb-2">
                                    <strong>Bulk Actions:</strong>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    @foreach ($driver_attendance_status as $id => $status)
                                        @continue(in_array(strtolower(trim($status)), ['replace', 'leave', 'leave remove'], true))
                                        <button type="button" class="btn btn-sm btn-outline-primary status-btn"
                                            data-status-id="{{ $id }}">
                                            {{ $status }}
                                        </button>
                                    @endforeach
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                    <label class="form-check-label font-weight-bold" for="selectAll">Select All
                                        Drivers<span id="selectAllCount"
                                            class="text-muted font-weight-normal"></span></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto pr-0 d-flex align-items-center mb-2">
                        <div class="form-check">

                        </div>
                        <div class="col-md-2">
                            <strong>Driver</strong>
                        </div>

                        <div class="col-md-2">

                            <strong>CNIC No</strong>
                        </div>
                        <div class="col-md-1">
                            <strong>Father Name</strong>
                        </div>
                        <div class="col-md-1">
                            <strong>Station</strong>
                        </div>
                        <div class="col-md-1">
                            <strong class="pl-1">Shift</strong>
                        </div>
                        <div class="col-md-1">
                            <strong class="pl-2">Status</strong>
                        </div>

                        <div class="col-md-2">
                            <strong class="pl-2">Attendance</strong>
                        </div>
                        <div class="col-md-2">
                            <strong class="pl-2">Pool Driver</strong>
                        </div>
                    </div>
                    @foreach ($drivers as $i => $driver)
                        <div class="row align-items-center mb-3 driver-attendance-row"
                            data-driver-id="{{ $driver->id }}"
                            data-driver-status-id="{{ $driver->driver_status_id }}"
                            data-station-id="{{ $driver->vehicle?->station_id ?? $driver->station_id ?? '' }}"
                            data-driver-name="{{ strtolower($driver->full_name) }}">
                            <!-- Checkbox -->
                            <div class="col-auto pr-0 d-flex align-items-center" style="margin-bottom: 29px;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input driver-checkbox"
                                        data-driver-idx="{{ $i }}" style="margin-top: 0;">
                                </div>
                            </div>
                            <input type="hidden" class="form-control" name="driver_id[{{ $i }}]" value="{{ $driver->id }}">
                            <!-- Driver Name -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="text" class="form-control"                                         value="{{ $driver->full_name }}{{ $driver->driver_type === 'pool' ? ' (Pool)' : ' (Regular)' }}"
                                        readonly>
                                </div>
                            </div>
                            <!-- CNIC -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="text" class="form-control"                                         value="{{ $driver->cnic_no }}" readonly>
                                </div>
                            </div>
                            <!-- Father Name -->
                            <div class="col-md-1">
                                <div class="form-group">
                                    <input type="text" class="form-control"                                         value="{{ $driver->father_name }}" readonly>
                                </div>
                            </div>
                            <!-- Station (drives the Station filter above) -->
                            <div class="col-md-1">
                                <div class="form-group">
                                    <input type="text" class="form-control"                                         value="{{ $driver->station_name }}" readonly>
                                </div>
                            </div>
                            <!-- Shift -->
                            <div class="col-md-1">
                                <div class="form-group">
                                    <input type="text" class="form-control"                                         value="{{ $driver->shiftTiming ? $driver->shiftTiming->name . ' (' . \Carbon\Carbon::parse($driver->shiftTiming->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($driver->shiftTiming->end_time)->format('h:i A') . ')' : 'N/A' }}"
                                        readonly>
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="col-md-1">
                                <div class="form-group">
                                    <input type="text" class="form-control"                                         value="{{ $driver->driverStatus->name ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                            <!-- Attendance -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select class="custom-select @error('status.' . $i) is-invalid @enderror"
                                        name="status[{{ $i }}]" data-driver-idx="{{ $i }}">
                                        <option value="">Select</option>
                                        @foreach ($driver_attendance_status as $statusKey => $statusLabel)
                                            @continue(in_array(strtolower(trim($statusLabel)), ['replace', 'leave', 'leave remove'], true))
                                            <option value="{{ $statusKey }}"
                                                {{ old('status.' . $i) == (string) $statusKey ? 'selected' : '' }}>
                                                {{ $statusLabel }}</option>
                                        @endforeach
                                    </select>
                                    @error("status.$i")
                                        <label class="text-danger">{{ $message }}</label>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2 replacement-driver-wrapper"
                                data-driver-idx="{{ $i }}"
                                data-replace-visible="{{ old('status.' . $i) == (string) $replaceStatusId ? '1' : '0' }}"
                                style="{{ old('status.' . $i) == (string) $replaceStatusId ? '' : 'display:none;' }}">
                                <div class="form-group">
                                    <select class="custom-select @error('replacement_driver_id.' . $i) is-invalid @enderror"
                                        name="replacement_driver_id[{{ $i }}]" data-driver-idx="{{ $i }}">
                                        <option value="">Select Pool Driver</option>
                                        @foreach (($poolDriverOptions[$driver->id] ?? collect()) as $poolDriverId => $poolDriverName)
                                            <option value="{{ $poolDriverId }}"
                                                {{ old('replacement_driver_id.' . $i) == (string) $poolDriverId ? 'selected' : '' }}>
                                                {{ $poolDriverName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("replacement_driver_id.$i")
                                        <label class="text-danger">{{ $message }}</label>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="sticky-action-bar">
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('driverAttendances.index') }}" class="btn btn-warning">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- Toast library used by the bulk-action feedback below. Without it the
         "select at least one driver" warning and the confirmation both threw,
         so bulk actions appeared to do nothing at all. --}}
    <script src="{{ asset('assets/js/plugins/notifications/noty.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            const replaceStatusId = @json($replaceStatusId);

            $('.select2').select2({
                theme: 'bootstrap4',
                allowClear: true,
                closeOnSelect: false,
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || '--Select--';
                }
            });

            // Checkboxes belonging to rows that survive the current filters.
            // Bulk actions must never reach a driver the user cannot see.
            function visibleDriverCheckboxes() {
                return $('.driver-attendance-row:visible').find('.driver-checkbox');
            }

            function syncSelectAllState() {
                const $visible = visibleDriverCheckboxes();
                const allChecked = $visible.length > 0 && $visible.filter(':not(:checked)').length === 0;
                $('#selectAll').prop('checked', allChecked);
            }

            // Changing a filter starts a fresh selection. Without this, ticks left
            // over from a previous filter kept "Select All" in the checked state, so
            // the user's next click de-selected the list instead of selecting it.
            function resetDriverSelection() {
                $('.driver-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
            }

            function applyDriverFilters() {
                const selectedDriver = $('#driver_name_filter').val();
                const selectedStatuses = ($('#driver_status_filter').val() || []).map(String);
                const selectedStations = ($('#station_filter').val() || []).map(String);

                $('.driver-attendance-row').each(function() {
                    const row = $(this);
                    const rowDriverId = String(row.data('driver-id'));
                    const rowStatusId = String(row.data('driver-status-id') || '');
                    const rowStationId = String(row.data('station-id') || '');
                    const matchesDriver = !selectedDriver || rowDriverId === String(selectedDriver);
                    const matchesStatus = !selectedStatuses.length || selectedStatuses.includes(rowStatusId);
                    const matchesStation = !selectedStations.length || selectedStations.includes(rowStationId);
                    const visible = matchesDriver && matchesStatus && matchesStation;

                    row.toggle(visible);
                });

                resetDriverSelection();

                const shown = $('.driver-attendance-row:visible').length;
                $('#selectAllCount').text(' (' + shown + ' driver' + (shown === 1 ? '' : 's') + ' shown)');
            }

            $('#driver_name_filter').on('change', applyDriverFilters);
            $('#driver_status_filter, #station_filter').on('change', applyDriverFilters);

            $('#reset-driver-filters').on('click', function() {
                $('#driver_name_filter').val('').trigger('change');
                $('#driver_status_filter').val(null).trigger('change');
                $('#station_filter').val(null).trigger('change');
            });

            function toggleReplacementDropdown(driverIdx, selectedStatusId) {
                const showReplacement = replaceStatusId !== null && String(selectedStatusId) === String(replaceStatusId);
                const $wrapper = $(`.replacement-driver-wrapper[data-driver-idx='${driverIdx}']`);
                const $select = $wrapper.find("select[name^='replacement_driver_id[']");

                if (showReplacement) {
                    $wrapper.show();
                } else {
                    $select.val('');
                    $wrapper.hide();
                }
            }

            // Handle status button clicks
            $('.status-btn').on('click', function() {
                const statusId = $(this).data('status-id');
                const statusName = $(this).text().trim();
                // Only drivers that are both selected and visible under the current
                // filters may be updated.
                const $checkedBoxes = visibleDriverCheckboxes().filter(':checked');
                if ($checkedBoxes.length === 0) {
                    // Show error if no drivers are selected
                    new Noty({
                        type: 'error',
                        text: 'Please select at least one driver',
                        timeout: 3000
                    }).show();
                    return;
                }
                // Update status for each selected driver
                $checkedBoxes.each(function() {
                    const driverIdx = $(this).data('driver-idx');
                    $(`select[data-driver-idx='${driverIdx}'][name^='status']`).val(statusId);
                    toggleReplacementDropdown(driverIdx, statusId);
                });
                // Show success message
                new Noty({
                    type: 'success',
                    text: `Updated attendance to ${statusName} for ${$checkedBoxes.length} driver(s)`,
                    timeout: 3000
                }).show();
            });
            // Select All functionality — limited to the drivers currently in view,
            // so selecting a Station and clicking it only picks that station's drivers.
            $('#selectAll').on('change', function() {
                visibleDriverCheckboxes().prop('checked', $(this).prop('checked'));
            });
            // Keep "Select All" in step with the visible rows only
            $('.driver-checkbox').on('change', syncSelectAllState);

            $("select[name^='status[']").on('change', function() {
                toggleReplacementDropdown($(this).data('driver-idx'), $(this).val());
            });

            $("select[name^='status[']").each(function() {
                toggleReplacementDropdown($(this).data('driver-idx'), $(this).val());
            });

            // Only submit rows that actually carry an attendance status.
            //
            // The sheet can list hundreds of drivers, and PHP silently discards
            // everything past max_input_vars (1000 by default) — which used to
            // truncate the POST and save attendance for only part of the
            // selection. Field names carry explicit indices, so dropping the
            // untouched rows here cannot shift the remaining ones.
            $('form').on('submit', function() {
                let marked = 0;

                $('.driver-attendance-row').each(function() {
                    const $row = $(this);
                    const $status = $row.find("select[name^='status[']");
                    const isMarked = !!$status.val();

                    $row.find("[name^='driver_id['], [name^='status['], [name^='replacement_driver_id[']")
                        .prop('disabled', !isMarked);

                    if (isMarked) marked++;
                });

                $('#marked_row_count').val(marked);
            });

            applyDriverFilters();
        });
    </script>
@endpush
