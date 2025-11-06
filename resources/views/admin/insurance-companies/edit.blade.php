@extends('layouts.admin')

@section('title', 'Edit Insurance Company')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Insurance Company</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.insurance-companies.index') }}" class="btn btn-primary">
            <span>View Insurance Companies <i class="icon-list ml-2"></i></span>
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
            <form action="{{ route('admin.insurance-companies.update', $insurance_company->id) }}" method="POST">
              @csrf
              @method('PUT')
              
              <div class="row">
                <!-- Serial No -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Serial No.</strong>
                    <input value="{{ $insurance_company->serial_no }}" type="text" class="form-control" readonly>
                  </div>
                </div>

                <!-- Name -->
                <div class="col-md-6">
                  <div class="form-group">
                    <strong>Name <span class="text-danger">*</span></strong>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           name="name" value="{{ old('name', $insurance_company->name) }}" required>
                    @error('name')
                      <label class="text-danger">{{ $message }}</label>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                      <i class="icon-check"></i> Update
                    </button>
                    <a href="{{ route('admin.insurance-companies.index') }}" class="btn btn-warning">
                      <i class="icon-cross2"></i> Cancel
                    </a>
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