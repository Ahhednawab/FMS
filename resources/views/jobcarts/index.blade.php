@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Maintenance Issues</h4>
                <div class="mb-3 d-flex align-items-end">
                    <label for="status-filter" class="me-2"><strong>Filter by Status:</strong></label>
                    <select id="status-filter" class="form-select w-auto form-control mx-2">
                        <option value="">All</option>
                        <option value="open request" {{ request('status') == 'open request' ? 'selected' : '' }}>Open Request
                        </option>
                        <option value="in progress" {{ request('status') == 'in progress' ? 'selected' : '' }}>In Progress
                        </option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                    <a href="{{ route('maintainer.jobcarts.create') }}" class="btn btn-primary">Create New Job Cart</a>
                </div>
            </div>

            <div class="card-body">
                @if ($jobCarts->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Issue</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Remarks</th>
                                    <th>Products</th>
                                    <th>Requested At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jobCarts as $job)
                                    <tr>
                                        <td>{{ $job->vehicle->vehicle_no ?? 'N/A' }}</td>
                                        <td>{{ $job->issue->title ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusClass = match ($job->status) {
                                                    'closed' => 'text-danger',
                                                    'in progress' => 'text-primary',
                                                    'open request' => 'text-success',
                                                    default => 'text-muted',
                                                };
                                            @endphp
                                            <span class="{{ $statusClass }}">{{ ucfirst($job->status) }}</span>
                                        </td>
                                        <td>{{ ucfirst($job->type) }}</td>
                                        <td>{{ $job->remarks ?? '-' }}</td>

                                        {{-- Products --}}
                                        <td>
                                            @if ($job->items->count())
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Product Name</th>
                                                            <th>Requested Quantity</th>
                                                            <th>Assigned Quantity</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($job->items as $item)
                                                            @php
                                                                $assignedQty = 0;
                                                                if (isset($assignedData[$job->id])) {
                                                                    $assigned = $assignedData[$job->id]->firstWhere(
                                                                        'product_id',
                                                                        $item->product_id,
                                                                    );
                                                                    $assignedQty = $assigned->total_assigned ?? 0;
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                                                <td>{{ $item->quantity }}</td>
                                                                <td>{{ $assignedQty }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>{{ $job->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-end my-2">
                        {{ $jobCarts->links() }}
                    </div>
                @else
                    <p class="text-center">No Job Carts found.</p>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('status-filter').addEventListener('change', function() {
            let status = this.value;
            let url = new URL(window.location.href);

            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }

            window.location.href = url.toString();
        });
    </script>
@endpush
