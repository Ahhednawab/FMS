@extends('layouts.admin')

@section('title', 'Dashboard')

@livewireStyles

<style>
    .content-wrapper {
        overflow-y: auto;
        height: calc(100vh - 60px);
    }

    .page-content {
        min-height: 100%;
        overflow-y: auto;
    }

    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }

    .dataTables_wrapper {
        overflow-x: auto;
    }

    .dataTables_paginate {
        margin-top: 10px;
    }

    .table td,
    .table th {
        white-space: nowrap;
        word-wrap: break-word;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table td:hover,
    .table th:hover {
        white-space: normal;
        word-wrap: break-word;
        max-width: none;
    }

    .navbar-collapse {
        display: block !important;
    }

    .navbar-nav {
        flex-direction: row;
    }
</style>

@section('content')
    <div class="page-content">
        <!-- Page Header -->
        <div class="page-header page-header-light">
            <div class="page-header-content header-elements-lg-inline">
                <div class="page-title d-flex">
                    <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Home</span> - Dashboard</h4>
                    <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Main Content -->
        <div class="content" style="overflow-y: auto; max-height: calc(100vh - 200px);">
            <!-- Summary Cards -->

            @if ($lowStockInventory->count())
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0">
                            <i class="icon-warning mr-2"></i> Low Stock Inventory
                        </h6>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Serial No.</th>
                                        <th>Quantity</th>
                                        <th>Expiry Date</th>
                                        <th>Expiry</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lowStockInventory as $index => $item)
                                        <tr class="{{ $item->quantity <= 5 ? 'table-danger' : 'table-warning' }}">
                                            <td>{{ $lowStockInventory->firstItem() + $index }}</td>
                                            <td>{{ $item->name ?? 'N/A' }}</td>
                                            <td>{{ $item->serial_no ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    {{ $item->quantity }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($item->expiry_date)->format('d M Y') }}</td>
                                            <td>{{ $item->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        {{ $lowStockInventory->links() }}
                    </div>
                </div>
            @else
                <div class="alert alert-success">
                    <i class="icon-checkmark-circle mr-2"></i>
                    All products are sufficiently stocked 🎉
                </div>
            @endif


        </div>
        <!-- /Main Content -->
    </div>

    @livewireScripts

    <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>

    <script>
        let dataTablesInitialized = false;

        function initializeDataTables() {
            // Only initialize if not already initialized
            if (dataTablesInitialized) {
                return;
            }

            // Check if tables exist
            if ($('#drivers-table').length === 0 || $('#vehicles-table').length === 0) {
                return;
            }

            // Initialize DataTables for drivers table
            $('#drivers-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "All"]
                ],
                "responsive": true,
                "autoWidth": false,
                "order": [
                    [0, "asc"]
                ],
                "paging": true,
                "searching": true,
                "info": true,
                "columnDefs": [{
                        "width": "10%",
                        "targets": 0
                    },
                    {
                        "width": "25%",
                        "targets": 1
                    },
                    {
                        "width": "15%",
                        "targets": 2
                    },
                    {
                        "width": "30%",
                        "targets": 3
                    },
                    {
                        "width": "20%",
                        "targets": 4
                    }
                ],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "No entries found",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });

            // Initialize DataTables for vehicles table
            $('#vehicles-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "All"]
                ],
                "responsive": true,
                "autoWidth": false,
                "order": [
                    [0, "asc"]
                ],
                "paging": true,
                "searching": true,
                "info": true,
                "columnDefs": [{
                        "width": "10%",
                        "targets": 0
                    },
                    {
                        "width": "20%",
                        "targets": 1
                    },
                    {
                        "width": "15%",
                        "targets": 2
                    },
                    {
                        "width": "15%",
                        "targets": 3
                    },
                    {
                        "width": "15%",
                        "targets": 4
                    },
                    {
                        "width": "15%",
                        "targets": 5
                    },
                    {
                        "width": "10%",
                        "targets": 6
                    }
                ],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "No entries found",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });

            dataTablesInitialized = true;
        }

        $(document).ready(function() {
            // Initialize DataTables after a short delay to ensure Livewire components are loaded
            // setTimeout(function() {
            //     initializeDataTables();
            // }, 2000);
        });

        // Only initialize once when Livewire loads
        document.addEventListener('livewire:load', function() {
            // setTimeout(function() {
            //     initializeDataTables();
            // }, 1000);
        });
    </script>
@endsection
