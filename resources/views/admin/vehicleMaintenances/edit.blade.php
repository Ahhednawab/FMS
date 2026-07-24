@extends('layouts.admin')

@section('title', 'Edit Vehicle Maintenance')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Vehicle Maintenance</span></h4>
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
                <form method="POST" action="{{ route('vehicleMaintenances.update', $vehicleMaintenance->id) }}" id="maintenance-form">
                    @csrf
                    @method('PUT')

                    @include('admin.vehicleMaintenances.partials.form', [
                        {{-- Grouped by warehouse + product, since each row now
                             carries its own warehouse. --}}
                        'selectedParts' => $vehicleMaintenance->maintenanceParts
                            ->groupBy(fn ($part) => $part->warehouse_id . '|' . $part->product_id)
                            ->map(function ($rows) {
                                return [
                                    'warehouse_id' => $rows->first()->warehouse_id,
                                    'product_id' => $rows->first()->product_id,
                                    'product_name' => $rows->first()->product?->name ?? 'Unknown Product',
                                    'quantity' => $rows->sum('quantity'),
                                    'unit_price' => $rows->first()->unit_price,
                                ];
                            })
                            ->values(),
                    ])

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Update</button>
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
