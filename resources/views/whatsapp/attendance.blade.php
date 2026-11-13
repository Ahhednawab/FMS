@extends('layouts.admin')

@section('title', 'WhatsApp Attendance')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-bubble-lines4 mr-2"></i>
                    <span class="font-weight-semibold">WhatsApp Attendance</span>
                </h4>
            </div>
        </div>
    </div>

    <div class="content">
        @if (session('success'))
            <div id="alert-message" class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-warning">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="alert alert-info">
            <strong>Nothing here is attendance yet.</strong>
            These are the lines supervisors sent on WhatsApp. Check each one, fix
            anything that is wrong, then confirm - only then is attendance saved.
        </div>

        <div class="card mb-3 wa-filter-card">
            <div class="card-body">
                <form method="GET" action="{{ route('whatsappAttendance.index') }}">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label><strong>State</strong></label>
                            <select name="state" class="form-control select2">
                                @foreach ($states as $key => $label)
                                    <option value="{{ $key }}" {{ $state === $key ? 'selected' : '' }}>
                                        {{ $label }} ({{ $counts[$key] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label><strong>Attendance Date</strong></label>
                            <input type="date" name="date" value="{{ $date }}" class="form-control">
                        </div>

                        <div class="col-md-3 form-group">
                            <label><strong>Supervisor</strong></label>
                            <select name="sender_id" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($senderOptions as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ (string) $senderId === (string) $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-1 form-group">
                            <label><strong>Show</strong></label>
                            <select name="per_page" class="form-control">
                                @foreach ([25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 form-group d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">Filter</button>
                            <a href="{{ route('whatsappAttendance.index') }}" class="btn btn-light">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <form method="POST" id="draftForm">
            @csrf
            <input type="hidden" name="rendered_row_count" value="{{ $drafts->count() }}">

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 font-weight-semibold">{{ $states[$state] }}</h6>
                        <span class="text-muted">{{ number_format($drafts->total()) }} line(s)</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped wa-draft-table">
                            <thead>
                                <tr>
                                    <th style="width: 34px;" class="text-center">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th style="width: 13%;">Supervisor</th>
                                    <th style="width: 10%;">Date</th>
                                    <th style="width: 12%;">What was sent</th>
                                    <th style="width: 9%;">Vehicle</th>
                                    <th style="width: 16%;">Driver</th>
                                    <th style="width: 12%;">Status</th>
                                    {{-- The notes carry the whole reason a row is flagged, so this
                                         column gets a floor the others cannot squeeze it below. --}}
                                    <th style="min-width: 200px;">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($drafts as $draft)
                                    @php
                                        $isPending = $draft->state === 'pending';
                                        $vehicleId = (int) $draft->vehicle_id;
                                        $drivers = $driverChoices[$vehicleId] ?? [];
                                        $existingKey = $draft->driver_id . '|' . ($draft->attendance_date?->toDateString() ?? '');
                                        $existing = $existingAttendance[$existingKey] ?? null;
                                        $blocked = $draft->confirmBlockedReason();
                                    @endphp
                                    <tr class="{{ $draft->error && $isPending ? 'table-warning' : '' }}">
                                        <td class="text-center">
                                            @if ($isPending)
                                                <input type="checkbox" name="draft_ids[]" value="{{ $draft->id }}"
                                                    class="draft-check">
                                            @endif
                                        </td>

                                        <td>
                                            {{ $draft->message->sender->name ?? ($draft->message->wa_profile_name ?? 'Unknown') }}
                                            <div class="text-muted" style="font-size: 11px;">
                                                {{ $draft->message->from_number }}<br>
                                                {{ $draft->message->received_at?->format('d-m-Y H:i') }}
                                            </div>
                                        </td>

                                        <td>
                                            @if ($isPending)
                                                <input type="date" name="attendance_date[{{ $draft->id }}]"
                                                    value="{{ $draft->attendance_date?->toDateString() }}"
                                                    class="form-control form-control-sm">
                                            @else
                                                {{ $draft->attendance_date?->format('d-m-Y') ?? '-' }}
                                            @endif
                                        </td>

                                        <td><code class="wa-raw">{{ $draft->raw_line }}</code></td>

                                        <td>
                                            @if ($draft->vehicle)
                                                {{ $draft->vehicle->vehicle_no }}
                                            @else
                                                <span class="text-muted">{{ $draft->vehicle_token ?? '-' }}</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($isPending)
                                                <select name="driver_id[{{ $draft->id }}]"
                                                    class="form-control form-control-sm {{ $drivers === [] ? 'wa-driver-all' : '' }}">
                                                    <option value="">-- choose --</option>
                                                    @foreach ($drivers === [] ? $allDrivers : $drivers as $id => $name)
                                                        <option value="{{ $id }}"
                                                            {{ (int) $draft->driver_id === (int) $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $draft->driver->full_name ?? '-' }}
                                            @endif
                                        </td>

                                        <td>
                                            @if ($isPending)
                                                <select name="status[{{ $draft->id }}]" class="form-control form-control-sm">
                                                    <option value="">-- choose --</option>
                                                    @foreach ($statusOptions as $id => $name)
                                                        <option value="{{ $id }}"
                                                            {{ (int) $draft->status === (int) $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $draft->attendanceStatus->name ?? '-' }}
                                            @endif
                                        </td>

                                        <td style="font-size: 12px;">
                                            @if ($draft->error)
                                                <div class="text-danger">{{ $draft->error }}</div>
                                            @endif

                                            @if ($isPending && $existing)
                                                <div class="text-warning">
                                                    Already marked <strong>{{ $existing }}</strong> - confirming replaces it.
                                                </div>
                                            @endif

                                            @if ($isPending && $blocked && !$draft->error)
                                                <div class="text-muted">{{ $blocked }}</div>
                                            @endif

                                            @if (!$isPending)
                                                <span class="text-muted">
                                                    {{ ucfirst($draft->state) }}
                                                    @if ($draft->confirmedBy)
                                                        by {{ $draft->confirmedBy->name }}
                                                    @endif
                                                    {{ $draft->confirmed_at?->format('d-m-Y H:i') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            Nothing here. Attendance sent on WhatsApp shows up on this page.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($state === 'pending' && $drafts->count())
                        <div class="mt-3">
                            <button type="submit" formaction="{{ route('whatsappAttendance.confirm') }}"
                                class="btn btn-success mr-2"
                                onclick="return confirm('Save attendance for the selected lines?')">
                                <i class="icon-checkmark3 mr-1"></i> Confirm Selected
                            </button>
                            <button type="submit" formaction="{{ route('whatsappAttendance.reject') }}"
                                class="btn btn-light"
                                onclick="return confirm('Reject the selected lines? No attendance will be written.')">
                                Reject Selected
                            </button>
                        </div>
                    @endif

                    @if ($drafts->hasPages())
                        <div class="mt-3">
                            {{ $drafts->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <script>
        $(function () {
            $('.wa-filter-card select.select2').select2({
                width: '100%',
                allowClear: false
            });

            // A driver list that fell back to every active driver is long
            // enough to need searching; the per-vehicle ones are not.
            $('.wa-draft-table select.wa-driver-all').select2({
                width: '100%',
                placeholder: 'Search driver...'
            });

            $('#checkAll').on('change', function () {
                $('.draft-check').prop('checked', this.checked);
            });

            $('.draft-check').on('change', function () {
                if (!this.checked) {
                    $('#checkAll').prop('checked', false);
                }
            });

            setTimeout(() => $('#alert-message').fadeOut(), 4000);
        });
    </script>

    <style>
        .wa-filter-card label {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .wa-filter-card .form-control,
        .wa-filter-card .select2-container--default .select2-selection--single {
            height: 36px;
            min-height: 36px;
            font-size: 13px;
        }

        .wa-filter-card .select2-container--default .select2-selection--single {
            padding-top: 3px;
        }

        .wa-draft-table td,
        .wa-draft-table th {
            vertical-align: middle;
            padding: 6px 8px;
        }

        .wa-draft-table .form-control-sm {
            height: 30px;
            font-size: 12px;
        }

        /* A native date input asks for far more room than it needs, and left
           alone it takes the width the notes column has to live on. */
        .wa-draft-table input[type="date"] {
            max-width: 130px;
        }

        /* The line exactly as the supervisor typed it, so a stray character is
           visible rather than smoothed away by the page font. */
        .wa-raw {
            background: #f5f5f5;
            color: #333;
            padding: 2px 5px;
            border-radius: 3px;
            white-space: nowrap;
        }
    </style>
@endsection
