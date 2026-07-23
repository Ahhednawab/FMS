@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">Master Warehouse Inventory</h2>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Master Warehouse Banner --}}
        @if (isset($masterWarehouse) && $masterWarehouse)
            <div class="alert alert-success d-flex align-items-center justify-content-between mb-3"
                style="border-left: 4px solid #28a745;">
                <div>
                    <i class="icon-shield-check mr-2"></i>
                    <strong>Master Warehouse:</strong>
                    <span class="badge badge-success ml-1">{{ $masterWarehouse->name }}</span>
                    <small class="text-muted ml-2">— Stock is distributed from here to Sub-Warehouses.</small>
                </div>
                <a href="{{ route('master_warehouse_inventory.create') }}" class="btn btn-sm btn-success">
                    <i class="icon-plus3 mr-1"></i> Add Inventory
                </a>
            </div>
        @else
            <div class="alert alert-warning d-flex align-items-center justify-content-between mb-3"
                style="border-left: 4px solid #ffc107;">
                <div>
                    <i class="icon-warning2 mr-2"></i>
                    <strong>No Master Warehouse configured.</strong>
                    <small class="text-muted ml-1">Set a Master Warehouse before adding inventory.</small>
                </div>
                <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-warning">Manage Warehouses</a>
            </div>
        @endif

        @php
            $masterStockSummary = collect($masterStockSummary ?? []);
            $masterStockByProduct = $masterStockSummary->keyBy('id');
            $masterAlertProducts = $masterStockSummary->filter(fn ($row) => $row->status !== 'ok');
        @endphp

        {{-- ── Low stock alerts ────────────────────────────────────── --}}
        @foreach ($masterAlertProducts as $row)
            @php $unit = $row->unit_name ? ' ' . $row->unit_name . ($row->current_stock == 1 ? '' : 's') : ''; @endphp
            @if ($row->status === 'out')
                <div class="alert alert-danger mb-2">
                    <i class="icon-cross-circle2 mr-1"></i>
                    <strong>Out of Stock:</strong> {{ $row->name }} is out of stock in the Master Warehouse. Please add new inventory.
                </div>
            @elseif ($row->status === 'critical')
                <div class="alert alert-danger mb-2">
                    <i class="icon-warning2 mr-1"></i>
                    <strong>Critical Stock Alert:</strong> {{ $row->name }} has only {{ $row->current_stock + 0 }}{{ $unit }} remaining in the Master Warehouse. Please replenish the stock.
                </div>
            @else
                <div class="alert alert-warning mb-2">
                    <i class="icon-warning22 mr-1"></i>
                    <strong>Low Stock Alert:</strong> {{ $row->name }} has only {{ $row->current_stock + 0 }}{{ $unit }} remaining in the Master Warehouse. Please replenish the stock.
                </div>
            @endif
        @endforeach

        {{-- ── Filters ─────────────────────────────────────────────── --}}
        <div class="card shadow mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('master_warehouse_inventory.index') }}">
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
                            <label class="mb-1">Category</label>
                            <select name="product_category_id" class="form-control">
                                <option value="">All Categories</option>
                                @foreach ($filterCategories ?? [] as $category)
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
                                @foreach ($filterBrands ?? [] as $brand)
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
                                    <option value="{{ $count }}" {{ ($perPage ?? 10) == $count ? 'selected' : '' }}>
                                        {{ $count }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="icon-filter3 mr-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('master_warehouse_inventory.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Batch</th>
                            <th>Expiry</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>Available Qty</th>
                            <th style="width:220px;">Low Stock Limit</th>
                            <th class="text-center">Assign to Sub-Warehouse</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory as $item)
                            @php
                                $productSummary = $masterStockByProduct->get($item->product_id);
                                $productStatus = $productSummary->status ?? 'ok';
                            @endphp
                            <tr class="{{ $productStatus === 'out' || $productStatus === 'critical' ? 'table-danger' : ($productStatus === 'low' ? 'table-warning' : '') }}">
                                <td>
                                    <strong>{{ $item->product?->name ?? '—' }}</strong>
                                    @if ($productStatus === 'out')
                                        <span class="badge bg-danger ml-1">Out of Stock</span>
                                    @elseif ($productStatus === 'critical')
                                        <span class="badge bg-danger ml-1">Critical Stock</span>
                                    @elseif ($productStatus === 'low')
                                        <span class="badge bg-warning text-dark ml-1">Low Stock</span>
                                    @endif
                                </td>
                                <td>{{ $item->batch_number ?? '-' }}</td>
                                <td>
                                    @if ($item->expiry_date)
                                        {{ \Carbon\Carbon::parse($item->expiry_date)->format('d M Y') }}
                                        @if (\Carbon\Carbon::parse($item->expiry_date)->isPast())
                                            <span class="badge bg-danger ms-2">Expired</span>
                                        @elseif(\Carbon\Carbon::parse($item->expiry_date)->diffInDays(now()) <= 30)
                                            <span class="badge bg-warning ms-2">Expiring Soon</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>Rs. {{ number_format($item->price, 2) }}</td>
                                <td>Rs. {{ number_format($item->price * $item->quantity, 2) }}</td>
                                <td>
                                    @php
                                        $limit = $item->product?->low_stock_limit;
                                        $isLow = $item->quantity > 0 && $limit !== null && $limit > 0 && $item->quantity <= $limit;
                                    @endphp
                                    <span class="badge bg-{{ $item->quantity <= 0 ? 'danger' : ($isLow ? 'warning text-dark' : 'success') }}">
                                        {{ $item->quantity }} {{ $item->product?->unit?->name }}
                                    </span>
                                    @if ($item->quantity <= 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif ($isLow)
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->product)
                                        <div class="d-flex">
                                            <input type="number" min="0" step="0.01"
                                                class="form-control form-control-sm low-stock-limit-input"
                                                style="width:110px;"
                                                value="{{ $item->product->low_stock_limit !== null ? $item->product->low_stock_limit + 0 : '' }}"
                                                placeholder="Not set"
                                                data-product-id="{{ $item->product_id }}">
                                            <button type="button" class="btn btn-sm btn-primary ml-1 save-low-stock-limit"
                                                data-product-id="{{ $item->product_id }}">Save</button>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($item->quantity > 0 && isset($masterWarehouse) && $masterWarehouse && count($warehouses) > 0)
                                        <form class="assign-form d-flex gap-2" data-inventory-id="{{ $item->id }}">
                                            @csrf
                                            <input type="number" name="qty" class="form-control form-control-sm mx-1"
                                                style="width:110px" required min="0.01" step="0.01"
                                                max="{{ $item->quantity }}"
                                                placeholder="Qty{{ $item->product?->unit?->name ? ' (' . $item->product->unit->name . ')' : '' }}"
                                                title="Available: {{ $item->quantity }} {{ $item->product?->unit?->name }}">

                                            {{-- ✅ FIX: Uses Warehouse::subWarehouses() data from controller --}}
                                            <select name="warehouse_id" class="form-select form-select-sm mx-1" required>
                                                <option value="">Sub-Warehouse</option>
                                                @foreach ($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}">
                                                        {{ $warehouse->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <button type="submit" class="btn btn-sm btn-success px-3 mx-1">
                                                Assign
                                            </button>
                                        </form>
                                    @elseif($item->quantity <= 0)
                                        <span class="text-muted">No stock</span>
                                    @elseif(!isset($masterWarehouse) || !$masterWarehouse)
                                        <span class="text-warning small">No Master Warehouse</span>
                                    @elseif(count($warehouses) === 0)
                                        <span class="text-muted small">No Sub-Warehouses</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    {{ request()->hasAny(['q', 'warehouse_id', 'product_category_id', 'brand_id', 'unit_id', 'stock_status']) ? 'No inventory matches the selected filters.' : 'No inventory records found.' }}
                                </td>
                            </tr>
                        @endforelse
                        <tr>
                            <td colspan="8">
                                <div class="float-right">
                                    {{ $inventory->links() }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

    @endsection

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Safe & working AJAX assignment script
            document.querySelectorAll('.assign-form').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const btn = form.querySelector('button');
                    const qtySelect = form.querySelector('[name="qty"]');
                    const warehouseSelect = form.querySelector('[name="warehouse_id"]');
                    const qty = qtySelect.value;
                    const warehouseId = warehouseSelect.value;
                    const masterId = form.dataset.inventoryId;

                    if (!qty || !warehouseId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Incomplete!',
                            text: 'Please select quantity and a Sub-Warehouse',
                            confirmButtonColor: '#3085d6'
                        });
                        return;
                    }
                    const confirmResult = await Swal.fire({
                        title: 'Assign Stock?',
                        text: `Assign ${qty} unit(s) from Master Warehouse to selected Sub-Warehouse?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Assign!',
                        cancelButtonText: 'Cancel'
                    });
                    if (!confirmResult.isConfirmed) return;

                    // Disable button
                    btn.disabled = true;
                    const originalText = btn.textContent;
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span> Assigning...';

                    try {
                        const response = await fetch(
                            `master-inventory/assign`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        ?.getAttribute('content') ||
                                        '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    master_inventory_id: masterId,
                                    warehouse_id: warehouseId,
                                    quantity: qty
                                })
                            });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message || 'Stock assigned successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload(); // Refresh to show updated quantity
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: data.message || 'Could not assign stock',
                            });
                            qtySelect.value = '';
                            warehouseSelect.value = '';
                        }
                    } catch (error) {
                        console.error('Assignment error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Please check your connection and try again.',
                        });
                    } finally {
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                });
            });

            // ── Low Stock Limit save (per product) ─────────────────────
            document.querySelectorAll('.save-low-stock-limit').forEach(function(btn) {
                btn.addEventListener('click', async function() {
                    // A product can span multiple batch rows — use the input beside this button.
                    const input = btn.closest('div').querySelector('.low-stock-limit-input');
                    const originalText = btn.textContent;

                    btn.disabled = true;
                    btn.textContent = '...';

                    try {
                        const response = await fetch(`{{ route('assigned_inventory.lowStockLimit') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                    '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                product_id: btn.dataset.productId,
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
