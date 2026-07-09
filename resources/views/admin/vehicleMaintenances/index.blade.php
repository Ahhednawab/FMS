@extends('layouts.admin')

@section('title', 'Vehicle Maintenance List')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Maintenance List</span></h4>
            </div>
            <div class="header-elements d-none">
                <a href="{{ route('vehicleMaintenances.create') }}" class="btn btn-primary">
                    Add Vehicle Maintenance <i class="icon-plus3 ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="content">
        @if ($message = Session::get('success'))
            <div id="alert-message" class="alert alert-success">{{ $message }}</div>
        @elseif ($message = Session::get('delete_msg'))
            <div id="alert-message" class="alert alert-danger">{{ $message }}</div>
        @endif

        <div class="card mb-3 maintenance-filter-card">
            <div class="card-body">
                <form method="GET" action="{{ route('vehicleMaintenances.index') }}">
                    <div class="row">
                        <div class="col-md-2 form-group">
                            <label>Date From</label>
                            <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Date To</label>
                            <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Vehicle Number</label>
                            <select name="vehicle_id" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($vehicles as $id => $name)
                                    <option value="{{ $id }}" {{ request('vehicle_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Vehicle Make</label>
                            <select name="vehicle_make" class="form-control">
                                <option value="">All</option>
                                @foreach ($vehicleMakes as $make)
                                    <option value="{{ $make }}" {{ request('vehicle_make') == $make ? 'selected' : '' }}>{{ $make }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Vehicle Model</label>
                            <select name="vehicle_model" class="form-control">
                                <option value="">All</option>
                                @foreach ($vehicleModels as $model)
                                    <option value="{{ $model }}" {{ request('vehicle_model') == $model ? 'selected' : '' }}>{{ $model }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Maintenance Type</label>
                            <select name="maintenance_type" class="form-control">
                                <option value="">All</option>
                                @foreach ($maintenanceTypes as $key => $label)
                                    <option value="{{ $key }}" {{ request('maintenance_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2 form-group">
                            <label>Work Done</label>
                            <select name="work_done_id" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($workDones as $id => $name)
                                    <option value="{{ $id }}" {{ request('work_done_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Warehouse</label>
                            <select name="warehouse_id" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($warehouses as $id => $name)
                                    <option value="{{ $id }}" {{ request('warehouse_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Workshop</label>
                            <select name="workshop_id" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($workshops as $id => $name)
                                    <option value="{{ $id }}" {{ request('workshop_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Product / Part Used</label>
                            <select name="product_id" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($products as $id => $name)
                                    <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Amount Min</label>
                            <input type="number" name="amount_min" value="{{ request('amount_min') }}" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Amount Max</label>
                            <input type="number" name="amount_max" value="{{ request('amount_max') }}" class="form-control" step="0.01">
                        </div>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-md-3 form-group">
                            <label>Created By</label>
                            <select name="created_by" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($createdByUsers as $id => $name)
                                    <option value="{{ $id }}" {{ request('created_by') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search maintenance...">
                        </div>
                        <div class="col-md-6 form-group text-right">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="{{ route('vehicleMaintenances.index') }}" class="btn btn-light">Clear Filters</a>
                            {{-- <button type="button" id="excelBtn" class="btn btn-success">Excel</button>
                            <button type="button" id="pdfBtn" class="btn btn-danger">PDF</button>
                            <button type="button" id="printBtn" class="btn btn-secondary">Print</button> --}}
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover datatable-colvis-basic">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Vehicle No</th>
                                <th>Make</th>
                                <th>Model</th>
                                <th>Type</th>
                                <th>Work Done</th>
                                <th>Warehouse</th>
                                <th>Workshop</th>
                                <th>Parts Used</th>
                                <th>Amount</th>
                                <th>Created By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vehicleMaintenances as $value)
                                <tr>
                                    <td>{{ optional($value->service_date)->format('d M Y') }}</td>
                                    <td>{{ $value->vehicle?->vehicle_no ?? 'N/A' }}</td>
                                    <td>{{ $value->vehicle_make ?? 'N/A' }}</td>
                                    <td>{{ $value->model ?? 'N/A' }}</td>
                                    <td>{{ $maintenanceTypes[$value->maintenance_type] ?? $value->maintenance_type }}</td>
                                    <td>{{ $value->workDone?->name ?? 'N/A' }}</td>
                                    <td>{{ $value->warehouse?->name ?? 'N/A' }}</td>
                                    <td>{{ $value->workshop?->name ?? 'N/A' }}</td>
                                    <td>
                                        @forelse ($value->maintenanceParts->groupBy('product_id') as $rows)
                                            <div>{{ $rows->first()->product?->name }} ({{ $rows->sum('quantity') }})</div>
                                        @empty
                                            N/A
                                        @endforelse
                                    </td>
                                    <td>Rs. {{ number_format($value->service_cost, 2) }}</td>
                                    <td>{{ $value->createdBy?->name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="{{ route('vehicleMaintenances.show', $value->id) }}" class="dropdown-item"><i class="icon-eye"></i> View Details</a>
                                                    <a href="{{ route('vehicleMaintenances.edit', $value->id) }}" class="dropdown-item"><i class="icon-pencil7"></i> Edit</a>
                                                    <form action="{{ route('vehicleMaintenances.destroy', $value->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete?')">
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
                <div class="mt-3">
                    {{ $vehicleMaintenances->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2').select2({ width: '100%' });

            const table = $('.datatable-colvis-basic').DataTable({
                paging: false,
                searching: false,
                info: false,
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', title: 'Vehicle Maintenance', className: 'd-none', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'pdfHtml5', title: 'Vehicle Maintenance', className: 'd-none', orientation: 'landscape', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'print', title: 'Vehicle Maintenance', className: 'd-none', exportOptions: { columns: ':not(:last-child)' } },
                ],
            });

            $('#excelBtn').on('click', function() { table.button(0).trigger(); });
            $('#pdfBtn').on('click', function() { table.button(1).trigger(); });
            $('#printBtn').on('click', function() { table.button(2).trigger(); });

            setTimeout(function() {
                $('#alert-message').fadeOut(500, function() { $(this).remove(); });
            }, 3000);
        });
    </script>
    <style>
    .datatable-colvis-basic {
        font-size: 12px;
    }

    .datatable-colvis-basic th,
    .datatable-colvis-basic td {
        padding: 6px 8px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .datatable-colvis-basic td div {
        margin-bottom: 2px;
    }

    .datatable-colvis-basic th {
        font-size: 12px;
        font-weight: 600;
    }

    .maintenance-filter-card label {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
}

.maintenance-filter-card .form-control,
.maintenance-filter-card .select2-container--default .select2-selection--single {
    height: 34px;
    min-height: 34px;
    font-size: 12px;
}

.maintenance-filter-card .select2-container--default .select2-selection--single {
    padding-top: 2px;
}

.maintenance-filter-card .form-group {
    margin-bottom: 10px;
}

.maintenance-filter-card .btn {
    height: 34px;
    font-size: 12px;
    padding: 6px 12px;
}
</style>
@endsection
