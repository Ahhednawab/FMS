@extends('layouts.admin')

@section('title', 'Driver Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Driver Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.drivers.index') }}" class="btn btn-primary"><span>View Drivers <i class="icon-list ml-2"></i></span></a>
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
            <!-- Serial No -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Serial No</h5>
                <p>{{ $driver->serial_no }}</p>
              </div>
            </div>

            <!-- Full Name -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Full Name</h5>
                <p>{{ $driver->full_name }}</p>
              </div>
            </div>

            <!-- Father Name -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Father Name</h5>
                <p>{{ $driver->father_name }}</p>
              </div>
            </div>

            <!-- Mother Name -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Mother Name</h5>
                <p>{{ $driver->mother_name }}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Cell Phone No -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Cell Phone No</h5>
                <p>{{ $driver->phone }}</p>
              </div>
            </div>

            <!-- Salary -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Salary</h5>
                <p>{{ $driver->salary }}</p>
              </div>
            </div>

            <!-- Account No -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Account No</h5>
                <p>{{ $driver->account_no }}</p>
              </div>
            </div>

            <!-- Status -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Status</h5>
                <p>{{ $driver->driverStatus->name }}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Marital Status -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Marital Status</h5>
                <p>{{ $driver->maritalStatus->name }}</p>
              </div>
            </div>

            <!-- DOB -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">DOB</h5>
                <p>{{ \Carbon\Carbon::parse($driver->dob)->format('d-M-Y') }}</p>
              </div>
            </div>

            <!-- Vehicle No -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Vehicle No</h5>
                <p>{{ $driver->vehicle->vehicle_no}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- CNIC No -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">CNIC No</h5>
                <p>{{ $driver->cnic_no }}</p>
              </div>
            </div>

            <!-- CNIC Expiry Date -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">CNIC Expiry Date</h5>
                <p>{{ \Carbon\Carbon::parse($driver->cnic_expiry_date)->format('d-M-Y') }}</p>
              </div>
            </div>

            <!-- CNIC -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">CNIC</h5>
                <p><a href="{{ asset('uploads/drivers/' . $driver->cnic_file) }}" download> Download</a></p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- EOBI No -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">EOBI No</h5>
                <p>{{ $driver->eobi_no ? $driver->eobi_no : 'N/A' }}</p>
              </div>
            </div>

            <!-- EOBI Start Date -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">EOBI Start Date</h5>
                <p>{{ $driver->eobi_start_date ? \Carbon\Carbon::parse($driver->eobi_start_date)->format('d-M-Y') : 'N/A' }}</p>
              </div>
            </div>

            <!-- EOBI Card -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">EOBI Card</h5>
                <p>
                  @if($driver->eobi_card_file)
                    <a href="{{ asset('uploads/drivers/' . $driver->eobi_card_file) }}" download> Download</a>
                  @else
                    N/A
                  @endif
                </p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Picture -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Picture</h5>
                <p><a href="{{ asset('uploads/drivers/' . $driver->picture_file) }}" download> Download</a></p>
              </div>
            </div>

            <!-- Medical Report -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Medical Report</h5>
                <p>
                  @if($driver->medical_report_file)
                    <a href="{{ asset('uploads/drivers/' . $driver->medical_report_file) }}" download> Download</a>
                  @else
                    N/A
                  @endif
                </p>
              </div>
            </div>

            <!-- Authority Letter -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Authority Letter</h5>
                <p><a href="{{ asset('uploads/drivers/' . $driver->authority_letter_file) }}" download> Download</a></p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Employment Date -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Employment Date</h5>
                <p>{{ \Carbon\Carbon::parse($driver->employment_date)->format('d-M-Y') }}</p>
              </div>
            </div>

            <!-- Employee Card -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Employee Card</h5>
                <p><a href="{{ asset('uploads/drivers/' . $driver->employee_card_file) }}" download> Download</a></p>
              </div>
            </div>

            <!-- DDC -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">DDC</h5>
                <p><a href="{{ asset('uploads/drivers/' . $driver->ddc_file) }}" download> Download</a></p>
              </div>
            </div>

            <!-- 3P Driver Form -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">3P Driver Form</h5>
                <p>
                  @if(isset($driver->third_party_driver_file))
                    <a href="{{ asset('uploads/drivers/' . $driver->third_party_driver_file) }}" download> Download</a>
                  @else
                    N/A
                  @endif
                </p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- License No -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">License No</h5>
                <p>$driver->license_no</p>
              </div>
            </div>

            <!-- License Category -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">License Category</h5>
                <p>{{$driver->licenseCategory->name}}</p>
              </div>
            </div>

            <!-- License Expiry Date -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">License Expiry Date</h5>
                <p>{{ \Carbon\Carbon::parse($driver->license_expiry_date)->format('d-M-Y') }}</p>
              </div>
            </div>

            <!-- License -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">License</h5>
                <p><a href="{{ asset('uploads/drivers/' . $driver->license_file) }}" download> Download</a></p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Uniform Issue Date -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Uniform Issue Date</h5>
                <p>{{ \Carbon\Carbon::parse($driver->uniform_issue_date)->format('d-M-Y') }}</p>
              </div>
            </div>

            <!-- Sandal Issue Date -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Sandal Issue Date</h5>
                <p>{{ \Carbon\Carbon::parse($driver->sandal_issue_date)->format('d-M-Y') }}</p>
              </div>
            </div>

            <!-- Address -->
            <div class="col-md-3 text-center">
              <div class="card p-2">
                <h5 class="m-0">Address</h5>
                <p>{{$driver->address}}</p>
              </div>
            </div>
          </div>

          

          
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="{{ route('admin.drivers.edit', $driver->id) }}" class="btn btn-warning">Edit</a>
              <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">Back</a>
              <form action="{{ route('admin.drivers.destroy', $driver->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this driver?');">
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
