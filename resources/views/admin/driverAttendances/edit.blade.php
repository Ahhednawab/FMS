@extends('layouts.admin')

@section('title', 'Edit Daily Attendance')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />

    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Daily Attendance</span>
                </h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('driverAttendances.index') }}" class="btn btn-primary">
                        <span>View Daily Attendance <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('driverAttendances.update', $driverAttendance->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Driver</strong>
                                        <input type="text" class="form-control" name="full_name"
                                            value="{{ $driverAttendance->driver->full_name }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Date</strong>
                                        <input type="date" class="form-control" name="date"
                                            value="{{ old('date', $driverAttendance->date ?? '') }}"
                                            max="{{ date('Y-m-d') }}">
                                        @if ($errors->has('date'))
                                            <label class="text-danger">{{ $errors->first('date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Shift</strong>
                                        <input type="text" class="form-control" name="shift"
                                            value="{{ optional($driverAttendance->driver->shiftTiming)->name
                                                ? $driverAttendance->driver->shiftTiming->name .
                                                    ' (' .
                                                    \Carbon\Carbon::parse($driverAttendance->driver->shiftTiming->start_time)->format('h:i A') .
                                                    ' - ' .
                                                    \Carbon\Carbon::parse($driverAttendance->driver->shiftTiming->end_time)->format('h:i A') .
                                                    ')'
                                                : 'N/A' }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Status</strong>
                                        <input type="text" class="form-control" name="full_name"
                                            value="{{ $driverAttendance->driver->driverStatus->name }}" readonly>
                                    </div>
                                </div>

                                <!-- Attendance -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Attendance</strong>
                                        <select class="custom-select" name="status">
                                            <option value="">Select</option>
                                            @foreach ($driver_attendance_status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ (string) old('status', $driverAttendance->status) === (string) $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror

                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label for=""></label>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <a href="{{ route('driverAttendances.index') }}" class="btn btn-warning">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            function calculateMileage() {
                var previous_km = $('.previous_km').val() || 0;
                var current_km = $('.current_km').val() || 0;
                var mileage = current_km - previous_km;
                if (mileage < 0) mileage = 0;
                if (previous_km) {
                    previous_km = current_km;
                }
                $('input[name="mileage"]').val(mileage.toFixed(0))
            }

            $('.current_km').on('input', function() {
                calculateMileage();
            });
        });
    </script>
@endsection
