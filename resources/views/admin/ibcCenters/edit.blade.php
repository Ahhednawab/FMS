@extends('layouts.admin')

@section('title', 'Edit IBC Center')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit IBC Center</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('ibcCenters.index') }}" class="btn btn-primary">
                        <span>View IBC Centers <i class="icon-list ml-2"></i></span>
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
                        <form method="POST" action="{{ route('ibcCenters.update', $ibcCenter->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Serial No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Serial NO</strong>
                                        <input type="text" name="serial_no" class="form-control"
                                            value="{{ $ibcCenter->serial_no }}" readonly>
                                    </div>
                                </div>

                                <!-- Station -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Station</strong>
                                        <select class="custom-select" name="station_id">
                                            <option value="">Select Station</option>
                                            @foreach ($stations as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('station_id', $ibcCenter->station_id ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
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
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $ibcCenter->name ?? '') }}">
                                        @if ($errors->has('name'))
                                            <label class="text-danger">{{ $errors->first('name') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-3">
                                    <label for=""></label>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <a href="{{ route('ibcCenters.index') }}" class="btn btn-warning">Cancel</a>
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
