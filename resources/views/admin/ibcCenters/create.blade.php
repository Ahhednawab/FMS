@extends('layouts.admin')

@section('title', 'Create IBC Center')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Create IBC Center</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.ibcCenters.index') }}" class="btn btn-primary">
            <span>View IBC Center <i class="icon-list ml-2"></i></span>
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
            <form method="POST" action="{{ route('admin.ibcCenters.store') }}">
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

                <!-- Station -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Station</strong>
                    <select class="custom-select" name="station_id">
                      <option value="">Select Station</option>
                      @foreach($stations as $key => $value)
                        <option value="{{$key}}" {{ ($draftData['station_id'] ?? old('station_id')) == $key ? 'selected' : '' }}>{{$value}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('station_id'))
                      <label class="text-danger">{{ $errors->first('station_id') }}</label>
                    @endif
                  </div>
                </div>

                <!-- IBC Center Name -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>IBC Center Name</strong>
                    <input type="text" name="name" class="form-control" value="{{ $draftData['name'] ?? old('name') }}">
                    @if ($errors->has('name'))
                      <label class="text-danger">{{ $errors->first('name') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Buttons -->
                <div class="col-md-3">
                  <label for=""></label>
                  <div class="text-right">
                    <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                      <i class="icon-save"></i> Draft
                    </button>
                    <button type="submit" class="btn btn-primary">
                      <i class="icon-check"></i> Save
                    </button>
                    <a href="{{ route('admin.ibcCenters.index') }}" class="btn btn-warning">Cancel</a>
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