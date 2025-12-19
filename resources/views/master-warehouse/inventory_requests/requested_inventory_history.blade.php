@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-history"></i> My Requested Inventory History
                        </h3>
                    </div>

                    <div class="card-body">
                        @if ($requests->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Reason</th>
                                            <th>Requested At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($requests as $request)
                                            <tr>
                                                <td>{{ $loop->iteration + ($requests->currentPage() - 1) * $requests->perPage() }}
                                                </td>
                                                <td>{{ $request->inventory->product->name ?? 'N/A' }}</td>
                                                <td>{{ $request->quantity }}</td>
                                                <td>Rs. {{ number_format($request->price, 2) }}</td>
                                                <td>
                                                    @if ($request->status === 'pending')
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @elseif ($request->status === 'approved')
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif ($request->status === 'rejected')
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @endif
                                                </td>
                                                <td>{{ $request->reason ?? '-' }}</td>
                                                <td>{{ $request->created_at->format('d M Y h:i A') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $requests->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No inventory requests found.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
