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

        <a href="{{ route($role_slug . '.master_warehouse_inventory.create') }}" class="btn btn-primary mb-3">
            Add New Inventory
        </a>

        <div class="card shadow">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Batch</th>
                            <th>Expiry</th>
                            <th>Price</th>
                            <th>Available Qty</th>
                            <th class="text-center">Assign Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory as $item)
                            <tr>
                                <td><strong>{{ $item->product?->name ?? '—' }}</strong></td>
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
                                <td>
                                    <span title="{{ $item->quantity }}"
                                        class="badge qty-badge bg-{{ $item->quantity > 0 ? 'success' : 'danger' }}">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td>
                                    @if ($item->quantity > 0)
                                        <form class="assign-form d-flex gap-2" data-inventory-id="{{ $item->id }}">
                                            @csrf
                                            <select name="qty" class="form-select form-select-sm mx-1"
                                                style="width:90px" required>
                                                <option value="">Qty</option>
                                                @for ($i = 1; $i <= $item->quantity; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>

                                            <select name="warehouse_id" class="form-select form-select-sm mx-1" required>
                                                <option value="">Warehouse</option>
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
                                    @else
                                        <span class="text-muted">No stock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No inventory records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endsection

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            var role_slug = "{{ $role_slug }}";
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
                            text: 'Please select quantity and warehouse',
                            confirmButtonColor: '#3085d6'
                        });
                        return;
                    }
                    const confirmResult = await Swal.fire({
                        title: 'Assign Stock?',
                        text: `Assign ${qty} unit(s) to selected warehouse?`,
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
                                        '{{ csrf_token() }}', // fallback if meta missing
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
        </script>
    @endpush
