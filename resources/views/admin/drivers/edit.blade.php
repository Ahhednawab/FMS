@extends('layouts.admin')

@section('title', 'Edit Driver')

@section('content')
    <style>
        .select2-search--dropdown::after {
            content: '' !important;
            display: none !important;
            background: none !important;
        }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />

    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Driver</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('drivers.index') }}" class="btn btn-primary">
                        <span>View Drivers <i class="icon-list ml-2"></i></span>
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
                        <form method="POST" action="{{ route('drivers.update', $driver->id) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Serial No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Serial No</strong>
                                        <input type="text" name="serial_no" class="form-control"
                                            value="{{ $driver->serial_no }}" readonly>
                                    </div>
                                </div>

                                <!-- Full Name -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Full Name</strong>
                                        <input type="text" name="full_name" class="form-control"
                                            value="{{ old('full_name', $driver->full_name ?? '') }}">
                                        @if ($errors->has('full_name'))
                                            <label class="text-danger">{{ $errors->first('full_name') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Father Name -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Father Name</strong>
                                        <input type="text" name="father_name" class="form-control"
                                            value="{{ old('father_name', $driver->father_name ?? '') }}">
                                        @if ($errors->has('father_name'))
                                            <label class="text-danger">{{ $errors->first('father_name') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Mother Name -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Mother Name</strong>
                                        <input type="text" name="mother_name" class="form-control"
                                            value="{{ old('mother_name', $driver->mother_name ?? '') }}">
                                        @if ($errors->has('mother_name'))
                                            <label class="text-danger">{{ $errors->first('mother_name') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Cell Phone No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Cell Phone No</strong>
                                        <input type="text" name="phone" id="phone" class="form-control"
                                            value="{{ old('phone', $driver->phone ?? '') }}">
                                        @if ($errors->has('phone'))
                                            <label class="text-danger">{{ $errors->first('phone') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Salary -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Salary</strong>
                                        <input type="number" min="0" step="1" name="salary"
                                            class="form-control" value="{{ old('salary', $driver->salary ?? '') }}">
                                        @if ($errors->has('salary'))
                                            <label class="text-danger">{{ $errors->first('salary') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Account No. -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Account No.</strong>
                                        <input type="text" name="account_no" class="form-control"
                                            value="{{ old('account_no', $driver->account_no ?? '') }}">
                                        @if ($errors->has('account_no'))
                                            <label class="text-danger">{{ $errors->first('account_no') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Status</strong>
                                        <select class="custom-select" name="driver_status_id">
                                            <option value="">Select Status</option>
                                            @foreach ($driver_status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('driver_status_id', $driver->driver_status_id ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('driver_status_id'))
                                            <label class="text-danger">{{ $errors->first('driver_status_id') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Marital Status -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Marital Status</strong>
                                        <select class="custom-select" name="marital_status_id">
                                            <option value="">Select Status</option>
                                            @foreach ($marital_status as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('marital_status_id', $driver->marital_status_id ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('marital_status_id'))
                                            <label class="text-danger">{{ $errors->first('marital_status_id') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- DOB -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>DOB</strong>
                                        <input type="date" name="dob" class="form-control"
                                            value="{{ old('dob', $driver->dob ?? '') }}">
                                        @if ($errors->has('dob'))
                                            <label class="text-danger">{{ $errors->first('dob') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Vehicle Number -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Vehicle Number</strong>
                                        <select class="custom-select select2 vehicle" id="vehicle_id" name="vehicle_id">
                                            <option value="">--Select--</option>
                                            @foreach ($vehicles as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('vehicle_id', $driver->vehicle_id ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('vehicle_id'))
                                            <label class="text-danger">{{ $errors->first('vehicle_id') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Shift Timing -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Shift Timing</strong>
                                        <select class="form-control " id="shift_timing_id" name="shift_timing_id">
                                            <option value="">--Select--</option>
                                            @foreach ($shift_timings as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('shift_timing_id', $driver->shift_timing_id ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('shift_timing_id'))
                                            <label class="text-danger">{{ $errors->first('shift_timing_id') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- CNIC No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>CNIC No <span class="text-danger">*</span></strong>
                                        {{-- <input type="text" name="cnic_no" id="cnic_no" class="form-control" value="{{ old('cnic_no') }}"> --}}
                                        <input type="text" name="cnic_no" class="form-control" id="cnic_no"
                                            value="{{ old('cnic_no', $driver->cnic_no ?? '') }}">
                                        @if ($errors->has('cnic_no'))
                                            <label class="text-danger">{{ $errors->first('cnic_no') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- CNIC Expiry Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>CNIC Expiry Date <span class="text-danger">*</span></strong>
                                        <input type="date" name="cnic_expiry_date" class="form-control"
                                            value="{{ old('cnic_expiry_date', $driver->cnic_expiry_date ?? '') }}">
                                        @if ($errors->has('cnic_expiry_date'))
                                            <label class="text-danger">{{ $errors->first('cnic_expiry_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- CNIC -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>CNIC</strong>
                                        <input type="file" class="form-control" name="cnic_file">
                                        @if ($errors->has('cnic_file'))
                                            <label class="text-danger">{{ $errors->first('cnic_file') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- EOBI No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>EOBI No</strong>
                                        <input type="text" name="eobi_no" class="form-control"
                                            value="{{ old('eobi_no', $driver->eobi_no ?? '') }}">
                                        @if ($errors->has('eobi_no'))
                                            <label class="text-danger">{{ $errors->first('eobi_no') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- EOBI Start Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>EOBI Start Date</strong>
                                        <input type="date" name="eobi_start_date" class="form-control"
                                            value="{{ old('eobi_start_date', $driver->eobi_start_date ?? '') }}">
                                        @if ($errors->has('eobi_start_date'))
                                            <label class="text-danger">{{ $errors->first('eobi_start_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- EOBI Card -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>EOBI Card</strong>
                                        <input type="file" class="form-control" name="eobi_card_file">
                                        @if ($errors->has('eobi_card_file'))
                                            <label class="text-danger">{{ $errors->first('eobi_card_file') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Picture -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Picture</strong>
                                        <input type="file" class="form-control" name="picture_file">
                                        @if ($errors->has('picture_file'))
                                            <label class="text-danger">{{ $errors->first('picture_file') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Medical Report -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Medical Report</strong>
                                        <input type="file" class="form-control" name="medical_report_file">
                                        @if ($errors->has('medical_report_file'))
                                            <label class="text-danger">{{ $errors->first('medical_report_file') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Authority letter -->
                                <div class="col-md-3">
                                    <div class="Authority letter-group">
                                        <strong>Authority Letter</strong>
                                        <input type="file" class="form-control" name="authority_letter_file">
                                        @if ($errors->has('authority_letter_file'))
                                            <label
                                                class="text-danger">{{ $errors->first('authority_letter_file') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Employement Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Employment Date</strong>
                                        <input type="date" name="employment_date" class="form-control"
                                            value="{{ old('employment_date', $driver->employment_date ?? '') }}">
                                        @if ($errors->has('employment_date'))
                                            <label class="text-danger">{{ $errors->first('employment_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Employee Card -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Employee Card</strong>
                                        <input type="file" class="form-control" name="employee_card_file">
                                        @if ($errors->has('employee_card_file'))
                                            <label class="text-danger">{{ $errors->first('employee_card_file') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- DDC -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>DDC</strong>
                                        <input type="file" class="form-control" name="ddc_file">
                                        @if ($errors->has('ddc_file'))
                                            <label class="text-danger">{{ $errors->first('ddc_file') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- 3P Driver Form -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>3P Driver Form</strong>
                                        <input type="file" class="form-control" name="third_party_driver_file">
                                        @if ($errors->has('third_party_driver_file'))
                                            <label
                                                class="text-danger">{{ $errors->first('third_party_driver_file') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- License No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>License No <span class="text-danger">*</span></strong>
                                        <input type="text" name="license_no" class="form-control"
                                            value="{{ old('license_no', $driver->license_no ?? '') }}">
                                        @if ($errors->has('license_no'))
                                            <label class="text-danger">{{ $errors->first('license_no') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- License Category -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>License Category <span class="text-danger">*</span></strong>
                                        <select class="custom-select" name="license_category_id">
                                            <option value="">Select Category</option>
                                            @foreach ($licence_category as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('license_category_id', $driver->license_category_id ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('license_category_id'))
                                            <label class="text-danger">{{ $errors->first('license_category_id') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- License Expiry Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>License Expiry Date <span class="text-danger">*</span></strong>
                                        <input type="date" name="license_expiry_date" class="form-control"
                                            value="{{ old('license_expiry_date', $driver->license_expiry_date ?? '') }}">
                                        @if ($errors->has('license_expiry_date'))
                                            <label class="text-danger">{{ $errors->first('license_expiry_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- License -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>License</strong>
                                        <input type="file" class="form-control" name="license_file">
                                        @if ($errors->has('license_file'))
                                            <label class="text-danger">{{ $errors->first('license_file') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Uniform Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Uniform Issue Date</strong>
                                        <input type="date" name="uniform_issue_date" class="form-control"
                                            value="{{ old('uniform_issue_date', $driver->uniform_issue_date ?? '') }}">
                                        @if ($errors->has('uniform_issue_date'))
                                            <label class="text-danger">{{ $errors->first('uniform_issue_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Sandal Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Sandal Issue Date</strong>
                                        <input type="date" name="sandal_issue_date" class="form-control"
                                            value="{{ old('sandal_issue_date', $driver->sandal_issue_date ?? '') }}">
                                        @if ($errors->has('sandal_issue_date'))
                                            <label class="text-danger">{{ $errors->first('sandal_issue_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                {{-- Last Date --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Last Date</strong>
                                        <input type="date" name="last_date" class="form-control"
                                            value="{{ old('last_date', $driver->last_date ?? '') }}">
                                        @if ($errors->has('last_date'))
                                            <label class="text-danger">{{ $errors->first('last_date') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Address</strong>
                                        <input type="text" name="address" class="form-control"
                                            value="{{ old('address', $driver->address ?? '') }}">
                                        @if ($errors->has('address'))
                                            <label class="text-danger">{{ $errors->first('address') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-2">
                                    <label for=""></label>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <a href="{{ route('drivers.index') }}" class="btn btn-warning">Cancel</a>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.vehicle').select2({
                placeholder: "--Select--",
                allowClear: true,
                theme: 'bootstrap4'
            });

            $('#cnic_no').inputmask("99999-9999999-9");
            $('#phone').inputmask("0399-9999999");
        });
    </script>
@endsection
