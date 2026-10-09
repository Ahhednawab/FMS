<div class="expired-drivers-section">
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h5>
                    <i class="icon-user mr-2"></i>
                    <span class="font-weight-semibold">Drivers with Expired Documents</span>
                </h5>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>

            <div class="header-elements d-none">
                <div class="d-flex justify-content-center align-items-center">
                    <span class="badge badge-danger mr-2">{{ $expiredDrivers->total() }} Expired</span>

                    <a href="{{ route('dashboard.expiredDrivers.export', ['filter_reason' => $filters['filter_reason'] ?? '', 'search' => $filters['search'] ?? '']) }}"
                        class="btn btn-success btn-sm mx-1">
                        <i class="icon-file-excel mr-1"></i> Export Report
                    </a>

                    <a href="{{ route('alerts.index', ['type' => 'expired_drivers', 'title' => $filters['filter_reason'] ?? '', 'search' => $filters['search'] ?? '']) }}"
                        class="btn btn-sm btn-info">
                        View All
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div>
        @if ($expiredDrivers->total() > 0)
            <div class="card" style="min-height: 660px;">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Filter by Reason:</label>
                            <select class="form-control form-control-sm js-driver-reason">
                                <option value="">All Reasons</option>
                                @foreach ($reasonList as $reason)
                                    <option value="{{ $reason }}"
                                        {{ ($filters['filter_reason'] ?? '') === $reason ? 'selected' : '' }}>
                                        {{ $reason }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-12 d-flex align-items-end mt-2 mt-md-0">
                            <button type="button" class="btn btn-secondary btn-sm w-100 js-clear-driver-filters">
                                Clear Filters
                            </button>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Search:</label>
                            <input type="text" class="form-control form-control-sm js-driver-search"
                                placeholder="Search drivers..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm" id="drivers-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="font-size-sm">Serial No</th>
                                    <th class="font-size-sm">Name</th>
                                    <th class="font-size-sm">CNIC</th>
                                    <th class="font-size-sm">Status</th>
                                    <th>Reason</th>
                                    <th>Expiry Date</th>
                                    <th class="text-center font-size-sm">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expiredDrivers as $driver)
                                    <tr>
                                        <td class="font-size-sm" data-label="Serial No">{{ $driver['serial_no'] }}</td>
                                        <td class="font-size-sm" data-label="Name">{{ $driver['name'] }}</td>
                                        <td class="font-size-sm" data-label="CNIC">{{ $driver['cnic_no'] }}</td>
                                        <td class="font-size-sm">
                                            <span class="badge badge-warning badge-sm">{{ $driver['status'] }}</span>
                                        </td>
                                        <td class="text-danger">{{ $driver['reason'] }}</td>
                                        <td class="text-danger">{{ $driver['date'] }}</td>
                                        <td class="text-center" data-label="Actions">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                        <i class="icon-menu9"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a href="{{ route('admin.drivers.edit', $driver['id']) }}"
                                                            class="dropdown-item font-size-sm">
                                                            Edit Driver
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

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ $expiredDrivers->firstItem() }} to {{ $expiredDrivers->lastItem() }}
                            of {{ $expiredDrivers->total() }} results
                        </div>
                    </div>
                    <div class="expired-drivers-pagination">
                        {{ $expiredDrivers->links('vendor.pagination.ajax-short') }}
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Filter by Reason:</label>
                            <select class="form-control form-control-sm js-driver-reason">
                                <option value="">All Reasons</option>
                                @foreach ($reasonList as $reason)
                                    <option value="{{ $reason }}"
                                        {{ ($filters['filter_reason'] ?? '') === $reason ? 'selected' : '' }}>
                                        {{ $reason }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-12 d-flex align-items-end mt-2 mt-md-0">
                            <button type="button" class="btn btn-secondary btn-sm w-100 js-clear-driver-filters">
                                Clear Filters
                            </button>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Search:</label>
                            <input type="text" class="form-control form-control-sm js-driver-search"
                                placeholder="Search drivers..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="text-center py-5">
                        <i class="icon-checkmark-circle text-success" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">All drivers have valid documents!</h4>
                        <p class="text-muted">No expired documents found.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>
