@extends('layouts.admin')

@section('title', 'Add Accident Details')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Accident Details Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.accidentDetails.index') }}" class="btn btn-primary">
            <span>View Accident Details <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.accidentDetails.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>Accident ID</label>
                <input value="{{$accident_id}}" name="accident_id" type="text" class="form-control" readonly>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label>Accident Type</label>
                <select class="custom-select" name="accident_type">
                  <option value="">--Select--</option>
                  @foreach($accident_types as $key => $value)
                    <option value="{{$key}}" {{ old('accident_type') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('accident_type'))
                  <label class="text-danger">{{ $errors->first('accident_type') }}</label>
                @endif
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label>Location</label>
                <input type="text" class="form-control" name="location" value="{{ old('location') }}">
                @if ($errors->has('location'))
                  <label class="text-danger">{{ $errors->first('location') }}</label>
                @endif
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label>Accident Date</label>
                <input type="date" class="form-control" name="accident_date" value="{{ old('accident_date') }}">
                @if ($errors->has('accident_date'))
                  <label class="text-danger">{{ $errors->first('accident_date') }}</label>
                @endif
              </div>
            </div>            
          </div>

          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <div class="form-group">
                  <label>Accident Time</label>
                  <input type="time" class="form-control" name="accident_time" value="{{ old('accident_time') }}">
                  @if ($errors->has('accident_time'))
                    <label class="text-danger">{{ $errors->first('accident_time') }}</label>
                  @endif
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Accident Description</label>
                <input type="text" class="form-control" name="accident_description" value="{{ old('accident_description') }}">
                @if ($errors->has('accident_description'))
                  <label class="text-danger">{{ $errors->first('accident_description') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Person Involved</label>
                <select class="custom-select" name="person_involved">
                  <option value="">--Select--</option>
                  @foreach($users as $key => $value)
                    <option value="{{$key}}" {{ old('person_involved') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('person_involved'))
                  <label class="text-danger">{{ $errors->first('person_involved') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Injury Type</label>
                <select class="custom-select" name="injury_type">
                  <option value="">--Select--</option>
                  @foreach($injury_types as $key => $value)
                    <option value="{{$key}}" {{ old('injury_type') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('injury_type'))
                  <label class="text-danger">{{ $errors->first('injury_type') }}</label>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>Damage Type*</label>
                <select class="custom-select" name="damage_type">
                  <option value="">--Select--</option>
                  @foreach($damage_types as $key => $value)
                    <option value="{{$key}}" {{ old('damage_type') == $key ? 'selected' : '' }}>{{$value}}</option>
                  @endforeach
                </select>
                @if ($errors->has('damage_type'))
                  <label class="text-danger">{{ $errors->first('damage_type') }}</label>
                @endif
              </div>
            </div>
            <div class="col-md-9">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.accidentDetails.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
