@extends('layouts.admin')

@section('title', 'Edit Vehicle Maintenance Configuration')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i>
                    <span class="font-weight-semibold">Vehicle Maintenance Configuration</span>
                </h4>
            </div>
            <div class="header-elements d-none">
                <a href="{{ route('vehicleMaintenanceConfigurations.index') }}" class="btn btn-primary">
                    View Configurations <i class="icon-list ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('vehicleMaintenanceConfigurations.update', $configuration->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @include('admin.vehicleMaintenanceConfigurations.partials.form')

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('vehicleMaintenanceConfigurations.index') }}" class="btn btn-warning">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
