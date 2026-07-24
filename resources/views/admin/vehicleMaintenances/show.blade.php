@extends('layouts.admin')

@section('title', 'Vehicle Maintenance Detail')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Maintenance Detail</span></h4>
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
                <div class="row">
                    <div class="col-md-3 mb-3"><strong>Date</strong><p>{{ optional($vehicleMaintenance->service_date)->format('d M Y') }}</p></div>
                    <div class="col-md-3 mb-3"><strong>Vehicle Number</strong><p>{{ $vehicleMaintenance->vehicle?->vehicle_no ?? 'N/A' }}</p></div>
                    <div class="col-md-3 mb-3"><strong>Vehicle Make</strong><p>{{ $vehicleMaintenance->vehicle_make ?? 'N/A' }}</p></div>
                    <div class="col-md-3 mb-3"><strong>Vehicle Model</strong><p>{{ $vehicleMaintenance->model ?? 'N/A' }}</p></div>
                    <div class="col-md-3 mb-3"><strong>Current Mileage</strong><p>{{ $vehicleMaintenance->odometer_reading ?? 'N/A' }}</p></div>
                    <div class="col-md-3 mb-3"><strong>Maintenance Type</strong><p>{{ ucwords(str_replace('_', ' ', $vehicleMaintenance->maintenance_type)) }}</p></div>
                    <div class="col-md-3 mb-3"><strong>Work Done</strong>
                        <p>
                            @forelse ($vehicleMaintenance->workDoneNames() as $workDoneName)
                                <span class="badge badge-light border mr-1 mb-1">{{ $workDoneName }}</span>
                            @empty
                                N/A
                            @endforelse
                        </p>
                    </div>
                    <div class="col-md-3 mb-3"><strong>Workshop</strong><p>{{ $vehicleMaintenance->workshop?->name ?? 'N/A' }}</p></div>
                    <div class="col-md-3 mb-3"><strong>Labor / Service Charges</strong><p>Rs. {{ number_format($vehicleMaintenance->labor_cost, 2) }}</p></div>
                    <div class="col-md-3 mb-3"><strong>Amount</strong><p>Rs. {{ number_format($vehicleMaintenance->service_cost, 2) }}</p></div>
                    <div class="col-md-3 mb-3"><strong>Created By</strong><p>{{ $vehicleMaintenance->createdBy?->name ?? 'N/A' }}</p></div>
                </div>

                @if(!empty($vehicleMaintenance->remarks))
                    <div class="row mt-3">
                        <div class="col-md-12 mb-3">
                            <strong>Remarks</strong>
                            <p>{{ $vehicleMaintenance->remarks }}</p>
                        </div>
                    </div>
                @endif

                <h5 class="mt-3">Parts Used</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th>Product</th>
                                <th>Quantity Used</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vehicleMaintenance->maintenanceParts->groupBy(fn ($part) => $part->warehouse_id . '|' . $part->product_id) as $rows)
                                <tr>
                                    <td>{{ $rows->first()->warehouse?->name ?? 'N/A' }}</td>
                                    <td>{{ $rows->first()->product?->name ?? 'N/A' }}</td>
                                    <td>{{ $rows->sum('quantity') + 0 }} {{ $rows->first()->product?->unit?->name }}</td>
                                    <td>Rs. {{ number_format($rows->first()->unit_price, 2) }}</td>
                                    <td>Rs. {{ number_format($rows->sum('total_price'), 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No parts used.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <a href="{{ route('vehicleMaintenances.edit', $vehicleMaintenance->id) }}" class="btn btn-warning">Edit</a>
                    <a href="{{ route('vehicleMaintenances.index') }}" class="btn btn-secondary">Back</a>
                    <form action="{{ route('vehicleMaintenances.destroy', $vehicleMaintenance->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Vehicle Maintenance?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
