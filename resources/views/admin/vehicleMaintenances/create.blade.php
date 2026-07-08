@extends('layouts.admin')

@section('title', 'Add Vehicle Maintenance')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Maintenance</span></h4>
            </div>
            <div class="header-elements d-none">
                <a href="{{ route('vehicleMaintenances.index') }}" class="btn btn-primary">
                    View Vehicle Maintenance <i class="icon-list ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('vehicleMaintenances.store') }}" method="POST" id="maintenance-form">
                    @csrf
                    <input type="hidden" name="maintenance_id" value="{{ $maintenance_id }}">

                    @include('admin.vehicleMaintenances.partials.form', [
                        'vehicleMaintenance' => null,
                        'selectedParts' => [],
                    ])

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('vehicleMaintenances.index') }}" class="btn btn-warning">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('admin.vehicleMaintenances.partials.form-scripts')
@endpush
