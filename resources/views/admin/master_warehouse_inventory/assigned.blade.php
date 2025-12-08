@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-exchange-alt"></i> Assigned Inventory History
                    </h3>
                </div>

                <div class="card-body">
                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Batch / Expiry</th>
                                        <th>Quantity Assigned</th>
                                        <th>Price</th>
                                        <th>Warehouse</th>
                                        <th>Assigned By</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assign)
                                    <tr>
                                        <td>{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}</td>
                                        <td>
                                            <strong>{{ $assign->masterInventory?->product?->name ?? 'Product Deleted' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $assign->masterInventory?->product?->serial_no ?? '' }}</small>
                                        </td>
                                        <td>
                                            <small>
                                                <strong>Batch:</strong> {{ $assign->batch_number ?? '-' }}<br>
                                                @if($assign->expiry_date)
                                                    <span class="{{ \Carbon\Carbon::parse($assign->expiry_date)->isPast() ? 'text-danger' : '' }}">
                                                        {{ \Carbon\Carbon::parse($assign->expiry_date)->format('d M Y') }}
                                                    </span>
                                                @else
                                                    —
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success fs-6">{{ $assign->quantity }}</span>
                                        </td>
                                        <td>₹{{ number_format($assign->price, 2) }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $assign->warehouse?->name ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $assign->assignedBy?->name ?? 'System' }}
                                        </td>
                                        <td>
                                            <small>
                                                {{ \Carbon\Carbon::parse($assign->assigned_at)->format('d M Y') }}<br>
                                                {{ \Carbon\Carbon::parse($assign->assigned_at)->format('h:i A') }}
                                            </small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $assignments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No assignments yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection