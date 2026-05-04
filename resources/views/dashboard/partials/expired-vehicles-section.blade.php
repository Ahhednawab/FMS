<div class="expired-vehicles-section">
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h5>
                    <i class="icon-truck mr-2"></i>
                    <span class="font-weight-semibold">Vehicles with Expired Dates</span>
                </h5>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>

            <div class="header-elements d-none">
                <div class="d-flex justify-content-center align-items-center">
                    <span class="badge badge-danger mr-2">{{ $expiredVehicles->total() }} Expired</span>

                    <a href="{{ route('dashboard.expiredVehicles.export', ['reason' => $filters['reason'] ?? '', 'search' => $filters['search'] ?? '']) }}"
                        class="btn btn-success btn-sm mx-1">
                        <i class="icon-file-excel mr-1"></i> Export Report
                    </a>

                    <button type="button" class="btn btn-sm btn-info js-open-all-vehicles-modal" data-toggle="modal"
                        data-target="#allVehiclesModal">
                        View All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div>
        @if ($expiredVehicles->total() > 0)
            <div class="card" style="min-height: 660px;">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Filter by Reason:</label>
                            <select class="form-control form-control-sm js-vehicle-reason">
                                <option value="">All Reasons</option>
                                @foreach ($reasonList as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ ($filters['reason'] ?? '') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-12 d-flex align-items-end mt-2 mt-md-0">
                            <button type="button" class="btn btn-secondary btn-sm w-100 js-clear-vehicle-filters">
                                Clear Filters
                            </button>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Search:</label>
                            <input type="text" class="form-control js-vehicle-search" placeholder="Search vehicles..."
                                value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm" id="vehicles-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="font-size-sm">Serial No</th>
                                    <th class="font-size-sm">Vehicle No</th>
                                    <th class="font-size-sm">Model</th>
                                    <th class="font-size-sm">Type</th>
                                    <th class="font-size-sm">Station</th>
                                    <th class="font-size-sm">Reason</th>
                                    <th class="font-size-sm">Expiry Date</th>
                                    <th class="text-center font-size-sm">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expiredVehicles as $vehicle)
                                    <tr>
                                        <td class="font-size-sm" data-label="Serial No">{{ str_pad($vehicle->id, 9, '0', STR_PAD_LEFT) }}</td>
                                        <td class="font-size-sm" data-label="Vehicle No">{{ $vehicle->vehicle_no }}</td>
                                        <td class="font-size-sm" data-label="Model">{{ $vehicle->model }}</td>
                                        <td class="font-size-sm" data-label="Type">{{ $vehicle->vehicle_type_name }}</td>
                                        <td class="font-size-sm" data-label="Station">{{ $vehicle->station_area }}</td>
                                        <td class="font-size-sm text-danger" data-label="Reason">{{ $vehicle->reason }}</td>
                                        <td class="font-size-sm text-danger" data-label="Expiry Date">{{ $vehicle->date }}</td>
                                        <td class="text-center" data-label="Actions">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                        <i class="icon-menu9"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}"
                                                            class="dropdown-item font-size-sm">
                                                            <i class="icon-pencil7"></i> Edit Vehicle
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted small">
                            Showing {{ $expiredVehicles->firstItem() }} to {{ $expiredVehicles->lastItem() }}
                            of {{ $expiredVehicles->total() }} results
                        </div>
                    </div>
                    <div class="expired-vehicles-pagination">
                        {{ $expiredVehicles->links('vendor.pagination.ajax-short') }}
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Filter by Reason:</label>
                            <select class="form-control form-control-sm js-vehicle-reason">
                                <option value="">All Reasons</option>
                                @foreach ($reasonList as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ ($filters['reason'] ?? '') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-12 d-flex align-items-end mt-2 mt-md-0">
                            <button type="button" class="btn btn-secondary btn-sm w-100 js-clear-vehicle-filters">
                                Clear Filters
                            </button>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Search:</label>
                            <input type="text" class="form-control js-vehicle-search" placeholder="Search vehicles..."
                                value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="text-center py-5">
                        <i class="icon-checkmark-circle text-success" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">All vehicles have valid dates!</h4>
                        <p class="text-muted">No expired dates found.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="allVehiclesModal" tabindex="-1" role="dialog" aria-labelledby="allVehiclesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="allVehiclesModalLabel">Expired Vehicles List</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body p-0" style="overflow-x: auto; max-height: 70vh;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Serial No</th>
                                    <th>Vehicle No</th>
                                    <th>Model</th>
                                    <th>Type</th>
                                    <th>Station</th>
                                    <th>Reason</th>
                                    <th>Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expiredVehicles as $vehicle)
                                    <tr>
                                        <td>{{ str_pad($vehicle->id, 9, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $vehicle->vehicle_no }}</td>
                                        <td>{{ $vehicle->model }}</td>
                                        <td>{{ $vehicle->vehicle_type_name }}</td>
                                        <td>{{ $vehicle->station_area }}</td>
                                        <td class="text-danger">{{ $vehicle->reason }}</td>
                                        <td class="text-danger">{{ $vehicle->date }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No expired dates found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center my-3 expired-vehicles-modal-pagination">
                        {{ $expiredVehicles->links('vendor.pagination.ajax-short') }}
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
