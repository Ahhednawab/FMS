@extends('layouts.admin')

@section('title', 'Vehicle Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.vehicles.index') }}" class="btn btn-primary"><span>View Vehicles <i class="icon-list ml-2"></i></span></a>
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
            <!-- Serial NO -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Serial No</h5>
                <p>{{$vehicle->serial_no}}</p>
              </div>
            </div>

            <!-- Vehicle No -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Vehicle No</h5>
                <p>{{$vehicle->vehicle_no}}</p>
              </div>
            </div>

            <!-- Make (Manufacturer) -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Make (Manufacturer)</h5>
                <p>{{$vehicle->make}}</p>
              </div>
            </div>

            <!-- Model (Year) -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Model (Year)</h5>
                <p>{{$vehicle->model}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Chasis No -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Chasis No</h5>
                <p>{{$vehicle->chasis_no}}</p>
              </div>
            </div>

            <!-- Engine No -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Engine No</h5>
                <p>{{$vehicle->engine_no}}</p>
              </div>
            </div>

            <!-- Ownership -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Ownership</h5>
                <p>{{$vehicle->ownership}}</p>
              </div>
            </div>

            <!-- Cone -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Cone</h5>
                <p>{{$vehicle->cone}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- PSO Card Details -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">PSO Card Details</h5>
                <p>{{$vehicle->pso_card}}</p>
              </div>
            </div>

            <!-- AKPL -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">AKPL</h5>
                <p>{{$vehicle->akpl}}</p>
              </div>
            </div>

            <!-- Shift Hours -->
            <div class="col-md-2 text-center">
              <div class="card">
                <h5 class="m-0">Shift Hours</h5>
                <p>{{$vehicle->shiftHours->name}}</p>
              </div>
            </div>

            <!-- Vehicle Type -->
            <div class="col-md-2 text-center">
              <div class="card">
                <h5 class="m-0">Vehicle Type</h5>
                <p>{{$vehicle->vehicleType->name}}</p>
              </div>
            </div>

            <!-- Fabrication Vendor -->
            <div class="col-md-2 text-center">
              <div class="card">
                <h5 class="m-0">Fabrication Vendor</h5>
                <p>{{$vehicle->fabricationVendor ? $vehicle->fabricationVendor->name : 'N/A'}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Station -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Station</h5>
                <p>{{$vehicle->station->area}}</p>
              </div>
            </div>

            <!-- IBC Center -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">IBC Center</h5>
                <p>{{$vehicle->ibcCenter->name}}</p>
              </div>
            </div>

            <!-- Medical Box -->
{{--            <div class="col-md-2 text-center">--}}
{{--              <div class="card">--}}
{{--                <h5 class="m-0">Medical Boxxxx</h5>--}}
{{--                <p>{{$vehicle->medical_box == 1 ? 'Yes' : 'No'}}</p>--}}
{{--              </div>--}}
{{--            </div>--}}

            <!-- Seat Cover -->
            <div class="col-md-2 text-center">
              <div class="card">
                <h5 class="m-0">Seat Cover</h5>
                <p>{{$vehicle->seat_cover == 1 ? 'Yes' : 'No'}}</p>
              </div>
            </div>

            <!-- Fire Extinguisher -->
            <div class="col-md-2 text-center">
              <div class="card">
                <h5 class="m-0">Fire Extinguisher</h5>
                <p>{{$vehicle->fire_exteinguisher == 1 ? 'Yes' : 'No'}}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Tracker Installation Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Tracker Installation Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->tracker_installation_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Inspection Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Inspection Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->inspection_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Next Inspection Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Next Inspection Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->next_inspection_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Induction Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Induction Date</h5>
                <p>{{ ($vehicle->induction_date && $vehicle->induction_date !== '0000-00-00')  ? \Carbon\Carbon::parse($vehicle->induction_date)->format('d-M-Y')  : 'N/A' }} </p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Fitness Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Fitness Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->fitness_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Next fitness date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Next fitness date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->next_fitness_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Fitness Attachment -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Fitness Attachment</h5>
                <p><a href="{{ asset('uploads/vehicles/' . $vehicle->fitness_file) }}" download> Download</a></p>
              </div>
            </div>

            <!-- Registration Attachment -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Registration Attachment</h5>
                <p><a href="{{ asset('uploads/vehicles/' . $vehicle->registration_file) }}" download> Download</a></p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Insurance Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Insurance Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->insurance_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Insurance Expiry Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Insurance Expiry Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->insurance_expiry_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Insurance Attachment -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Insurance Attachment</h5>
                <p><a href="{{ asset('uploads/vehicles/' . $vehicle->insurance_file) }}" download> Download</a></p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Route Permit Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Route Permit Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->route_permit_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Route Permit Expiry Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Route Permit Expiry Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->route_permit_expiry_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Route Permit Attachment -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Route Permit Attachment</h5>
                <p><a href="{{ asset('uploads/vehicles/' . $vehicle->route_permit_file) }}" download> Download</a></p>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Tax Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Route Permit Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->tax_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Next Tax Date -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Next Tax Date</h5>
                <p>{{\Carbon\Carbon::parse($vehicle->next_tax_date)->format('d-M-Y')}}</p>
              </div>
            </div>

            <!-- Tax Attachment -->
            <div class="col-md-3 text-center">
              <div class="card">
                <h5 class="m-0">Tax Attachment</h5>
                <p><a href="{{ asset('uploads/vehicles/' . $vehicle->tax_file) }}" download> Download</a></p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-12">
          <label for=""></label>
          <div class="text-right">
            <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Back</a>
            <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Vehicle?');">
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
  <!-- /content area -->
@endsection
