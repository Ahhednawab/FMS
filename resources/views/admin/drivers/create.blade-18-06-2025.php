@extends('layouts.admin')

@section('title', 'Add Driver')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Driver Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.drivers.index') }}" class="btn btn-primary">
            <span>View Drivers <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.drivers.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
            <!-- Serial No -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Serial No</label>
                <input type="text" name="serial_no" class="form-control" value="{{$serial_no}}" readonly>
              </div>
            </div>

            <!-- Full Name -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}">
                @if ($errors->has('full_name'))
                  <label class="text-danger">{{ $errors->first('full_name') }}</label>
                @endif
              </div>
            </div>

            <!-- Father Name -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Father Name</label>
                <input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}">
                @if ($errors->has('father_name'))
                  <label class="text-danger">{{ $errors->first('father_name') }}</label>
                @endif
              </div>
            </div>

            <!-- Mother Name -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Mother Name</label>
                <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name') }}">
                @if ($errors->has('mother_name'))
                  <label class="text-danger">{{ $errors->first('mother_name') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Designation -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Designation</label>
                <select class="custom-select" name="designation_id">
                  <option value="">Select Designation</option>
                  @foreach($designation as $value)
                    <option value="{{$value->id}}" {{ old('designation_id') == $value->id ? 'selected' : '' }}>{{$value->designation}}</option>
                  @endforeach
                </select>
                @if ($errors->has('designation_id'))
                  <label class="text-danger">{{ $errors->first('designation_id') }}</label>
                @endif
              </div>
            </div>

            <!-- Status -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Status</label>
                <select class="custom-select" name="status">
                  <option value="">Select Status</option>
                  @foreach($driver_status as $key => $value)
                    <option value="{{$key}}" {{ old('status') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('status'))
                  <label class="text-danger">{{ $errors->first('status') }}</label>
                @endif
              </div>
            </div>

            <!-- Vehicle Number -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Vehicle Number</label>
                <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number') }}">
                @if ($errors->has('vehicle_number'))
                  <label class="text-danger">{{ $errors->first('vehicle_number') }}</label>
                @endif
              </div>
            </div>

            <!-- Last Vehicle -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Last Vehicle</label>
                <input type="text" name="last_vehicle" class="form-control" value="{{ old('last_vehicle') }}">
                @if ($errors->has('last_vehicle'))
                  <label class="text-danger">{{ $errors->first('last_vehicle') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Account No. -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Account No.</label>
                <input type="text" name="account_no" class="form-control" value="{{ old('account_no') }}">
                @if ($errors->has('account_no'))
                  <label class="text-danger">{{ $errors->first('account_no') }}</label>
                @endif
              </div>
            </div>

            <!-- Employement Date -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Employment Date</label>
                <input type="date" name="employment_date" class="form-control" value="{{ old('employment_date') }}">
                @if ($errors->has('employment_date'))
                  <label class="text-danger">{{ $errors->first('employment_date') }}</label>
                @endif
              </div>
            </div>
            
            <!-- Employment Form -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Employment Form</label>
                <select class="custom-select" name="employement_form">
                  <option value="">Select Option</option>
                  @foreach($status as $key => $value)
                    <option value="{{$key}}" {{ old('employement_form') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('employement_form'))
                  <label class="text-danger">{{ $errors->first('employement_form') }}</label>
                @endif
              </div>
            </div>

            <!-- CNIC Copy -->
            <div class="col-md-3">
              <div class="form-group">
                <label>CNIC Copy</label>
                <select class="custom-select" name="cnic_copy">
                  <option value="">Select Option</option>
                  @foreach($status as $key => $value)
                    <option value="{{ $key }}" {{ old('cnic_copy') == $key ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
                @if ($errors->has('cnic_copy'))
                  <label class="text-danger">{{ $errors->first('cnic_copy') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- License Copy -->
            <div class="col-md-3">
              <div class="form-group">
                <label>License Copy</label>
                <select class="custom-select" name="license_copy">
                  <option value="">Select Option</option>
                  @foreach($status as $key => $value)
                    <option value="{{ $key }}" {{ old('license_copy') == $key ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
                @if ($errors->has('license_copy'))
                  <label class="text-danger">{{ $errors->first('license_copy') }}</label>
                @endif
              </div>
            </div>

            <!-- DDC -->
            <div class="col-md-3">
              <div class="form-group">
                <label>DDC</label>
                <select class="custom-select" name="ddc">
                  <option value="">Select Option</option>
                  @foreach($status as $key => $value)
                    <option value="{{ $key }}" {{ old('ddc') == $key ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
                @if ($errors->has('ddc'))
                  <label class="text-danger">{{ $errors->first('ddc') }}</label>
                @endif
              </div>
            </div>

            <!-- 3P Driver Form -->
            <div class="col-md-3">
              <div class="form-group">
                <label>3P Driver Form</label>
                <select class="custom-select" name="p_driver_form">
                  <option value="">Select Option</option>
                  @foreach($status as $key => $value)
                    <option value="{{ $key }}" {{ old('p_driver_form') == $key ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
                @if ($errors->has('p_driver_form'))
                  <label class="text-danger">{{ $errors->first('p_driver_form') }}</label>
                @endif
              </div>
            </div>

            <!-- Employee Card Copy -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Employee Card Copy</label>
                <select class="custom-select" name="employee_card_copy">
                  <option value="">Select Option</option>
                  @foreach($status as $key => $value)
                    <option value="{{ $key }}" {{ old('employee_card_copy') == $key ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
                @if ($errors->has('employee_card_copy'))
                  <label class="text-danger">{{ $errors->first('employee_card_copy') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Medical Report -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Medical Report</label>
                <select class="custom-select" name="medical_report">
                  <option value="">Select Option</option>
                  @foreach($status as $key => $value)
                    <option value="{{ $key }}" {{ old('medical_report') == $key ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
                @if ($errors->has('medical_report'))
                  <label class="text-danger">{{ $errors->first('medical_report') }}</label>
                @endif
              </div>
            </div>      

            <!-- EOBI Start Date -->
            <div class="col-md-3">
              <div class="form-group">
                <label>EOBI Start Date</label>
                <input type="date" name="eobi_start_date" class="form-control" value="{{ old('eobi_start_date') }}">
                @if ($errors->has('eobi_start_date'))
                  <label class="text-danger">{{ $errors->first('eobi_start_date') }}</label>
                @endif
              </div>
            </div>

            <!-- EOBI No -->
            <div class="col-md-3">
              <div class="form-group">
                <label>EOBI No</label>
                <input type="text" name="eobi_no" class="form-control" value="{{ old('eobi_no') }}">
                @if ($errors->has('eobi_no'))
                  <label class="text-danger">{{ $errors->first('eobi_no') }}</label>
                @endif
              </div>
            </div> 

            <!-- DOB -->
            <div class="col-md-3">
              <div class="form-group">
                <label>DOB</label>
                <input type="date" name="dob" class="form-control" value="{{ old('dob') }}">
                @if ($errors->has('dob'))
                  <label class="text-danger">{{ $errors->first('dob') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Marital Status -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Marital Status</label>
                <select class="custom-select" name="marital_status">
                  <option value="">Select Status</option>
                  @foreach($marital_status as $key => $value)
                    <option value="{{$key}}" {{ old('marital_status') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('marital_status'))
                  <label class="text-danger">{{ $errors->first('marital_status') }}</label>
                @endif
              </div>
            </div>

            <!-- CNIC No -->
            <div class="col-md-3">
              <div class="form-group">
                <label>CNIC No</label>
                <input type="text" name="cnic_no" class="form-control" value="{{ old('cnic_no') }}">
                @if ($errors->has('cnic_no'))
                  <label class="text-danger">{{ $errors->first('cnic_no') }}</label>
                @endif
              </div>
            </div>

            <!-- CNIC Issue Date -->
            <div class="col-md-3">
              <div class="form-group">
                <label>CNIC Issue Date</label>
                <input type="date" name="cnic_issue_date" class="form-control" value="{{ old('cnic_issue_date') }}">
                @if ($errors->has('cnic_issue_date'))
                  <label class="text-danger">{{ $errors->first('cnic_issue_date') }}</label>
                @endif
              </div>
            </div>

            <!-- CNIC Expiry Date -->
            <div class="col-md-3">
              <div class="form-group">
                <label>CNIC Expiry Date</label>
                <input type="date" name="cnic_expiry_date" class="form-control" value="{{ old('cnic_expiry_date') }}">
                @if ($errors->has('cnic_expiry_date'))
                  <label class="text-danger">{{ $errors->first('cnic_expiry_date') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- License No -->
            <div class="col-md-3">
              <div class="form-group">
                <label>License No</label>
                <input type="text" name="license_no" class="form-control" value="{{ old('license_no') }}">
                @if ($errors->has('license_no'))
                  <label class="text-danger">{{ $errors->first('license_no') }}</label>
                @endif
              </div>
            </div>

            <!-- License Category -->
            <div class="col-md-3">
              <div class="form-group">
                <label>License Category</label>
                <select class="custom-select" name="license_category">
                  <option value="">Select Category</option>
                  @foreach($licence_category as $key => $value)
                    <option value="{{$key}}" {{ old('license_category') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('license_category'))
                  <label class="text-danger">{{ $errors->first('license_category') }}</label>
                @endif
              </div>
            </div>

            <!-- License Issue Date -->
            <div class="col-md-3">
              <div class="form-group">
                <label>License Issue Date</label>
                <input type="date" name="license_issue_date" class="form-control" value="{{ old('license_issue_date') }}">
                @if ($errors->has('license_issue_date'))
                  <label class="text-danger">{{ $errors->first('license_issue_date') }}</label>
                @endif
              </div>
            </div>

            <!-- License Expiry Date -->
            <div class="col-md-3">
              <div class="form-group">
                <label>License Expiry Date</label>
                <input type="date" name="license_expiry_date" class="form-control" value="{{ old('license_expiry_date') }}">
                @if ($errors->has('license_expiry_date'))
                  <label class="text-danger">{{ $errors->first('license_expiry_date') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Blacklist -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Blacklist</label>
                <input type="text" name="black_list" class="form-control" value="{{ old('black_list') }}">
                @if ($errors->has('black_list'))
                  <label class="text-danger">{{ $errors->first('black_list') }}</label>
                @endif
              </div>
            </div>

            <!-- Cell Phone No -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Cell Phone No</label>
                <input type="text" name="cell_phone" class="form-control" value="{{ old('cell_phone') }}">
                @if ($errors->has('cell_phone'))
                  <label class="text-danger">{{ $errors->first('cell_phone') }}</label>
                @endif
              </div>
            </div>

            <!-- Address -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                @if ($errors->has('address'))
                  <label class="text-danger">{{ $errors->first('address') }}</label>
                @endif
              </div>
            </div>

            <!-- Authority Letter -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Authority Letter</label>
                <select class="custom-select" name="authority_letter">
                  <option value="">Select Option</option>
                  @foreach($status as $key => $value)
                    <option value="{{$key}}" {{ old('authority_letter') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('authority_letter'))
                  <label class="text-danger">{{ $errors->first('authority_letter') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Picture -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Picture</label>
                <input type="file" class="form-control" name="picture_file">
                @if ($errors->has('picture_file'))
                  <label class="text-danger">{{ $errors->first('picture_file') }}</label>
                @endif
              </div>
            </div>

            <!-- Authority letter -->
            <div class="col-md-3">
              <div class="Authority letter-group">
                <label>Authority Letter</label>
                <input type="file" class="form-control" name="authority_letter_file">
                @if ($errors->has('authority_letter_file'))
                  <label class="text-danger">{{ $errors->first('authority_letter_file') }}</label>
                @endif
              </div>
            </div>

            <!-- Buttons -->
            <div class="col-md-6">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-warning">Save</button>
                <a href="{{ route('admin.drivers.index') }}" class="btn btn-primary">Cancel</a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
