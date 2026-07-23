@extends('layouts.admin')

@section('content')
    <div class="container-fluid pt-2">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="text-white d-flex justify-content-between align-items-center"
                        style="background-color: #1b3244;padding:10px;">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-boxes"></i> Current Stock Levels
                        </h3>
                        <a href="{{ route('assigned_inventory.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-exchange-alt mr-1"></i> Assigned Inventory History
                        </a>
                    </div>

                    <div class="card-body">
                        {{-- ── Filters ─────────────────────────────────── --}}
                        <form method="GET" action="{{ route('assigned_inventory.stockLevels') }}" class="mb-3">
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
                                        @foreach ($filterWarehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}"
                                                {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Category</label>
                                    <select name="product_category_id" class="form-control">
                                        <option value="">All Categories</option>
                                        @foreach ($filterCategories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ request('product_category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Brand</label>
                                    <select name="brand_id" class="form-control">
                                        <option value="">All Brands</option>
                                        @foreach ($filterBrands as $brand)
                                            <option value="{{ $brand->id }}"
                                                {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1 mb-2">
                                    <label class="mb-1">Unit</label>
                                    <select name="unit_id" class="form-control">
                                        <option value="">All</option>
                                        @foreach ($filterUnits ?? [] as $unit)
                                            <option value="{{ $unit->id }}"
                                                {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1 mb-2">
                                    <label class="mb-1">Status</label>
                                    <select name="stock_status" class="form-control">
                                        <option value="">All</option>
                                        <option value="in" {{ request('stock_status') === 'in' ? 'selected' : '' }}>In Stock</option>
                                        <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                                        <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                                    </select>
                                </div>
                                <div class="col-md-1 mb-2">
                                    <label class="mb-1">Per Page</label>
                                    <select name="per_page" class="form-control">
                                        @foreach ([10, 25, 50, 100] as $count)
                                            <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>
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
                                <a href="{{ route('assigned_inventory.stockLevels') }}" class="btn btn-secondary btn-sm">Reset</a>
                            </div>
                        </form>

                        {{-- ── Stock table ─────────────────────────────── --}}
                        @if ($stockLevels->count())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Brand</th>
                                            <th>Warehouses</th>
                                            <th>Current Stock</th>
                                            <th style="width:240px;">Low Stock Limit</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stockLevels as $row)
                                            <tr class="{{ $row->status === 'out' || $row->status === 'critical' ? 'table-danger' : ($row->status === 'low' ? 'table-warning' : '') }}">
                                                <td><strong>{{ $row->name }}</strong></td>
                                                <td>{{ $row->category_name ?? '—' }}</td>
                                                <td>{{ $row->brand_name ?? '—' }}</td>
                                                <td><small>{{ $row->warehouse_names ?? '—' }}</small></td>
                                                <td>{{ $row->current_stock + 0 }} {{ $row->unit_name }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <input type="number" min="0" step="0.01"
                                                            class="form-control form-control-sm low-stock-limit-input"
                                                            style="width:110px;"
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
                                                        <span class="badge bg-success">In Stock</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 float-right">
                                {{ $stockLevels->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No stock found for the selected filters.</p>
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
