@extends('layouts.admin')

@section('title', 'Warehouse List')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Warehouse List</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
                        <span>Add Warehouse <i class="icon-plus3 ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div class="content">
        @if ($message = Session::get('success'))
            <div id="alert-message" class="alert alert-success alert-dismissible alert-dismissible-2" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path class="heroicon-ui"
                            d="M16.24 14.83a1 1 0 0 1-1.41 1.41L12 13.41l-2.83 2.83a1 1 0 0 1-1.41-1.41L10.59 12 7.76 9.17a1 1 0 0 1 1.41-1.41L12 10.59l2.83-2.83a1 1 0 0 1 1.41 1.41L13.41 12l2.83 2.83z">
                        </path>
                    </svg>
                </button>
            </div>
        @elseif ($message = Session::get('delete_msg'))
            <div id="alert-message" class="alert alert-danger alert-dismissible alert-dismissible-2" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path class="heroicon-ui"
                            d="M16.24 14.83a1 1 0 0 1-1.41 1.41L12 13.41l-2.83 2.83a1 1 0 0 1-1.41-1.41L10.59 12 7.76 9.17a1 1 0 0 1 1.41-1.41L12 10.59l2.83-2.83a1 1 0 0 1 1.41 1.41L13.41 12l2.83 2.83z">
                        </path>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Master Warehouse Status Banner --}}
        @if ($masterWarehouse)
            <div class="alert alert-success d-flex align-items-center justify-content-between" role="alert"
                style="border-left: 4px solid #28a745;">
                <div>
                    <i class="icon-shield-check mr-2" style="font-size:1.1rem;"></i>
                    <strong>Master Warehouse:</strong>
                    <span class="ml-1 badge badge-success" style="font-size:0.85rem;">{{ $masterWarehouse->name }}</span>
                    <small class="text-muted ml-2">(Serial: {{ $masterWarehouse->serial_no }})</small>
                    <span class="ml-2 text-muted small">— All new inventory is received here first.</span>
                </div>
                <a href="{{ route('warehouses.show', $masterWarehouse->id) }}" class="btn btn-sm btn-outline-success">
                    View Master
                </a>
            </div>
        @else
            <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert"
                style="border-left: 4px solid #ffc107;">
                <div>
                    <i class="icon-warning2 mr-2"></i>
                    <strong>No Master Warehouse configured.</strong>
                    <span class="ml-1 text-muted small">Use the "Set as Master" action on any warehouse below, or create a new warehouse and mark it as Master.</span>
                </div>
                <a href="{{ route('warehouses.create') }}" class="btn btn-sm btn-warning">
                    Create Master Warehouse
                </a>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table datatable-colvis-basic">
                    <thead>
                        <tr>
                            <th>Serial No</th>
                            <th>Warehouse Name</th>
                            <th>Role</th>
                            <th>Warehouse Manager</th>
                            <th>Station</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($warehouses as $key => $value)
                            <tr>
                                <td>{{ $value->serial_no }}</td>
                                <td>
                                    {{ $value->name }}
                                    @if ($value->is_master)
                                        <i class="icon-star-full2 text-warning ml-1" title="Master Warehouse"></i>
                                    @endif
                                </td>
                                <td>
                                    @if ($value->is_master)
                                        <span class="badge badge-success">
                                            <i class="icon-star-full2 mr-1"></i> Master Warehouse
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">Sub-Warehouse</span>
                                    @endif
                                </td>
                                <td>{{ $value->manager?->name ?? 'N/A' }}</td>
                                <td>{{ $value->station?->area ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('warehouses.show', $value->id) }}" class="dropdown-item">
                                                    <i class="icon-file-eye"></i> View Details
                                                </a>
                                                <a href="{{ route('warehouses.edit', $value->id) }}" class="dropdown-item">
                                                    <i class="icon-pencil7"></i> Edit
                                                </a>

                                                {{-- Quick Set as Master (only shown for sub-warehouses) --}}
                                                @if (!$value->is_master)
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('warehouses.setMaster', $value->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Set \'{{ $value->name }}\' as the Master Warehouse?\n\nThe current master will become a Sub-Warehouse.');">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i class="icon-star-full2"></i> Set as Master Warehouse
                                                        </button>
                                                    </form>
                                                    <div class="dropdown-divider"></div>
                                                @endif

                                                <form action="{{ route('warehouses.destroy', $value->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="icon-trash"></i> Delete
                                                    </button>
                                                </form>
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
    </div>
    <!-- /content area -->

    <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>
    <script src="{{ asset('assets/js/demo_pages/datatables_extension_colvis.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.datatable-colvis-basic').DataTable();
        });

        setTimeout(function() {
            let alertBox = document.getElementById('alert-message');
            if (alertBox) {
                alertBox.style.transition = 'opacity 0.5s ease';
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }
        }, 3000);
    </script>
@endsection
