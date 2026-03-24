@extends('layouts.admin')

@section('title', 'Daily Mileage List')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Mileage List</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('dailyMileages.create') }}" class="btn btn-primary">
                        <span>Add Daily Mileage <i class="icon-plus3 ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->
    <style>
        .custom-checkbox input {
            position: absolute;
            z-index: 2;
            cursor: pointer;
        }

        .checkmark {
            z-index: 1;
        }
    </style>
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

        {{-- <!-- Bulk Actions -->
        <div class="bulk-actions card mb-3 d-none" id="bulkActions">
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
        </div> --}}

        <div class="card">
            <div class="card-body">
                <form action="{{ route('dailyMileages.index') }}" method="GET">
                    <div class="row">
                        <!-- Vehicle -->
                        <div class="col-md-3">
                            <label><strong>Vehicle No</strong></label>
                            <select name="vehicle_id[]" class="form-control select2" multiple>
                                @foreach ($vehicles as $v)
                                    <option value="{{ $v->id }}"
                                        {{ collect(request('vehicle_id'))->contains($v->id) ? 'selected' : '' }}>
                                        {{ $v->vehicle_no }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- From -->
                        <div class="col-md-2">
                            <label><strong>From</strong></label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>

                        <!-- To -->
                        <div class="col-md-2">
                            <label><strong>To</strong></label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('from_date') }}">
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-2 mt-4">
                            <button class="btn btn-primary">Filter</button>
                            <a href="{{ route('dailyMileages.index') }}" class="btn btn-secondary">Reset</a>
                        </div>

                    </div>
                </form>

            </div>
        </div>

        <!-- Basic datatable -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="input-group" style="width: 250px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0">
                                    <i class="icon-search4"></i>
                                </span>
                            </div>
                            <input type="text" id="tableSearch" class="form-control border-left-0"
                                placeholder="Search in table...">


                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-wrap">

                        <!-- Delete -->
                        <button class="btn btn-danger mr-2" id="deleteSelectedBtn">
                            <i class="icon-trash"></i> Delete
                        </button>

                        <!-- Excel -->
                        <button class="btn btn-light mr-2" id="excelBtn" title="Export to Excel">
                            <i class="icon-file-excel"></i> Excel
                        </button>

                        <!-- Print -->
                        <button class="btn btn-light mr-2" id="printBtn" title="Print">
                            <i class="icon-printer"></i> Print
                        </button>

                        <!-- PDF -->
                        <button class="btn btn-light mr-2" id="pdfBtn" title="Export PDF">
                            <i class="icon-file-pdf"></i> PDF
                        </button>

                        <!-- Columns Visibility -->
                        <div class="btn-group mr-3">
                            <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown">
                                <i class="icon-grid7"></i> Columns Visibility
                            </button>

                            <div class="dropdown-menu dropdown-menu-right" id="column-visibility">
                                <h6 class="dropdown-header">Show/Hide Columns</h6>
                                <div class="dropdown-divider"></div>

                                <div class="px-3">
                                    @foreach ([
                                                    0 => 'Vehicle',
                                                    1 => 'Station',
                                                    2 => 'Report Date',
                                                    3 => 'Previous Kms',
                                                    4 => 'Current Kms',
                                                    5 => 'Mileage',
                                                    6 => 'Actions',
                                                ] as $i => $label)
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input column-toggle"
                                                id="col{{ $i }}" data-column="{{ $i }}" checked>
                                            <label class="custom-control-label" for="col{{ $i }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Show Per Page (SEPARATE FORM) -->
                        <form action="{{ route('dailyMileages.index') }}" method="GET"
                            class="d-flex align-items-center">

                            {{-- Preserve filters --}}
                            @foreach (request()->except('per_page', 'page') as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $v)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach

                            <label class="mb-0 mr-2"><strong>Show</strong></label>
                            <select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
                                @foreach ([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}"
                                        {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                    </div>

                </div>

                <table id="dailyMileages" class="table datatable-colvis-basic dataTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <label class="custom-checkbox">
                                    <input type="checkbox" id="selectAll">
                                    <span class="checkmark"></span>
                                </label>
                            </th>
                            <th>Vehicle</th>
                            <th>Station</th>
                            <th>Report Date</th>
                            <th>Previous Kms</th>
                            <th>Current Kms</th>
                            <th>Mileage</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dailyMileages as $key => $value)
                            <tr>
                                <td>
                                    <label class="custom-checkbox">
                                        <input type="checkbox" class="select-checkbox" value="{{ $value->id }}">
                                        <span class="checkmark"></span>
                                    </label>
                                </td>
                                <td>{{ $value->vehicle->vehicle_no }}</td>
                                <td>{{ $value->vehicle->station->area }}</td>
                                <td>{{ \Carbon\Carbon::parse($value->report_date)->format('d-M-Y') }}</td>
                                <td>{{ $value->previous_km }}</td>
                                <td>{{ $value->current_km }}</td>
                                <td>{{ $value->mileage }}</td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('dailyMileages.show', $value->id) }}"
                                                    class="dropdown-item">
                                                    <i class="icon-eye"></i> View Details
                                                </a>
                                                <a href="{{ route('dailyMileages.edit', $value->id) }}"
                                                    class="dropdown-item">
                                                    <i class="icon-pencil7"></i> Edit
                                                </a>
                                                <form action="{{ route('dailyMileages.destroy', $value->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger"
                                                        onclick="return confirm('Are you sure you want to delete?')">
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
                <div class="d-flex justify-content-between align-items-center mt-3">

                    <!-- Count info -->
                    <div class="text-muted">
                        Showing
                        <strong>{{ $dailyMileages->firstItem() }}</strong>
                        to
                        <strong>{{ $dailyMileages->lastItem() }}</strong>
                        of
                        <strong>{{ $dailyMileages->total() }}</strong>
                        entries
                    </div>

                    <!-- Pagination links -->
                    <div>
                        {{ $dailyMileages->links() }}
                    </div>

                </div>


            </div>
        </div>
        <!-- /basic datatable -->
    </div>
    <!-- /content area -->

    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

    <script>
        // $(document).ready(function () {
        //   $('.datatable-colvis-basic').DataTable();




        //   $('.vehicle').select2({
        //     placeholder: "--Select--",
        //     allowClear: true,
        //     theme: 'bootstrap4'
        //   });
        // });


        $(document).ready(function() {

            $("input[type=checkbox]").prop("checked", false);

            // Initialize DataTable
            var table = $('.datatable-colvis-basic').DataTable({
                dom: "lrtip",
                paging: false,
                searching: true,
                ordering: false,
                info: false,
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                    paginate: {
                        previous: '<i class="icon-arrow-left8"></i>',
                        next: '<i class="icon-arrow-right8"></i>'
                    }
                },
                columns: [{
                        visible: true
                    }, // Checkbox
                    {
                        visible: true
                    }, // Vehicle
                    {
                        visible: true
                    }, // Station
                    {
                        visible: true
                    }, // Report Date
                    {
                        visible: true
                    }, // Previous Kms
                    {
                        visible: true
                    }, // Current Kms
                    {
                        visible: true
                    }, // Mileage
                    {
                        visible: true
                    } // Actions
                ]
            });

            // Initialize DataTable Buttons
            new $.fn.dataTable.Buttons(table, {
                buttons: [{
                        extend: 'print',
                        text: 'Print',
                        className: 'd-none',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        className: 'd-none',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Excel',
                        className: 'd-none',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    }
                ]
            });

            $('#tableSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: 'Select vehicle(s)',
                    closeOnSelect: false, // multiple selection UX
                    width: '100%'
                });
            });

            // Print button
            $('#printBtn').on('click', function() {
                table.button('.buttons-print').trigger();
            });

            // PDF button
            $('#pdfBtn').on('click', function() {
                table.button('.buttons-pdf').trigger();
            });

            // Excel button
            $('#excelBtn').on('click', function() {
                table.button('.buttons-excel').trigger();
            });

            // Initialize select2
            $('.vehicle').select2({
                placeholder: "--Select--",
                allowClear: true,
                theme: 'bootstrap4'
            });

            // Column toggle
            $('.column-toggle').on('change', function() {
                var column = table.column($(this).data('column'));
                column.visible(!column.visible());
                $(this).prop('checked', column.visible());
            });

            // Sync checkbox state
            table.columns().every(function() {
                $('.column-toggle[data-column="' + this.index() + '"]').prop('checked', this.visible());
            });
        });

        /* ============================
           CHECKBOX SELECTION LOGIC
        ============================ */

        $(document).on('change', '#selectAll', function() {
            const isChecked = this.checked;
            $('.select-checkbox').prop('checked', isChecked).trigger('change');
        });

        $(document).on('click', '#clearSelection', function() {
            $('.select-checkbox, #selectAll').prop('checked', false).trigger('change');
        });

        $(document).on('click', '#deleteSelected', function(e) {
            e.preventDefault();

            const ids = getSelectedIds();

            if (ids.length === 0) {
                alert('Please select at least one daily mileage record to delete.');
                return;
            }

            if (confirm('Are you sure you want to delete ' + ids.length + ' selected daily mileage record(s)?')) {
                // Create a form for submission
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('dailyMileages.destroyMultiple') }}";
                form.style.display = 'none';

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Add each ID
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]'; // Important: Use array notation []
                    input.value = id;
                    form.appendChild(input);
                });

                // Submit the form
                document.body.appendChild(form);
                form.submit();
            }
        });

        function getSelectedIds() {
            const ids = [];
            $('.select-checkbox:checked').each(function() {
                ids.push($(this).val());
            });
            return ids;
        }

        $('#deleteSelectedBtn').on('click', function() {

            let ids = [];

            $('.select-checkbox:checked').each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                alert('Please select at least one record.');
                return;
            }

            if (!confirm('Are you sure you want to delete selected records?')) {
                return;
            }

            let form = $('<form>', {
                method: 'POST',
                action: "{{ route('dailyMileages.destroyMultiple') }}"
            });

            form.append('@csrf');

            ids.forEach(function(id) {
                form.append(
                    $('<input>', {
                        type: 'hidden',
                        name: 'ids[]',
                        value: id
                    })
                );
            });

            $('body').append(form);
            form.submit();
        });


        /* ============================
                   AUTO HIDE ALERT
                ============================ */



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
