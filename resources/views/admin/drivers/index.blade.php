@extends('layouts.admin')

@section('title', 'Drivers List')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Drivers List</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                        <span>Add Driver <i class="icon-plus3 ml-2"></i></span>
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

        <!-- Bulk Actions -->
        <div class="bulk-actions card mb-3" id="bulkActions">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <span id="selectedCount">0</span> items selected
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown"
                            aria-expanded="false">
                            Bulk Actions
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" id="deleteSelected">Delete Selected</a>
                            <a class="dropdown-item d-none" href="#" id="exportSelected">Export Selected</a>
                        </div>
                    </div>
                    <button type="button" class="btn btn-link text-danger ml-auto" id="clearSelection">Clear
                        Selection</button>
                </div>
            </div>
        </div>


        <!-- Basic datatable -->
        <div class="card">
            <div class="card-body">
                <button class="btn btn-light" id="excelBtn" title="Export to Excel">
                    <i class="icon-file-excel"></i> Excel
                </button>
                <button class="btn btn-light" id="printBtn" title="Print">
                    <i class="icon-printer"></i> Print
                </button>
                <button class="btn btn-light ml-2" id="pdfBtn" title="Export PDF">
                    <i class="icon-file-pdf"></i> PDF
                </button>
                <table class="table datatable-colvis-basic  ">
                    <thead>
                        <tr>
                            <th width="50">
                                <label class="custom-checkbox">
                                    <input type="checkbox" id="selectAll">
                                    <span class="checkmark"></span>
                                </label>
                            </th>
                            <th>Name</th>
                            <th>Cell no</th>
                            <th>Account</th>
                            <th>Vehicle No</th>
                            <th class="text-center">Shift</th>
                            <th class="text-center">Father Name</th>
                            <th class="text-center">CNiC No.</th>
                            <th>CNiC expiry date</th>
                            <th>Eobi #</th>
                            <th>License expiry date</th>
                            <th>Uniform issue date</th>
                            <th>Sandal issue date</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($drivers as $key => $value)
                            <tr data-id="{{ $value->id }}">
                                <td>
                                    <label class="custom-checkbox">
                                        <input type="checkbox" class="select-checkbox" value="{{ $value->id }}">
                                        <span class="checkmark"></span>
                                    </label>
                                </td>
                                {{--                <td>{{$value->serial_no}}</td> --}}
                                <td>{{ $value->full_name }}</td>
                                <td>{{ $value->phone }}</td>
                                <td>{{ $value->account_no }}</td>
                                <td>
                                    @if ($value->vehicle)
                                        {{ $value->vehicle->vehicle_no }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($value->shiftTiming)
                                        {{ $value->shiftTiming->name }}
                                        ({{ \Carbon\Carbon::parse($value->shiftTiming->start_time)->format('h:i A') }}
                                        -
                                        {{ \Carbon\Carbon::parse($value->shiftTiming->end_time)->format('h:i A') }})
                                    @else
                                        N/A
                                    @endif
                                </td>
                                {{--                  <td>{{ $value->vehicle?->station?->area ?? 'N/A' }}</td> --}}
                                {{--                <td>@if ($value->vehicle) {{$value->vehicle->vehicle_no}} @else N/A @endif</td> --}}
                                <td>{{ $value->father_name }}</td>
                                <td>{{ $value->cnic_no }}</td>
                                <td>{{ $value->cnic_expiry_date }}</td>
                                <td>{{ $value->eobi_no }}</td>
                                <td>{{ $value->license_expiry_date }}</td>
                                <td>{{ $value->uniform_issue_date }}</td>
                                <td>{{ $value->sandal_issue_date }}</td>
                                <td>{{ $value->is_active ? 'Active' : 'In Active' }}</td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown"><i
                                                    class="icon-menu9"></i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('drivers.show', $value->id) }}" class="dropdown-item"><i
                                                        class="icon-eye"></i> View</a>
                                                <a href="{{ route('drivers.edit', $value->id) }}"
                                                    class="dropdown-item"><i class="icon-pencil7"></i> Edit</a>
                                                <form action="{{ route('drivers.destroy', $value->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item"
                                                        onclick="return confirm('Are you sure?')"><i
                                                            class="icon-trash"></i> Delete</button>
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
        <!-- /basic datatable -->
    </div>
    <!-- /content area -->

    <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>
    <script src="{{ asset('assets/js/demo_pages/datatables_extension_colvis.js') }}"></script>

    <script>
        function setCookie(name, value, days = 365) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }

        $(document).ready(function() {

            /* ============================
               DATATABLE INIT
            ============================ */

            var table = $('.datatable-colvis-basic').DataTable();

            /* ============================
               COLUMN VISIBILITY COOKIE
            ============================ */

            const cookieName = 'driver_list_column_visibility';
            let savedVisibility = getCookie(cookieName);
            savedVisibility = savedVisibility ? JSON.parse(savedVisibility) : {};

            /* ============================
               APPLY SAVED VISIBILITY
            ============================ */

            setTimeout(function() {

                Object.keys(savedVisibility).forEach(function(colIndex) {

                    const isVisible = savedVisibility[colIndex];
                    const nth = parseInt(colIndex) + 1;

                    // Header
                    document.querySelectorAll(
                        '.datatable-colvis-basic thead th:nth-child(' + nth + ')'
                    ).forEach(el => {
                        el.style.display = isVisible ? '' : 'none';
                    });

                    // Body
                    document.querySelectorAll(
                        '.datatable-colvis-basic tbody td:nth-child(' + nth + ')'
                    ).forEach(el => {
                        el.style.display = isVisible ? '' : 'none';
                    });

                    // ColVis dropdown UI sync
                    document.querySelectorAll(
                        '.dt-button-collection button[data-cv-idx="' + colIndex + '"]'
                    ).forEach(btn => {
                        btn.classList.toggle('active', isVisible);
                        btn.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
                    });
                });

            }, 700);

            /* ============================
               FIX COLVIS TOGGLE STATE
            ============================ */

            document.addEventListener('click', function(e) {

                if (!e.target.closest('.dt-button.buttons-colvis')) return;

                setTimeout(function() {

                    document.querySelectorAll('.dt-button-collection button').forEach(function(
                        btn) {

                        const colIdx = btn.getAttribute('data-cv-idx');
                        if (colIdx === null) return;

                        if (savedVisibility[colIdx] === false) {
                            btn.classList.remove('active');
                            btn.setAttribute('aria-pressed', 'false');
                        }
                    });

                }, 50);
            });

            /* ============================
               EXPORT BUTTONS
            ============================ */

            new $.fn.dataTable.Buttons(table, {
                buttons: [{
                        extend: 'print',
                        className: 'd-none',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'd-none',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        className: 'd-none',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    }
                ]
            });


            table.on('column-visibility.dt', function() {
                let visibility = {};

                table.columns().every(function(index) {
                    visibility[index] = this.visible();
                });

                setCookie(cookieName, JSON.stringify(visibility));
            });

            $('#printBtn').on('click', () => table.button('.buttons-print').trigger());
            $('#pdfBtn').on('click', () => table.button('.buttons-pdf').trigger());
            $('#excelBtn').on('click', () => table.button('.buttons-excel').trigger());

            /* ============================
               CHECKBOX SELECTION LOGIC
            ============================ */

            $('#selectAll').on('change', function() {
                const checked = this.checked;
                $('.select-checkbox').prop('checked', checked);
                updateBulkActions();
            });

            $(document).on('change', '.select-checkbox', function() {
                $('#selectAll').prop(
                    'checked',
                    $('.select-checkbox').length === $('.select-checkbox:checked').length
                );
                updateBulkActions();
            });

            $('#clearSelection').on('click', function() {
                $('.select-checkbox, #selectAll').prop('checked', false);
                updateBulkActions();
            });

            $('#deleteSelected').on('click', function(e) {
                e.preventDefault();
                const ids = getSelectedIds();
                if (!ids.length) return alert('Select at least one driver');

                if (confirm('Delete selected drivers?')) {
                    $.post("{{ route('drivers.destroyMultiple') }}", {
                        _token: '{{ csrf_token() }}',
                        ids: ids
                    }, res => res.success ? location.reload() : alert('Delete failed'));
                }
            });

            function getSelectedIds() {
                return $('.select-checkbox:checked').map(function() {
                    return this.value;
                }).get();
            }

            function updateBulkActions() {
                const count = $('.select-checkbox:checked').length;
                $('#selectedCount').text(count);
                $('#bulkActions').toggleClass('show', count > 0);
            }

            /* ============================
               AUTO HIDE ALERT
            ============================ */

            setTimeout(function() {
                const alertBox = document.getElementById('alert-message');
                if (alertBox) {
                    alertBox.style.opacity = '0';
                    setTimeout(() => alertBox.remove(), 500);
                }
            }, 3000);
        });
    </script>

@endsection
