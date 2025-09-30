@extends('layouts.admin')

@section('title', 'Create City')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i>
          <span class="font-weight-semibold">
            {{ isset($city) ? 'Edit City' : 'City Management' }}
          </span>
        </h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>

      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.cities.index') }}" class="btn btn-primary">
            <span>View Cities <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- /page header -->

  <!-- Content area -->
  <div class="content">
    <div class="row">
      <div class="col-lg-12">
        <!-- Basic layout -->
        <div class="card">
          <div class="card-body">
        <form method="POST" action="{{ route('admin.cities.store') }}">
          @csrf
          @if(isset($draftId))
            <input type="hidden" name="draft_id" value="{{ $draftId }}">
          @endif

              <div class="row">
                <!-- Serial No -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Serial NO</strong>
                    <input type="text" name="serial_no" class="form-control" value="{{$serial_no}}" readonly>
                  </div>
                </div>

                <!-- City -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>City</strong>
                    <input type="text" name="name" value="{{ $draftData['name'] ?? old('name') }}" class="form-control">
                    @error('name')
                      <label class="text-danger">{{ $message }}</label>
                    @enderror
                  </div>
                </div>

                <!-- Buttons -->
                <div class="col-md-6 mt-4 text-right">
                  <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                    <i class="icon-save"></i> Draft
                  </button>
                  <button type="submit" class="btn btn-primary">
                    <i class="icon-check"></i> Save
                  </button>
                  <a href="{{ route('admin.cities.index') }}" class="btn btn-warning">Cancel</a>                  
                </div>
              </div>
            </form>
          </div>
        </div>
        <!-- /basic layout -->
      </div>
    </div>
  </div>
  <!-- /content area -->
@endsection