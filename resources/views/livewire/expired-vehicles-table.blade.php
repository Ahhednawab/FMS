<div>
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h5><i class="icon-truck mr-2"></i>
                    <span class="font-weight-semibold">Vehicles with Expired Dates</span>
                </h5>
                <a href="#" class="header-elements-toggle text-body d-lg-none">
                    <i class="icon-more"></i>
                </a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center align-items-center">
                    <span class="badge badge-danger mr-2">{{ $expiredVehicles->total() }} Expired</span>
                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#allVehiclesModal">
                        View All
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->


    <div>
        @if($expiredVehicles->total() > 0)
            <div class="card">
                <div class="card-body">

                    <!-- ✅ Date Filters above the table -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="font-size-sm">From Date:</label>
                            <input type="date" class="form-control form-control-sm"
                                   wire:model.lazy="fromDate" wire:change="filterVehicles">
                        </div>
                        <div class="col-md-3">
                            <label class="font-size-sm">To Date:</label>
                            <input type="date" class="form-control form-control-sm"
                                   wire:model.lazy="toDate" wire:change="filterVehicles">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button wire:click="clearFilters" class="btn btn-secondary btn-sm w-100">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                    <!-- /Filters -->

                    <table class="table table-striped table-hover table-sm" id="vehicles-table">
                        <thead class="thead-light">
                        <tr>
                            <th class="font-size-sm">Serial No</th>
                            <th class="font-size-sm">Vehicle No</th>
                            <th class="font-size-sm">Model</th>
                            <th class="font-size-sm">Type</th>
                            <th class="font-size-sm">Station</th>
                            <th class="font-size-sm">Reason</th>
                            <th class="text-center font-size-sm">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($expiredVehicles as $vehicle)
                            <tr>
                                <td class="font-size-sm">{{ $vehicle['serial_no'] }}</td>
                                <td class="font-size-sm">{{ $vehicle['vehicle_no'] }}</td>
                                <td class="font-size-sm">{{ $vehicle['model'] }}</td>
                                <td class="font-size-sm">{{ $vehicle['type'] }}</td>
                                <td class="font-size-sm">{{ $vehicle['station'] }}</td>
                                <td class="font-size-sm">
                                    <span class="text-danger">{{ $vehicle['reason'] }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('admin.vehicles.edit', $vehicle['id']) }}"
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

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $expiredVehicles->links() }}
                    </div>

                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="icon-checkmark-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">All vehicles have valid dates!</h4>
                    <p class="text-muted">No expired dates found.</p>
                </div>
            </div>
        @endif
    </div>


    <!-- Modal for showing all expired vehicles -->
    <div class="modal fade" id="allVehiclesModal" tabindex="-1" role="dialog"
         aria-labelledby="allVehiclesModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="allVehiclesModalLabel">Expired Vehicles List</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
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
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($expiredVehicles as $vehicle)
                                <tr>
                                    <td>{{ $vehicle['serial_no'] }}</td>
                                    <td>{{ $vehicle['vehicle_no'] }}</td>
                                    <td>{{ $vehicle['model'] }}</td>
                                    <td>{{ $vehicle['type'] }}</td>
                                    <td>{{ $vehicle['station'] }}</td>
                                    <td><span class="text-danger">{{ $vehicle['reason'] }}</span></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center my-3">
                        {{ $expiredVehicles->links() }}
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#allVehiclesModal').on('hidden.bs.modal', function () {
            const cleanUrl = '/admin/dashboard';
            window.history.replaceState({}, document.title, cleanUrl);
            setTimeout(() => {
                window.location.reload();
            }, 100);
        });
    });
</script>
