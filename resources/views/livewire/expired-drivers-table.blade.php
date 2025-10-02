<div>
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h5><i class="icon-user mr-2"></i> <span class="font-weight-semibold">Drivers with Expired Documents</span></h5>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <span class="badge badge-danger mr-2">{{ count($expiredDrivers) }} Expired</span>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div>
        @if(count($expiredDrivers) > 0)
            <!-- Basic datatable -->
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped table-hover table-sm" id="drivers-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="font-size-sm">Serial No</th>
                                <th class="font-size-sm">Name</th>
                                <th class="font-size-sm">Status</th>
                                <th class="font-size-sm">Reason</th>
                                <th class="text-center font-size-sm">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiredDrivers as $driver)
                                <tr>
                                    <td class="font-size-sm">{{ $driver['serial_no'] }}</td>
                                    <td class="font-size-sm">{{ $driver['name'] }}</td>
                                    <td>
                                        <span class="badge badge-warning badge-sm">{{ $driver['status'] }}</span>
                                    </td>
                                    <td class="font-size-sm">
                                        <span class="text-danger">{{ $driver['reason'] }}</span>
                                    </td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('admin.drivers.edit', $driver['id']) }}" class="dropdown-item font-size-sm">
                                                    <i class="icon-pencil7"></i> Edit Driver
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
                    <h4 class="text-muted mt-3">All drivers have valid documents!</h4>
                    <p class="text-muted">No expired documents found.</p>
                </div>
            </div>
        @endif
    </div>
    <!-- /content area -->
</div>
