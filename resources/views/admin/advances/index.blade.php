@extends('layouts.admin')

@section('title', 'Issued Advances')

@section('content')
    <div class="page-header page-header-light d-flex justify-content-between align-items-center px-3">
        <h4>Issued Advances</h4>
        <a href="{{ route('advance.create') }}" class="btn btn-primary">Create New Advance</a>
    </div>

    <div class="content mt-3">
        <div class="card">
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <!-- Filters -->
                <div class="mb-3 pb-3 border-bottom">
                    <form method="GET" action="{{ route('advance.index') }}"
                        class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="mx-1">
                            <label class="mb-0 font-weight-bold">Search:</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Driver Name..." value="{{ request('search', '') }}" style="width: 200px;">
                        </div>
                        <div class="mx-1">
                            <label class="mb-0 font-weight-bold">Status:</label>
                            <select name="status" class="form-control form-control-sm" style="width: 150px;">
                                <option value="all" {{ isset($status) && $status === 'all' ? 'selected' : '' }}>All
                                </option>
                                <option value="open" {{ isset($status) && $status === 'open' ? 'selected' : '' }}>Open
                                </option>
                                <option value="closed" {{ isset($status) && $status === 'closed' ? 'selected' : '' }}>
                                    Closed</option>
                            </select>
                        </div>
                        <div class="mx-1">
                            <label class="mb-0 font-weight-bold">Show:</label>
                            <select name="per_page" class="form-control form-control-sm" style="width: 100px;">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary mx-1 mt-3">Search</button>
                    </form>
                </div>

                @if ($advances->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Driver</th>
                                    <th>Advance Date</th>
                                    <th>Amount</th>
                                    <th>Per Month Deduction</th>
                                    <th>Remaining</th>
                                    <th>Remarks</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($advances as $advance)
                                    <tr>
                                        <td>{{ $advances->firstItem() + $loop->index }}</td>
                                        <td>{{ $advance->driver->full_name ?? 'N/A' }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($advance->advance_date)->format('d M, Y') }}
                                        </td>
                                        <td>{{ number_format($advance->amount, 2) }}</td>
                                        <td>{{ number_format($advance->per_month_deduction, 2) }}</td>
                                        <td>{{ number_format($advance->remaining_amount, 2) }}</td>
                                        <td>{{ $advance->remarks }}</td>
                                        <td>
                                            @if ($advance->is_closed)
                                                <span class="badge bg-success">Closed</span>
                                            @else
                                                <span class="badge bg-warning">Open</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($advances->hasPages())
                        <div class="mt-3">
                            {{ $advances->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="icon-search" style="font-size: 48px; color: #ccc;"></i>
                        </div>
                        <h5 class="text-muted">No Advances Found</h5>
                        <p class="text-muted mb-3">
                            @if (request('search') || (isset($status) && $status !== 'all'))
                                No results match your search criteria. Try adjusting your filters.
                            @else
                                No advances have been issued yet.
                            @endif
                        </p>
                        <a href="{{ route('advance.create') }}" class="btn btn-primary">
                            <i class="icon-plus2 mr-2"></i> Issue New Advance
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
