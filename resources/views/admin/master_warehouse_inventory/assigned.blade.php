@extends('layouts.admin')

@section('content')
    <div class="container-fluid pt-2">

        @php
            $stockSummary = collect($stockSummary ?? []);
            $stockByProduct = $stockSummary->keyBy('id');
            $alertProducts = $stockSummary->filter(fn ($row) => $row->status !== 'ok');
        @endphp

        {{-- ── Stock alerts ─────────────────────────────────────────── --}}
        @foreach ($alertProducts as $row)
            @php $unit = $row->unit_name ? ' ' . $row->unit_name . ($row->current_stock == 1 ? '' : 's') : ''; @endphp
            @if ($row->status === 'out')
                <div class="alert alert-danger mb-2">
                    <i class="fas fa-times-circle mr-1"></i>
                    <strong>Out of Stock:</strong> {{ $row->name }} is out of stock. Please replenish the inventory immediately.
                </div>
            @elseif ($row->status === 'critical')
                <div class="alert alert-danger mb-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Critical Stock Alert:</strong> {{ $row->name }} has only {{ $row->current_stock + 0 }}{{ $unit }} remaining. Please refill the stock.
                </div>
            @else
                <div class="alert alert-warning mb-2">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <strong>Low Stock Alert:</strong> {{ $row->name }} has only {{ $row->current_stock + 0 }}{{ $unit }} remaining. Please refill the stock.
                </div>
            @endif
        @endforeach

        {{-- ── Current stock levels & low stock limits ──────────────── --}}
        @if ($stockSummary->count())
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-3">
                        <div class="text-white" style="background-color: #1b3244;padding-left: 10px;padding-bottom: 10px;">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-boxes"></i> Current Stock Levels
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Current Stock</th>
                                            <th style="width:260px;">Low Stock Limit</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stockSummary as $row)
                                            <tr class="{{ $row->status === 'out' || $row->status === 'critical' ? 'table-danger' : ($row->status === 'low' ? 'table-warning' : '') }}">
                                                <td><strong>{{ $row->name }}</strong></td>
                                                <td>{{ $row->current_stock + 0 }} {{ $row->unit_name }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <input type="number" min="0" step="0.01"
                                                            class="form-control form-control-sm low-stock-limit-input"
                                                            style="width:120px;"
                                                            value="{{ $row->low_stock_limit !== null ? $row->low_stock_limit + 0 : '' }}"
                                                            placeholder="Not set"
                                                            data-product-id="{{ $row->id }}">
                                                        <button type="button" class="btn btn-sm btn-primary ml-1 save-low-stock-limit"
                                                            data-product-id="{{ $row->id }}">Save</button>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($row->status === 'out')
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    @elseif ($row->status === 'critical')
                                                        <span class="badge bg-danger">Critical Stock</span>
                                                    @elseif ($row->status === 'low')
                                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                                    @else
                                                        <span class="badge bg-success">Normal</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class=" text-white" style="background-color: #1b3244;padding-left: 10px;padding-bottom: 10px;">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-exchange-alt"></i> Assigned Inventory History
                        </h3>
                    </div>

                    <div class="card-body">
                        @if ($assignments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Batch / Expiry</th>
                                                <th>Quantity Assigned</th>
                                                <th>Unit Price</th>
                                                <th>Total Price</th>
                                                <th>Warehouse</th>
                                                <th>Assigned By</th>
                                                <th>Date & Time</th>
                                            </tr>
                                        </thead>
                                    <tbody>
                                        @foreach ($assignments as $assign)
                                            @php
                                                $productStock = $stockByProduct->get($assign->masterInventory?->product?->id);
                                                $productStatus = $productStock->status ?? 'ok';
                                            @endphp
                                            <tr class="{{ $productStatus === 'out' || $productStatus === 'critical' ? 'table-danger' : ($productStatus === 'low' ? 'table-warning' : '') }}">
                                                <td>{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}
                                                </td>
                                                <td>
                                                    <strong>{{ $assign->masterInventory?->product?->name ?? 'Product Deleted' }}</strong>
                                                    @if ($productStatus === 'out')
                                                        <span class="badge bg-danger ml-1">Out of Stock</span>
                                                    @elseif ($productStatus === 'critical')
                                                        <span class="badge bg-danger ml-1">Critical Stock</span>
                                                    @elseif ($productStatus === 'low')
                                                        <span class="badge bg-warning text-dark ml-1">Low Stock</span>
                                                    @endif
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $assign->masterInventory?->product?->serial_no ?? '' }}</small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <strong>Batch:</strong> {{ $assign->batch_number ?? '-' }}<br>
                                                        @if ($assign->expiry_date)
                                                            <span
                                                                class="{{ \Carbon\Carbon::parse($assign->expiry_date)->isPast() ? 'text-danger' : '' }}">
                                                                {{ \Carbon\Carbon::parse($assign->expiry_date)->format('d M Y') }}
                                                            </span>
                                                        @else
                                                            —
                                                        @endif
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success fs-6">{{ $assign->quantity }} {{ $assign->masterInventory?->product?->unit?->name }}</span>
                                                </td>
                                                <td>RS. {{ number_format($assign->price, 2) }}</td>
                                                <td>RS. {{ number_format($assign->quantity * $assign->price, 2) }}</td>
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

                            <div class="mt-3 float-right">
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

@push('scripts')
    <script>
        document.querySelectorAll('.save-low-stock-limit').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const productId = btn.dataset.productId;
                const input = document.querySelector(`.low-stock-limit-input[data-product-id="${productId}"]`);
                const originalText = btn.textContent;

                btn.disabled = true;
                btn.textContent = '...';

                try {
                    const response = await fetch(`{{ route('assigned_inventory.lowStockLimit') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            low_stock_limit: input.value === '' ? null : input.value
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Could not save the low stock limit.');
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                } catch (error) {
                    alert('Could not save the low stock limit.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            });
        });
    </script>
@endpush
