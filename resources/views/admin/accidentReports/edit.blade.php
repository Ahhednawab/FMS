@extends('layouts.admin')

@section('title', 'Edit Accident Report')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Accident Report</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('accidentReports.index') }}" class="btn btn-primary">
                        <span>View Accident Report <i class="icon-list ml-2"></i></span>
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
                        <form method="POST" action="{{ route('accidentReports.update', $accidentReport->id) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Accident Report ID -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Accident Report ID</label>
                                        <input value="{{ $accidentReport->accident_report_id }}" name="accident_report_id"
                                            type="text" class="form-control" readonly>
                                    </div>
                                </div>

                                <!-- Accident Type -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Accident Type</label>
                                        <select class="custom-select" name="accident_type">
                                            <option value="">--Select--</option>
                                            @foreach ($accident_types as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('accident_type', $accidentReport->accident_type ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('accident_type'))
                                            <label class="text-danger">{{ $errors->first('accident_type') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Location</label>
                                        <input type="text" class="form-control" name="location"
                                            value="{{ old('location', $accidentReport->location ?? '') }}">
                                        @if ($errors->has('location'))
                                            <label class="text-danger">{{ $errors->first('location') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Accident Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Accident Date</label>
                                        <input type="date" class="form-control" name="accident_date"
                                            value="{{ old('accident_date', $accidentReport->accident_date ?? '') }}">
                                        @if ($errors->has('accident_date'))
                                            <label class="text-danger">{{ $errors->first('accident_date') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Accident Time -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Accident Time</label>
                                        <input type="time" class="form-control" name="accident_time"
                                            value="{{ old('accident_time', $accidentReport->accident_time ?? '') }}">
                                        @if ($errors->has('accident_time'))
                                            <label class="text-danger">{{ $errors->first('accident_time') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Accident Description -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Accident Description</label>
                                        <input type="text" class="form-control" name="accident_description"
                                            value="{{ old('accident_description', $accidentReport->accident_description ?? '') }}">
                                        @if ($errors->has('accident_description'))
                                            <label class="text-danger">{{ $errors->first('accident_description') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Person Involved -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Person Involved</label>
                                        <select class="custom-select" name="person_involved">
                                            <option value="">--Select--</option>
                                            @foreach ($users as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('person_involved', $accidentReport->person_involved ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('person_involved'))
                                            <label class="text-danger">{{ $errors->first('person_involved') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Injury Type -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Injury Type</label>
                                        <select class="custom-select" name="injury_type">
                                            <option value="">--Select--</option>
                                            @foreach ($injury_types as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('injury_type', $accidentReport->injury_type ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('injury_type'))
                                            <label class="text-danger">{{ $errors->first('injury_type') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Damage Type -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Damage Type</label>
                                        <select class="custom-select" name="damage_type">
                                            <option value="">--Select--</option>
                                            @foreach ($damage_types as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('damage_type', $accidentReport->damage_type ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('damage_type'))
                                            <label class="text-danger">{{ $errors->first('damage_type') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Witness Involved -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Witness Involved</label>
                                        <select class="custom-select" name="witness_involved">
                                            <option value="">--Select--</option>
                                            @foreach ($status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('witness_involved', $accidentReport->witness_involved ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('witness_involved'))
                                            <label class="text-danger">{{ $errors->first('witness_involved') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Vehicle No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Vehicle No</label>
                                        <input type="text" class="form-control" name="vehicle_no"
                                            value="{{ old('vehicle_no', $accidentReport->vehicle_no ?? '') }}">
                                        @if ($errors->has('vehicle_no'))
                                            <label class="text-danger">{{ $errors->first('vehicle_no') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Primary Cause -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Primary Cause</label>
                                        <select class="custom-select" name="primary_cause">
                                            <option value="">--Select--</option>
                                            @foreach ($primary_cause as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('primary_cause', $accidentReport->primary_cause ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('primary_cause'))
                                            <label class="text-danger">{{ $errors->first('primary_cause') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Medical Provided -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Medical Provided</label>
                                        <select class="custom-select" name="medical_provided">
                                            <option value="">--Select--</option>
                                            @foreach ($status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('medical_provided', $accidentReport->medical_provided ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('medical_provided'))
                                            <label class="text-danger">{{ $errors->first('medical_provided') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Police Report Filed -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Police Report Filed</label>
                                        <select class="custom-select" name="police_report_filed">
                                            <option value="">--Select--</option>
                                            @foreach ($status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('police_report_filed', $accidentReport->police_report_filed ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('police_report_filed'))
                                            <label class="text-danger">{{ $errors->first('police_report_filed') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Investigation Status -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Investigation Status</label>
                                        <select class="custom-select" name="investigation_status">
                                            <option value="">--Select--</option>
                                            @foreach ($investigation_status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('investigation_status', $accidentReport->investigation_status ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('investigation_status'))
                                            <label
                                                class="text-danger">{{ $errors->first('investigation_status') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Insurance Claimed -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Insurance Claimed</label>
                                        <select class="custom-select" name="insurance_claimed">
                                            <option value="">--Select--</option>
                                            @foreach ($status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('insurance_claimed', $accidentReport->insurance_claimed ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('insurance_claimed'))
                                            <label class="text-danger">{{ $errors->first('insurance_claimed') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Insurance Docs -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Insurance Docs</label>
                                        <input type="file" class="form-control" name="insurance_doc">
                                        @if ($errors->has('insurance_doc'))
                                            <label class="text-danger">{{ $errors->first('insurance_doc') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Police Report File -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Police Report File</label>
                                        <input type="file" class="form-control" name="police_report_file">
                                        @if ($errors->has('police_report_file'))
                                            <label class="text-danger">{{ $errors->first('police_report_file') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-6">
                                    <label for=""></label>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <a href="{{ route('accidentReports.index') }}" class="btn btn-warning">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
