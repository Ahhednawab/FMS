@extends('layouts.admin')

@section('title', 'Edit Daily Mileage')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />

    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Daily Mileage</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
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
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('dailyMileages.update', $dailyMileage->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Vehicle No</strong>
                                        <input type="text" class="form-control" name="vehicle_no"
                                            value="{{ old('vehicle_id', $dailyMileage->vehicle->vehicle_no ?? '') }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Report Date</strong>
                                        <input type="date" class="form-control" name="report_date"
                                            value="{{ old('report_date', $dailyMileage->report_date ?? '') }}">
                                        @if ($errors->has('report_date'))
                                            <label class="text-danger">{{ $errors->first('report_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Previous Km</strong>
                                        <input type="number" min="0" step="1" class="form-control previous_km"
                                            name="previous_km"
                                            value="{{ old('previous_km', $dailyMileage->previous_km ?? '') }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Current Km</strong>
                                        <input type="number" min="0" step="1" class="form-control current_km"
                                            name="current_km"
                                            value="{{ old('current_km', $dailyMileage->current_km ?? '') }}">
                                        @if ($errors->has('current_km'))
                                            <label class="text-danger">{{ $errors->first('current_km') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <strong>Mileage</strong>
                                        <input type="number" min="0" step="1" class="form-control"
                                            name="mileage" value="{{ old('mileage', $dailyMileage->mileage ?? '') }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label for=""></label>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <a href="{{ route('dailyMileages.index') }}" class="btn btn-warning">Cancel</a>
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
