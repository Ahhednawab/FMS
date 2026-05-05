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
                <form action="{{ route('daily-mileage.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label><strong>Date</strong></label>
                            <input type="date" name="report_date" class="form-control" value="{{ $reportDate }}"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Load Report</button>
                            <a href="{{ route('daily-mileage.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @include('admin.trackingData.partials.results', [
            'trackingData' => $trackingData,
            'totals' => $totals,
        ])
    </div>
@endsection
