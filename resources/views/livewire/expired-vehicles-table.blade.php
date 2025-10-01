<div>
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h5><i class="icon-truck mr-2"></i> <span class="font-weight-semibold">Vehicles with Expired Dates</span></h5>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <span class="badge badge-danger mr-2">{{ count($expiredVehicles) }} Expired</span>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div>
        @if(count($expiredVehicles) > 0)
            <!-- Basic datatable -->
            <div class="card">
                <div class="card-body">
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
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('admin.vehicles.edit', $vehicle['id']) }}" class="dropdown-item font-size-sm">
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
            </div>
            <!-- /basic datatable -->
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
    <!-- /content area -->
</div>
