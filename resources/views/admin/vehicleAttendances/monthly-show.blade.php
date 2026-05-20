@extends('layouts.admin')

@section('title', 'Monthly Vehicle Attendance Sheet')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-calendar22 mr-2"></i> <span class="font-weight-semibold">Monthly Vehicle Attendance Sheet</span></h4>
            </div>
        </div>
    </div>

    <div class="content">
        @include('admin.vehicleAttendances.partials.module-tabs')

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('vehicleAttendances.monthly.show', $vehicle->id) }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label><strong>Month / Year</strong></label>
                            <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}">
                        </div>
                        <div class="col-md-9">
                            <label><strong>Attendance Filters</strong></label>
                            <div class="d-flex flex-wrap align-items-center">
                                @php
                                    $filterOptions = [
                                        'present' => 'Present',
                                        'absent' => 'Absent',
                                        'off' => 'Off',
                                        'replace' => 'Replace',
                                        'no_record' => 'No Record',
                                    ];
                                @endphp
                                @foreach ($filterOptions as $value => $label)
                                    <label class="mr-3 mb-2">
                                        <input type="checkbox" name="statuses[]" value="{{ $value }}"
                                            {{ in_array($value, $selectedStatuses, true) ? 'checked' : '' }}>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="{{ route('vehicleAttendances.monthly.show', ['vehicle' => $vehicle->id, 'month' => $selectedMonth]) }}"
                                class="btn btn-secondary">Reset</a>
                            <a href="{{ route('vehicleAttendances.monthly.export', ['vehicle' => $vehicle->id, 'month' => $selectedMonth]) }}"
                                class="btn btn-success">Export to Excel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Vehicle</h6>
                        <h5 class="mb-0">{{ $vehicle->vehicle_no }}</h5>
                        <small>{{ $vehicle->station?->area ?? 'N/A' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Total Working Days</h6>
                        <h3 class="mb-0 text-primary">{{ $totalWorkingDays }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Present Count</h6>
                        <h3 class="mb-0 text-success">{{ $presentDaysCount }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Absent Count</h6>
                        <h3 class="mb-0 text-danger">{{ $absentDaysCount }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">{{ $monthLabel }} Attendance Sheet</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Station</th>
                                <th>Shift</th>
                                <th>Status</th>
                                <th>Replacement Vehicle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($calendarRows as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-M-Y') }}</td>
                                    <td>{{ $row['day_name'] }}</td>
                                    <td>{{ $row['station'] }}</td>
                                    <td>{{ $row['shift'] }}</td>
                                    <td>{{ $row['status_label'] }}</td>
                                    <td>{{ $row['is_replacement'] ? ($row['replacement_vehicle'] ?? 'N/A') : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No attendance records found for selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
