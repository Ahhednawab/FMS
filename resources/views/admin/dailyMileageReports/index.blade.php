@extends('layouts.admin')

@section('title', 'Daily Mileage Report')

@push('styles')
    <style>
        .dataTables_filter {
            display: none !important;
        }

        .input-group-text {
            background: transparent;
            border-right: none;
        }

        #customSearch {
            border-left: none;
            padding-left: 5px;
        }

        #customSearch:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        .dropdown-menu {
            min-width: 15rem;
            padding: 0.5rem 0;
        }

        .dropdown-header {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.5rem 1rem;
        }

        .custom-control-label {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Mileage Report</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div class="content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dailyMileageReports.index') }}" method="get">
                    <div class="row">
                        <!-- Vehicle No -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><strong>Vehicle No</strong></label>
                                <select class="custom-select select2 vehicle" name="vehicle_id" id="vehicle_no">
                                    <option value="">--Select--</option>
                                    @foreach ($vehicles as $value)
                                        <option value="{{ $value->vehicle_no }}"
                                            {{ request('vehicle_id') == $value->vehicle_no ? 'selected' : '' }}>
                                            {{ $value->vehicle_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!--From Date -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>From</strong></label>
                                <input type="date" class="form-control" name="from_date"
                                    value="{{ request('from_date') }}" max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <!--To Date -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>To</strong></label>
                                <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}"
                                    max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-3 mt-4">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('dailyMileageReports.index') }}" class="btn btn-primary">Reset</a>
                            </div>
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
                            <input type="text" id="customSearch" class="form-control border-left-0"
                                placeholder="Search...">
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-light" id="excelBtn" title="Export to Excel">
                            <i class="icon-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-light" id="printBtn" title="Print">
                            <i class="icon-printer"></i> Print
                        </button>
                        <button class="btn btn-light ml-2" id="pdfBtn" title="Export PDF">
                            <i class="icon-file-pdf"></i> PDF
                        </button>
                        <div class="btn-group ml-2">
                            <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false">
                                <i class="icon-grid7"></i> Columns Visibility<span class="caret"></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" id="column-visibility">
                                <h6 class="dropdown-header">Show/Hide Columns</h6>
                                <div class="dropdown-divider"></div>
                                <div class="px-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input column-toggle" id="col1"
                                            data-column="0" checked>
                                        <label class="custom-control-label" for="col1">Vehicle</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input column-toggle" id="col2"
                                            data-column="1" checked>
                                        <label class="custom-control-label" for="col2">Station</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input column-toggle" id="col3"
                                            data-column="2" checked>
                                        <label class="custom-control-label" for="col3">Start Date</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input column-toggle" id="col4"
                                            data-column="3" checked>
                                        <label class="custom-control-label" for="col4">End Date</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input column-toggle" id="col5"
                                            data-column="4" checked>
                                        <label class="custom-control-label" for="col5">Start Km</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input column-toggle" id="col6"
                                            data-column="5" checked>
                                        <label class="custom-control-label" for="col6">End Km</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input column-toggle" id="col7"
                                            data-column="6" checked>
                                        <label class="custom-control-label" for="col7">Mileage</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <table id="dailyMileages" class="table datatable-colvis-basic dataTable">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Station</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Start Km</th>
                            <th>End Km</th>
                            <th>Mileage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dailyMileages as $key => $value)
                            <tr>
                                <td>{{ $value->vehicle_no }}</td>
                                <td>{{ $value->station }}</td>
                                <td>{{ \Carbon\Carbon::parse($value->start_date)->format('d-M-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($value->end_date)->format('d-M-Y') }}</td>
                                <td>{{ $value->start_km }}</td>
                                <td>{{ $value->end_km }}</td>
                                <td>{{ $value->total_mileage }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('.datatable-colvis-basic').DataTable({
                dom: "lrtip",
                pageLength: 10,
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
                    }, // Vehicle
                    {
                        visible: true
                    }, // Station
                    {
                        visible: true
                    }, // Start Date
                    {
                        visible: true
                    }, // End Date
                    {
                        visible: true
                    }, // Start Km
                    {
                        visible: true
                    }, // End Km
                    {
                        visible: true
                    } // Mileage
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
                        extend: 'excelHtml5', // <<< Excel button added
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

            // Button triggers
            $('#printBtn').on('click', function() {
                table.button('.buttons-print').trigger();
            });

            $('#pdfBtn').on('click', function() {
                table.button('.buttons-pdf').trigger();
            });

            $('#excelBtn').on('click', function() { // <<< Excel button trigger
                table.button('.buttons-excel').trigger();
            });

            // Custom search input
            $('#customSearch').on('keyup', function() {
                table.search(this.value).draw();
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
