@extends('layouts.admin')

@section('title', 'Vehicle Maintenance Report Detail')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Maintenance Report
                        Detail</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('vehicleMaintenanceReports.index') }}" class="btn btn-primary"><span>View Vehicle
                            Maintenance Report <i class="icon-list ml-2"></i></span></a>
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
                        <!-- Maintenance Report ID -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Maintenance Report ID</h5>
                                <p>{{ $vehicleMaintenanceReport->maintenance_report_id }}</p>
                            </div>
                        </div>

                        <!-- Vehicle No -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Vehicle No</h5>
                                <p>{{ $vehicleMaintenanceReport->vehicle->vehicle_no }}</p>
                            </div>
                        </div>

                        <!-- Model -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Model</h5>
                                <p>{{ $vehicleMaintenanceReport->model }}</p>
                            </div>
                        </div>

                        <!-- Odometer Reading -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Odometer Reading</h5>
                                <p>{{ $vehicleMaintenanceReport->odo_meter_reading }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fuel Type -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Fuel Type</h5>
                                <p>{{ $vehicleMaintenanceReport->fuelType->name }}</p>
                            </div>
                        </div>

                        <!-- Maintenance Category -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Maintenance Category</h5>
                                <p>{{ $vehicleMaintenanceReport->maintenanceCategory->category }}</p>
                            </div>
                        </div>

                        <!-- Service Date -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Service Date</h5>
                                <p>{{ \Carbon\Carbon::parse($vehicleMaintenanceReport->service_date)->format('d-M-Y') }}</p>
                            </div>
                        </div>

                        <!-- Service Provider -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Service Provider</h5>
                                <p>{{ $vehicleMaintenanceReport->serviceProvider->name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Parts Replaced -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Parts Replaced</h5>
                                <p>{{ $vehicleMaintenanceReport->parts->name }}</p>
                            </div>
                        </div>

                        <!-- Cost of Service -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Cost of Service</h5>
                                <p>{{ $vehicleMaintenanceReport->service_cost }}</p>
                            </div>
                        </div>

                        <!-- Service Description -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Service Description</h5>
                                <p>{{ $vehicleMaintenanceReport->service_description }}</p>
                            </div>
                        </div>

                        <!-- Tyre Condition -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Tyre Condition</h5>
                                <p>{{ $vehicleMaintenanceReport->tyreCondition->name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Brake Condition -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Brake Condition</h5>
                                <p>{{ $vehicleMaintenanceReport->brakeCondition->name }}</p>
                            </div>
                        </div>

                        <!-- Engine Condition -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Engine Condition</h5>
                                <p>{{ $vehicleMaintenanceReport->engineCondition->name }}</p>
                            </div>
                        </div>

                        <!-- Battery Condition -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Battery Condition</h5>
                                <p>{{ $vehicleMaintenanceReport->batteryCondition->name }}</p>
                            </div>
                        </div>

                        <!-- Next Service Due -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Next Service Due</h5>
                                <p>{{ \Carbon\Carbon::parse($vehicleMaintenanceReport->next_service_date)->format('d-M-Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- /basic datatable -->
                    <div class="col-md-12">
                        <label for=""></label>
                        <div class="text-right">
                            <a href="{{ route('vehicleMaintenanceReports.edit', $vehicleMaintenanceReport->id) }}"
                                class="btn btn-warning">Edit</a>
                            <a href="{{ route('vehicleMaintenanceReports.index') }}" class="btn btn-secondary">Back</a>
                            <form action="{{ route('vehicleMaintenanceReports.destroy', $vehicleMaintenanceReport->id) }}"
                                method="POST" style="display:inline-block;"
                                onsubmit="return confirm('Are you sure you want to delete this Vehicle Maintenance Report?');">
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
