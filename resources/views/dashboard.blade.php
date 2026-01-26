@extends('layouts.admin')

@section('title', 'Dashboard')
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
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
            @if (auth()->user()->hasPermission('vehicle_attendances') || auth()->user()->hasPermission('driver_attendances'))

                <div class="row">
                    @if (auth()->user()->hasPermission('vehicle_attendances'))
                        <div class="col-lg-4">
                            <div class="card bg-teal text-white">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <h3 class="font-weight-semibold mb-0">3,450</h3>
                                        <span class="badge badge-dark badge-pill align-self-center ml-auto">+53.6%</span>
                                    </div>
                                    <div>Total Vehicles</div>
                                    <div class="font-size-sm opacity-75">489 avg</div>
                                </div>
                                <div class="container-fluid page-header-light py-3">
                                    <div id="members-online"></div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->hasPermission('driver_attendances'))
                        <!-- Card 2 -->
                        <div class="col-lg-4">
                            <div class="card bg-pink text-white">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <h3 class="font-weight-semibold mb-0">49.4%</h3>
                                        <div class="list-icons ml-auto">
                                            <a href="#" class="list-icons-item"><i class="icon-cog3"></i></a>
                                        </div>
                                    </div>
                                    <div>Total Drivers</div>
                                    <div class="font-size-sm opacity-75">34.6% avg</div>
                                </div>
                                <div id="server-load"></div>
                            </div>
                        </div>
                    @endif

                    <!-- Card 3 -->
                    <div class="col-lg-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex">
                                    <h3 class="font-weight-semibold mb-0">$18,390</h3>
                                    <div class="list-icons ml-auto">
                                        <a class="list-icons-item" data-action="reload"></a>
                                    </div>
                                </div>
                                <div>This week maintenance</div>
                                <div class="font-size-sm opacity-75">$37,578 avg</div>
                            </div>
                            <div id="today-revenue"></div>
                        </div>
                    </div>
                </div>
            @endif
            @if (auth()->user()->hasPermission('vehicle_attendances') || auth()->user()->hasPermission('driver_attendances'))
                <!-- Live Data Tables -->
                <div class="row mt-4">
                    @if (auth()->user()->hasPermission('driver_attendances'))
                        <!-- Drivers Table -->
                        <div class="col-lg-6">
                            @livewire('expired-drivers-table')
                        </div>
                    @endif
                    @if (auth()->user()->hasPermission('vehicle_attendances'))
                        <!-- Vehicles Table -->
                        <div class="col-lg-6">
                            @livewire('expired-vehicles-table')
                        </div>
                    @endif
                </div>
            @endif
            <div class="row mt-4">
                @if (auth()->user()->hasPermission('alerts'))

                    <div class="col-6">
                        <div class="page-header page-header-light">
                            <div class="page-header-content">
                                <h5>
                                    <i class="icon-database mr-2"></i>
                                    <span class="font-weight-semibold">Master Data Notifications</span>
                                </h5>
                            </div>
                        </div>

                        <div class="card" style="min-height:660px;">
                            <div class="card-body d-flex flex-column">

                                <!-- FILTERS -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="font-size-sm">Vehicle</label>
                                        <select id="master_vehicle" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            @foreach ($vehicles as $v)
                                                <option value="{{ $v->id }}">{{ $v->vehicle_no }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="font-size-sm">Type</label>
                                        <select id="master_title" class="form-control form-control-sm">
                                            <option value="">All</option>
                                        </select>
                                    </div>

                                </div>

                                <!-- TABLE -->
                                <div class="table-responsive flex-grow-1">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Message</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="master-table"></tbody>
                                    </table>
                                </div>

                                <!-- PAGINATION (STUCK) -->
                                <div class="mt-auto pt-3 border-top">
                                    <div id="master-pagination" class="d-flex flex-wrap"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="page-header page-header-light">
                            <div class="page-header-content">
                                <h5>
                                    <i class="icon-wrench mr-2"></i>
                                    <span class="font-weight-semibold">Maintenance Alerts</span>
                                </h5>
                            </div>
                        </div>

                        <div class="card" style="min-height:660px;">
                            <div class="card-body d-flex flex-column">

                                <!-- FILTERS -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="font-size-sm">Vehicle</label>
                                        <select id="maintenance_vehicle" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            @foreach ($vehicles as $v)
                                                <option value="{{ $v->id }}">{{ $v->vehicle_no }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="font-size-sm">Alert</label>
                                        <select id="maintenance_alert" class="form-control form-control-sm">
                                            <option value="">All</option>
                                        </select>
                                    </div>

                                </div>

                                <!-- TABLE -->
                                <div class="table-responsive flex-grow-1">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Alert</th>
                                                <th>Message</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="maintenance-table"></tbody>
                                    </table>
                                </div>

                                <!-- PAGINATION (STUCK) -->
                                <div class="mt-auto pt-3 border-top">
                                    <div id="maintenance-pagination" class="d-flex flex-wrap"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-6 mt-4">
                        <div class="page-header page-header-light">
                            <div class="page-header-content">
                                <h5>
                                    <i class="icon-user mr-2"></i>
                                    <span class="font-weight-semibold">Driver Notifications</span>
                                </h5>
                            </div>
                        </div>

                        <div class="card" style="min-height:660px;">
                            <div class="card-body d-flex flex-column">

                                <!-- FILTERS -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="font-size-sm">Driver</label>
                                        <select id="driver_id" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            @foreach ($drivers as $driver)
                                                <option value="{{ $driver->id }}"
                                                    {{ isset($driverId) && $driverId == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->full_name }} ({{ $driver->cnic_no }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="font-size-sm">Title</label>
                                        <select id="driver_title" class="form-control form-control-sm">
                                            <option value="">All</option>
                                        </select>
                                    </div>


                                </div>

                                <!-- TABLE -->
                                <div class="table-responsive flex-grow-1">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Message</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="driver-table"></tbody>
                                    </table>
                                </div>

                                <!-- PAGINATION -->
                                <div class="mt-auto pt-3 border-top">
                                    <div id="driver-pagination" class="d-flex flex-wrap"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                @endif


            </div>
        </div>
        <!-- /Main Content -->



    </div>

    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
    <script>
        function loadDrivers(page = 1) {
            $.get("{{ route('notifications.index') }}", {
                type: 'driver',
                page,
                driver_id: $('#driver_id').val(),
                title: $('#driver_title').val()
            }, res => {

                let tbody = $('#driver-table').empty();

                if (!res.data.data.length) {
                    tbody.append(`
                <tr>
                    <td colspan="3" class="text-center text-muted">No records found</td>
                </tr>
            `);
                    return;
                }

                res.data.data.forEach(n => {
                    tbody.append(`
                <tr id="n-${n.id}">
                    <td>${n.title}</td>
                    <td>${n.message}</td>
                    <td class="text-center">
                        <button class="btn btn-success btn-sm"
                            onclick="markDone(${n.id}, loadDrivers)">
                            Mark as Done
                        </button>
                    </td>
                </tr>
            `);
                });

                renderPagination(res.data, '#driver-pagination', 'loadDrivers');
            });
        }

        function loadDriverAlerts() {
            $.get('/notifications/driver-alerts', res => {
                let select = $('#driver_title').empty().append(`<option value="">All</option>`);
                res.data.forEach(t => {
                    select.append(`<option value="${t}">${t}</option>`);
                });
            });
        }

        function renderPagination(data, container, callback) {
            let html = '';
            let current = data.current_page;
            let last = data.last_page;

            if (current > 1) {
                html += `<button class="btn btn-sm btn-light" onclick="${callback}(${current - 1})">« Prev</button>`;
            }

            let pages = [...new Set([
                1, 2, current - 1, current, current + 1, last - 1, last
            ])].filter(p => p > 0 && p <= last).sort((a, b) => a - b);

            let prev = null;
            pages.forEach(p => {
                if (prev && p > prev + 1) html += `<span class="mx-1">…</span>`;
                html += `<button class="btn btn-sm ${p === current ? 'btn-primary' : 'btn-light'}"
            onclick="${callback}(${p})">${p}</button>`;
                prev = p;
            });

            if (current < last) {
                html += `<button class="btn btn-sm btn-light" onclick="${callback}(${current + 1})">Next »</button>`;
            }

            $(container).html(html);
        }

        // MASTER
        function loadMaster(page = 1) {
            $.get("{{ route('notifications.index') }}", {
                type: 'master_data',
                page,
                vehicle_id: $('#master_vehicle').val(),
                title: $('#master_title').val()
            }, res => {

                let tbody = $('#master-table').empty();

                if (!res.data.data.length) {
                    tbody.append(`
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        No records found
                    </td>
                </tr>
            `);
                    return;
                }

                res.data.data.forEach(n => {
                    tbody.append(`
                <tr id="n-${n.id}">
                    <td>${n.title}</td>
                    <td>${n.message}</td>
                    <td class="text-center">
                        <button
                            class="btn btn-success btn-sm"
                            onclick="markDone(${n.id}, loadMaster)">
                            Mark as Done
                        </button>
                    </td>

                </tr>
            `);
                });

                renderPagination(res.data, '#master-pagination', 'loadMaster');
            });
        }


        // MAINTENANCE
        function loadMaintenance(page = 1) {
            $.get("{{ route('notifications.index') }}", {
                type: 'maintenance',
                page,
                vehicle_id: $('#maintenance_vehicle').val(),
                title: $('#maintenance_alert').val()
            }, res => {

                let tbody = $('#maintenance-table').empty();

                if (!res.data.data.length) {
                    tbody.append(`
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        No alerts found
                    </td>
                </tr>
            `);
                    return;
                }

                res.data.data.forEach(n => {
                    tbody.append(`
                <tr id="n-${n.id}">
                    <td>${n.title}</td>
                    <td>${n.message}</td>
                    <td class="text-center">
                        <button
                            class="btn btn-success btn-sm"
                            onclick="markDone(${n.id}, loadMaintenance)">
                            Mark as Done
                        </button>
                    </td>
                </tr>
            `);
                });

                renderPagination(res.data, '#maintenance-pagination', 'loadMaintenance');
            });
        }


        // MARK DONE
        function markDone(id, reloadFn) {
            $.post(`/notifications/${id}/done`, {
                _token: '{{ csrf_token() }}'
            }, () => reloadFn());
        }

        // LOAD MAINTENANCE ALERTS
        function loadMaintenanceAlerts() {
            $.get('/notifications/maintenance-alerts', res => {
                let select = $('#maintenance_alert').empty().append(`<option value="">All Alerts</option>`);
                res.data.forEach(alert => {
                    select.append(`<option value="${alert}">${alert}</option>`);
                });
            });
        }

        function loadMasterDataAlerts() {
            $.get('/notifications/master-data-alerts', res => {
                let select = $('#master_title').empty().append(`<option value="">All Alerts</option>`);
                res.data.forEach(alert => {
                    select.append(`<option value="${alert}">${alert}</option>`);
                });
            });
        }

        // EVENTS
        $('#master_vehicle, #master_title').on('change', () => loadMaster(1));
        $('#maintenance_vehicle, #maintenance_alert').on('change', () => loadMaintenance(1));
        $('#driver_id, #driver_title').on('change', () => loadDrivers(1));

        // INIT
        $(document).ready(function() {
            $('#master_vehicle, #master_title, #maintenance_vehicle, #maintenance_alert, #driver_id, #driver_title')
                .select2({
                    width: '100%',
                    placeholder: 'Select an option',
                    allowClear: true
                });
            loadMaster();
            loadMaintenance();
            loadMaintenanceAlerts();
            loadMasterDataAlerts();
            loadDrivers();
            loadDriverAlerts();

        });
    </script>


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
