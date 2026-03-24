@extends('layouts.admin')

@section('title', 'Accident Details List')

@section('content')

    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4>
                    <i class="icon-arrow-left52 mr-2"></i>
                    <span class="font-weight-semibold">Accident Details List</span>
                </h4>
            </div>
            <div class="header-elements d-none">
                <a href="{{ route('accidentDetails.create') }}" class="btn btn-primary">
                    Add Accident Details <i class="icon-plus3 ml-2"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <div class="content">

        {{-- Alerts --}}
        @if ($message = Session::get('success'))
            <div id="alert-message" class="alert alert-success">{{ $message }}</div>
        @elseif ($message = Session::get('delete_msg'))
            <div id="alert-message" class="alert alert-danger">{{ $message }}</div>
        @endif

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form class="d-flex flex-wrap align-items-end gap-3">

                    <div class="mr-3">
                        <label class="font-weight-bold mb-0">Search</label>
                        <input type="text" id="live-search" name="search" value="{{ request('search') }}"
                            class="form-control form-control-sm" placeholder="Vehicle / Accident ID / Workshop"
                            autocomplete="off" style="width:300px">
                    </div>

                    <div class="mr-3">
                        <label class="font-weight-bold mb-0">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-control form-control-sm">
                            <option value="all">All</option>
                            @foreach ($payment_statuses as $key => $label)
                                <option value="{{ $key }}"
                                    {{ request('payment_status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mr-3">
                        <label class="font-weight-bold mb-0">Show</label>
                        <select name="per_page" id="per_page" class="form-control form-control-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary mx-1 mt-3">Search</button>

                </form>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-light mr-2" id="excelBtn">
                    <i class="icon-file-excel"></i> Excel
                </button>

                <button class="btn btn-light mr-2" id="printBtn">
                    <i class="icon-printer"></i> Print
                </button>

                <button class="btn btn-light" id="pdfBtn">
                    <i class="icon-file-pdf"></i> PDF
                </button>
            </div>



            <!-- Table -->
            <div class="card">
                <div class="card-body" id="table-container">

                @section('table')

                    @if ($accidentDetails->count())
                        <table class="table table-bordered table-striped datatable-accident">
                            <thead>
                                <tr>
                                    <th>Accident ID</th>
                                    <th>Accident Date</th>
                                    <th>Vehicle No</th>
                                    <th>Policy No</th>
                                    <th>Loss No</th>
                                    <th>Workshop</th>
                                    <th>Claim</th>
                                    <th>Depreciation</th>
                                    <th>Status</th>
                                    <th>Bill to KE</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accidentDetails as $row)
                                    <tr>
                                        <td>{{ $row->accident_id }}</td>
                                        <td>
                                            {{ $row->accident_date ? \Carbon\Carbon::parse($row->accident_date)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td>{{ $row->vehicle_no }}</td>
                                        <td>{{ $row->policy_no ?? 'N/A' }}</td>
                                        <td>{{ $row->loss_no ?? '-' }}</td>
                                        <td>{{ $row->workshop }}</td>
                                        <td>{{ number_format($row->claim_amount) }}</td>
                                        <td>{{ number_format($row->depreciation_amount) }}</td>
                                        <td>
                                            <span
                                                class="text-{{ $row->payment_status === 'pending' ? 'warning' : 'success' }}">
                                                {{ ucfirst($payment_statuses[$row->payment_status] ?? $row->payment_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            {!! $row->bill_to_ke ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>' !!}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('accidentDetails.show', $row->id) }}"
                                                class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('accidentDetails.edit', $row->id) }}"
                                                class="btn btn-sm btn-primary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $accidentDetails->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="icon-search" style="font-size:48px;color:#ccc;"></i>
                            <h5 class="text-muted mt-3">No Accident Details Found</h5>
                        </div>
                    @endif

                @show

            </div>
        </div>

    </div>

    {{-- JS --}}
    <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>

    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.colVis.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>


    <script>
        setTimeout(() => $('#alert-message').fadeOut(), 3000);

        let timer = null;

        $('#live-search, #payment_status, #per_page').on('keyup change', function() {
            clearTimeout(timer);

            timer = setTimeout(() => {
                $.ajax({
                    url: "{{ route('accidentDetails.index') }}",
                    type: "GET",
                    data: {
                        search: $('#live-search').val(),
                        payment_status: $('#payment_status').val(),
                        per_page: $('#per_page').val()
                    },
                    success: function(res) {
                        $('#table-container').html(res.html);
                    }
                });
            }, 400);
        });
$(document).ready(function () {

    var table = $('.datatable-accident').DataTable({
dom: 'Blfrtip',
        pageLength: 10,
        ordering: true,
        language: {
            search: "",
            searchPlaceholder: "Search..."
        },
        buttons: [
            {
                extend: 'colvis',
                text: 'Column visibility',
                className: 'btn btn-light ml-2 dropdown-toggle',
                columns: ':not(:first-child):not(:last-child)'
            },
            {
                extend: 'print',
                className: 'd-none'
            },
            {
                extend: 'pdfHtml5',
                className: 'd-none',
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Accident Details Report',
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            },
            {
                extend: 'excelHtml5',
                className: 'd-none',
                title: 'Accident Details Report',
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            }
        ]
    });
    $('.dt-button .dt-down-arrow').remove();


    $('#printBtn').click(() => table.button('.buttons-print').trigger());
    $('#pdfBtn').click(() => table.button('.buttons-pdf').trigger());
    $('#excelBtn').click(() => table.button('.buttons-excel').trigger());
});

    </script>

@endsection
