@extends('layouts.admin')


@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-warehouse"></i> Request Inventory
                        </h3>
                    </div>

                    <div class="card-body">
                        @if ($availableInventory->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Expiry</th>
                                            <th>Available Qty</th>
                                            <th>Price</th>
                                            <th>Request Qty</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($availableInventory as $inventory)
                                            <tr>
                                                <td>
                                                    {{ $loop->iteration + ($availableInventory->currentPage() - 1) * $availableInventory->perPage() }}
                                                </td>

                                                <td>
                                                    <strong>{{ $inventory->product->name ?? 'N/A' }}</strong>
                                                </td>

                                                <td>
                                                    {{ $inventory->expiry_date ? \Carbon\Carbon::parse($inventory->expiry_date)->format('d M Y') : '-' }}

                                                </td>

                                                <td>
                                                    <span class="badge bg-success">
                                                        {{ $inventory->quantity }}
                                                    </span>
                                                </td>

                                                <td>
                                                    Rs. {{ number_format($inventory->price, 2) }}
                                                </td>
                                                <td>
                                                    <form action="" method="POST" class="d-flex gap-2">
                                                        @csrf
                                                        <input type="hidden" name="master_inventory_id"
                                                            value="{{ $inventory->id }}">

                                                        @php
                                                            $requestedQty =
                                                                $requestedInventoryMap[$inventory->id] ?? null;
                                                        @endphp

                                                        <select name="quantity"
                                                            class="form-select form-select-sm request-qty" required
                                                            @if ($requestedQty) disabled @endif>
                                                            <option value="">Select</option>
                                                            @for ($i = 1; $i <= $inventory->quantity; $i++)
                                                                <option value="{{ $i }}"
                                                                    @if ($requestedQty == $i) selected @endif>
                                                                    {{ $i }}
                                                                </option>
                                                            @endfor
                                                        </select>
                                                </td>

                                                <td>
                                                    <button type="submit" class="btn btn-sm btn-primary request-btn"
                                                        data-id="{{ $inventory->id }}"
                                                        @if ($requestedQty) disabled @endif>
                                                        <i class="fas fa-paper-plane"></i>
                                                        @if ($requestedQty)
                                                            Requested
                                                        @else
                                                            Request
                                                        @endif
                                                    </button>
                                                    </form>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $availableInventory->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No inventory available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush
@push('scripts')
    <script>
        $(document).ready(function() {

            // Initialize Select2
            $('.request-qty').select2({
                width: '100%',
                placeholder: 'Select Qty'
            });

            // Handle request button click
            $('.request-btn').on('click', function(e) {
                e.preventDefault();

                let button = $(this);
                let row = button.closest('tr');

                let masterInventoryId = button.data('id');
                let quantity = row.find('.request-qty').val();

                if (!quantity) {
                    alert('Please select quantity');
                    return;
                }

                button.prop('disabled', true).html('Requesting...');

                $.ajax({
                    url: "{{ route('sub-warehouse.inventory-requests.store') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        master_inventory_id: masterInventoryId,
                        quantity: quantity
                    },
                    success: function(response) {
                        // Disable dropdown & button after success
                        row.find('.request-qty').prop('disabled', true);
                        button
                            .removeClass('btn-primary')
                            .addClass('btn-success')
                            .html('Requested');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Something went wrong');
                        button.prop('disabled', false).html('Request');
                    }
                });
            });

        });
    </script>
@endpush
