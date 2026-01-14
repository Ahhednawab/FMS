@extends('layouts.admin')

@section('title', 'Accident Details List')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Accident Details List</span>
                </h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('accidentDetails.create') }}" class="btn btn-primary">
                        <span>Add Accident Details <i class="icon-plus3 ml-2"></i></span>
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

        <!-- Custom Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('accidentDetails.index') }}"
                    class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="mx-1">
                        <label class="mb-0 font-weight-bold">Search:</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search ID, Vehicle, Workshop..." value="{{ request('search', '') }}"
                            style="width: 250px;">
                    </div>
                    <div class="mx-1">
                        <label class="mb-0 font-weight-bold">Payment Status:</label>
                        <select name="payment_status" class="form-control form-control-sm" style="width: 180px;">
                            <option value="all" {{ isset($status) && $status === 'all' ? 'selected' : '' }}>All
                            </option>
                            @foreach ($payment_statuses as $key => $label)
                                <option value="{{ $key }}"
                                    {{ isset($status) && $status === $key ? 'selected' : '' }}>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mx-1">
                        <label class="mb-0 font-weight-bold">Show:</label>
                        <select name="per_page" class="form-control form-control-sm" style="width: 120px;">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary mx-1 mt-3">Search</button>
                </form>
            </div>
        </div>

        <!-- Basic table (no DataTable features) -->
        <div class="card">
            <div class="card-body">
                @if ($accidentDetails->count() > 0)
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Accident ID</th>
                                <th>Vehicle No</th>
                                <th>Workshop</th>
                                <th>Claim Amount</th>
                                <th>Depreciation Amount</th>
                                <th>Payment Status</th>
                                <th>Bill to KE</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accidentDetails as $key => $value)
                                <tr>
                                    <td>{{ $value->accident_id }}</td>
                                    <td>{{ $value->vehicle_no }}</td>
                                    <td>{{ $value->workshop }}</td>
                                    <td>{{ number_format($value->claim_amount) }}</td>
                                    <td>{{ number_format($value->depreciation_amount) }}</td>
                                    <td>
                                        <span
                                            class="text-{{ $value->payment_status === 'pending' ? 'warning' : 'success' }}">
                                            {{ ucfirst($payment_statuses[$value->payment_status] ?? $value->payment_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($value->bill_to_ke == 1)
                                            <span class="text-success">Yes</span>
                                        @else
                                            <span class="text-danger">No</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="{{ route('accidentDetails.show', $value->id) }}"
                                                        class="dropdown-item">
                                                        <i class="icon-eye"></i> View Details
                                                    </a>
                                                    <a href="{{ route('accidentDetails.edit', $value->id) }}"
                                                        class="dropdown-item">
                                                        <i class="icon-pencil7"></i> Edit
                                                    </a>
                                                    <form action="{{ route('accidentDetails.destroy', $value->id) }}"
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
                    <!-- Pagination Links -->
                    <div class="mt-3">
                        {{ $accidentDetails->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="icon-search" style="font-size: 48px; color: #ccc;"></i>
                        </div>
                        <h5 class="text-muted">No Accident Details Found</h5>
                        <p class="text-muted mb-3">
                            @if (request('search') || (isset($status) && $status !== 'all'))
                                No results match your search criteria. Try adjusting your filters.
                            @else
                                No accident details have been recorded yet.
                            @endif
                        </p>
                        <a href="{{ route('accidentDetails.create') }}" class="btn btn-primary">
                            <i class="icon-plus3 mr-2"></i> Create New Accident Detail
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <!-- /basic datatable -->
    </div>
    <!-- /content area -->

    <script>
        // Auto-dismiss success/error alerts
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
