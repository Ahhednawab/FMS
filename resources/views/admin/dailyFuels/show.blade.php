@extends('layouts.admin')

@section('title', 'Daily Fuel Detail')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Fuel Detail</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('dailyFuels.index') }}" class="btn btn-primary"><span>View Daily Fuel <i
                                class="icon-list ml-2"></i></span></a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div class="content">
        <div class="card">
            <div class="card-body">
                <div class="container mt-3">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="card">
                                <h5 class="m-0">Station</h5>
                                <p>{{ $dailyFuel->vehicle->station->area }}</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card">
                                <h5 class="m-0">Vehicle No</h5>
                                <p>{{ $dailyFuel->vehicle->vehicle_no }}</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card">
                                <h5 class="m-0">Report Date</h5>
                                <p>{{ \Carbon\Carbon::parse($dailyFuel->report_date)->format('d-M-Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="card">
                                <h5 class="m-0">Previous Km</h5>
                                <p>{{ $dailyFuel->previous_km }} Km</p>
                            </div>
                        </div>

                        <div class="col-md-2 text-center">
                            <div class="card">
                                <h5 class="m-0">Current Km</h5>
                                <p>{{ $dailyFuel->current_km }} Km</p>
                            </div>
                        </div>

                        <div class="col-md-2 text-center">
                            <div class="card">
                                <h5 class="m-0">Mileage</h5>
                                <p>{{ $dailyFuel->current_km }} Km</p>
                            </div>
                        </div>


                        <div class="col-md-2 text-center">
                            <div class="card">
                                <h5 class="m-0">Fuel Taken</h5>
                                <p>{{ $dailyFuel->fuel_taken }} Litre</p>
                            </div>
                        </div>

                        <div class="col-md-2 text-center">
                            <div class="card">
                                <h5 class="m-0">Fuel Avg.</h5>
                                <p>{{ $dailyFuel->fuel_average }} Km/Ltr</p>
                            </div>
                        </div>

                        <div class="col-md-2 text-center">
                            <div class="card">
                                <h5 class="m-0">AKPL</h5>
                                <p>{{ $dailyFuel->vehicle->akpl }} Km/Ltr</p>
                            </div>
                        </div>
                    </div>
                    <!-- /basic datatable -->
                    <div class="col-md-12">
                        <label for=""></label>
                        <div class="text-right">
                            <a href="{{ route('dailyFuels.edit', $dailyFuel->id) }}" class="btn btn-warning">Edit</a>
                            <a href="{{ route('dailyFuels.index') }}" class="btn btn-secondary">Back</a>
                            <form action="{{ route('dailyFuels.destroy', $dailyFuel->id) }}" method="POST"
                                style="display:inline-block;"
                                onsubmit="return confirm('Are you sure you want to delete this Fuel Mileage Report?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /content area -->
@endsection
