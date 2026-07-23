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

        {{-- ── Warehouses containing the searched product ───────────── --}}
        @if (request()->filled('q'))
            <div class="card shadow mb-3">
                <div class="card-body py-3">
                    <h5 class="mb-2"><i class="fas fa-search-location mr-1"></i> Warehouse availability for "{{ request('q') }}"</h5>
                    @if (collect($productWarehouses ?? [])->count())
                        @foreach (collect($productWarehouses)->groupBy('product_name') as $productName => $rows)
                            <div class="mb-1">
                                <strong>{{ $productName }}</strong>:
                                @foreach ($rows as $row)
                                    <span class="badge bg-info mr-1">
                                        {{ $row->warehouse_name }} ({{ $row->current_stock + 0 }} {{ $row->unit_name }})
                                    </span>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No warehouse currently contains a product matching "{{ request('q') }}".</p>
                    @endif
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="text-white d-flex justify-content-between align-items-center"
                        style="background-color: #1b3244;padding:10px;">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-exchange-alt"></i> Assigned Inventory History
                        </h3>
                        <a href="{{ route('assigned_inventory.stockLevels') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-boxes mr-1"></i> Current Stock Levels
                        </a>
                    </div>

                    <div class="card-body">
                        {{-- ── Filters ─────────────────────────────────── --}}
                        <form method="GET" action="{{ route('assigned_inventory.index') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="mb-1">Product Name</label>
                                    <input type="text" name="q" class="form-control" placeholder="Search product..."
                                        value="{{ request('q') }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Warehouse</label>
                                    <select name="warehouse_id" class="form-control">
                                        <option value="">All Warehouses</option>
                                        @foreach ($filterWarehouses ?? [] as $warehouse)
                                            <option value="{{ $warehouse->id }}"
                                                {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Assigned By</label>
                                    <select name="assigned_by" class="form-control">
                                        <option value="">All Users</option>
                                        @foreach ($filterUsers ?? [] as $user)
                                            <option value="{{ $user->id }}"
                                                {{ request('assigned_by') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Assigned From</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Assigned To</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-1 mb-2">
                                    <label class="mb-1">Per Page</label>
                                    <select name="per_page" class="form-control">
                                        @foreach ([10, 25, 50, 100] as $count)
                                            <option value="{{ $count }}" {{ ($perPage ?? 10) == $count ? 'selected' : '' }}>
                                                {{ $count }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter mr-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('assigned_inventory.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                            </div>
                        </form>

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
                                <p class="text-muted">
                                    {{ request()->hasAny(['q', 'warehouse_id', 'assigned_by', 'date_from', 'date_to']) ? 'No assignments match the selected filters.' : 'No assignments yet.' }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

