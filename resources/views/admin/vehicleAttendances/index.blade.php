@extends('layouts.admin')

@section('title', 'Vehicle Attendance List')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Attendance List</span>
                </h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('admin.vehicleAttendances.create') }}" class="btn btn-primary">
                        <span>Add Vehicle Attendance <i class="icon-plus3 ml-2"></i></span>
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

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.vehicleAttendances.index') }}" method="GET">
                    @csrf
                    <div class="row">
                        @php
                            use Carbon\Carbon;

                            $defaultFromDate = request('from_date') ?? Carbon::now()->startOfMonth()->toDateString();
                            $defaultToDate = request('to_date') ?? Carbon::now()->today()->toDateString();
                        @endphp

                        <!--From Date -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>From</strong></label>
                                <input type="date" class="form-control" name="from_date" value="{{ $defaultFromDate }}"
                                    max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <!--To Date -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>To</strong></label>
                                <input type="date" class="form-control" name="to_date" value="{{ $defaultToDate }}"
                                    max="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <!-- Station Filter -->
                        {{--              <div class="col-md-3"> --}}
                        {{--                  <div class="form-group"> --}}
                        {{--                      <label><strong>Station</strong></label> --}}
                        {{--                      <select name="station_id" class="form-control"> --}}
                        {{--                          <option value="">-- Select Station --</option> --}}
                        {{--                          @foreach ($stations as $station) --}}
                        {{--                              <option value="{{ $station->id }}" {{ request('station_id') == $station->id ? 'selected' : '' }}> --}}
                        {{--                                  {{ $station->area }} --}}
                        {{--                              </option> --}}
                        {{--                          @endforeach --}}
                        {{--                      </select> --}}
                        {{--                  </div> --}}
                        {{--              </div> --}}

                        <div class="col-md-3 mt-4">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('admin.vehicleAttendances.index') }}" class="btn btn-primary">Reset</a>
                            </div>
                        </div>

                    </div>
                </form>
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
                <table class="table datatable-colvis-basic dataTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <label class="custom-checkbox">
                                    <input type="checkbox" id="selectAll">
                                    <span class="checkmark"></span>
                                </label>
                            </th>
                            <th>Vehicle</th>
                            <th class="text-center">Station</th>
                            <th class="text-center">Shift</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Attendance Status</th>
                            <th class="text-center">Replace By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vehicleAttendances as $key => $value)
                            <tr>
                                <td>
                                    <label class="custom-checkbox">
                                        <input type="checkbox" class="select-checkbox" value="{{ $value->id }}">
                                        <span class="checkmark"></span>
                                    </label>
                                </td>
                                <td>{{ $value->vehicle->vehicle_no }}</td>
                                <td class="text-center">{{ $value->vehicle->station->area }}</td>
                                <td class="text-center">
                                    @if ($value->vehicle->ShiftHours)
                                        {{ $value->vehicle->ShiftHours->name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($value->date)->format('d-M-Y') }}</td>
                                <td class="text-center">{{ $value->attendanceStatus->name }}</td>
                                <td class="text-center"
                                    @if ($value->pool) style="background-color: #FFEB3B; color: #000; padding: 0px 14px; border-radius: 100px; font-weight: bold; text-align: center; max-width: 120px; width: auto; vertical-align: middle;" @endif>
                                    {{ $value->pool->vehicle_no ?? '' }}
                                </td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('admin.vehicleAttendances.show', $value->id) }}"
                                                    class="dropdown-item">
                                                    <i class="icon-eye"></i> View Details
                                                </a>
                                                <a href="{{ route('admin.vehicleAttendances.edit', $value->id) }}"
                                                    class="dropdown-item">
                                                    <i class="icon-pencil7"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.vehicleAttendances.destroy', $value->id) }}"
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
            </div>
        </div>
        <!-- /basic datatable -->
    </div>
    <!-- /content area -->

    <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>
    <script src="{{ asset('assets/js/demo_pages/datatables_extension_colvis.js') }}"></script>

    <script>
        $(document).ready(function() {

            $("input[type=checkbox]").prop("checked", false);

            // Initialize DataTable
            var table = $('.datatable-colvis-basic').DataTable();

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
                        extend: 'excelHtml5', // Excel button added
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

            $('#excelBtn').on('click', function() { // Excel button trigger
                table.button('.buttons-excel').trigger();
            });

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
                    $.post("{{ route('admin.vehicleAttendances.destroyMultiple') }}", {
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
                let alertBox = document.getElementById('alert-message');
                if (alertBox) {
                    alertBox.style.transition = 'opacity 0.5s ease';
                    alertBox.style.opacity = '0';
                    setTimeout(() => alertBox.remove(), 500);
                }
            }, 3000);
        });
    </script>
@endsection
