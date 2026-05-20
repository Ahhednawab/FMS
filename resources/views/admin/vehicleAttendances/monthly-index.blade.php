@extends('layouts.admin')

@section('title', 'Monthly Vehicle Attendance')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-calendar22 mr-2"></i> <span class="font-weight-semibold">Monthly Vehicle Attendance</span></h4>
            </div>
        </div>
    </div>

    <div class="content">
        @include('admin.vehicleAttendances.partials.module-tabs')

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('vehicleAttendances.monthly.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label><strong>Month / Year</strong></label>
                            <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary" style="margin-bottom: 4px;">Load Monthly Attendance</button>
                            <a href="{{ route('vehicleAttendances.monthly.index') }}" class="btn btn-secondary">Reset</a>
                            <a href="{{ route('vehicleAttendances.exportMonthlyRegister', ['month' => $selectedMonth]) }}"
                                class="btn btn-success">Download Excel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Summary for {{ $monthLabel }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Station</th>
                                <th>Shift</th>
                                <th>Total Working Days</th>
                                <th>Total Present</th>
                                <th>Total Absent</th>
                                <th>Month / Year</th>
                                <th class="text-center">Show</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vehicles as $vehicle)
                                <tr>
                                    <td>{{ $vehicle->vehicle_no }}</td>
                                    <td>{{ $vehicle->station?->area ?? 'N/A' }}</td>
                                    <td>{{ $vehicle->shiftHours?->name ?? 'N/A' }}</td>
                                    <td>{{ $vehicle->total_working_days }}</td>
                                    <td>{{ $vehicle->total_present }}</td>
                                    <td>{{ $vehicle->total_absent }}</td>
                                    <td>{{ $monthLabel }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('vehicleAttendances.monthly.show', ['vehicle' => $vehicle->id, 'month' => $selectedMonth]) }}"
                                            class="btn btn-primary btn-sm">
                                            Show
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No monthly vehicle attendance records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
