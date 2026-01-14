@extends('layouts.admin')

@section('title', 'Accident Report Detail')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Accident Report Detail</span>
                </h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('accidentReports.index') }}" class="btn btn-primary"><span>View Accident Report <i
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
                        <!-- Accident Report ID -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Accident Report ID </h5>
                                <p>{{ $accidentReport->accident_report_id }}</p>
                            </div>
                        </div>

                        <!-- Accident Type -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Accident Type</h5>
                                <p>{{ $accident_types[$accidentReport->accident_type] }}</p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Location</h5>
                                <p>{{ $accidentReport->location }}</p>
                            </div>
                        </div>

                        <!-- Accident Date -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Accident Date</h5>
                                <p>{{ \Carbon\Carbon::parse($accidentReport->accident_date)->format('d-M-Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Accident Time -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Accident Time</h5>
                                <p>{{ \Carbon\Carbon::parse($accidentReport->accident_time)->format('h:i A') }}</p>
                            </div>
                        </div>

                        <!-- Accident Description -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Accident Description</h5>
                                <p>{{ $accidentReport->accident_description }}</p>
                            </div>
                        </div>

                        <!-- Person Involved -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Person Involved</h5>
                                <p>{{ $users[$accidentReport->person_involved] }}</p>
                            </div>
                        </div>

                        <!-- Injury Type -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Injury Type</h5>
                                <p>{{ $injury_types[$accidentReport->injury_type] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Damage Type -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Damage Type</h5>
                                <p>{{ $damage_types[$accidentReport->damage_type] }}</p>
                            </div>
                        </div>

                        <!-- Witness Involved -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Witness Involved</h5>
                                <p>{{ $status[$accidentReport->witness_involved] }}</p>
                            </div>
                        </div>

                        <!-- Vehicle No -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Vehicle No</h5>
                                <p>{{ $accidentReport->vehicle_no }}</p>
                            </div>
                        </div>

                        <!-- Primary Cause -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Primary Cause</h5>
                                <p>{{ $primary_cause[$accidentReport->primary_cause] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Medical Provided -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Medical Provided</h5>
                                <p>{{ $status[$accidentReport->medical_provided] }}</p>
                            </div>
                        </div>

                        <!-- Police Report Filed -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Police Report Filed</h5>
                                <p>{{ $status[$accidentReport->police_report_filed] }}</p>
                            </div>
                        </div>

                        <!-- Investigation Status -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Investigation Status</h5>
                                <p>{{ $investigation_status[$accidentReport->investigation_status] }}</p>
                            </div>
                        </div>

                        <!-- Insurance Claimed -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Insurance Claimed</h5>
                                <p>{{ $status[$accidentReport->insurance_claimed] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Insurance Docs -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Insurance Docs</h5>
                                @if ($accidentReport->insurance_doc)
                                    <a href="{{ asset('uploads/accidentReports/' . $accidentReport->insurance_doc) }}"
                                        download>Download</a>
                                @else
                                    <p>N/A</p>
                                @endif
                            </div>
                        </div>

                        <!-- Police Report File -->
                        <div class="col-md-3 text-center">
                            <div class="card">
                                <h5 class="m-0">Police Report File</h5>
                                @if ($accidentReport->police_report_file)
                                    <a href="{{ asset('uploads/accidentReports/' . $accidentReport->police_report_file) }}"
                                        download>Download</a>
                                @else
                                    <p>N/A</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- /basic datatable -->
                    <div class="col-md-12">
                        <label for=""></label>
                        <div class="text-right">
                            <a href="#" class="btn btn-warning">Edit</a>
                            <a href="{{ route('accidentReports.index') }}" class="btn btn-secondary">Back</a>
                            <form action="{{ route('accidentReports.destroy', $accidentReport->id) }}" method="POST"
                                style="display:inline-block;"
                                onsubmit="return confirm('Are you sure you want to delete this Accident Report?');">
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
