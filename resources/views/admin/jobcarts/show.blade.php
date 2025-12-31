@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>Job Cart Details</h4>
                <a href="{{ route('master-warehouse.jobcarts.index') }}" class="btn btn-secondary">
                    Back
                </a>
            </div>

            <div class="card-body">
                {{-- Job Info --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Vehicle:</strong>
                        <p>{{ $jobcart->vehicle->vehicle_no ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-4">
                        <strong>Issue:</strong>
                        <p>{{ $jobcart->issue->title ?? 'N/A' }}</p>
                    </div>

                    {{-- Job Cart Status --}}
                    <div class="mb-3">
                        <label for="jobcart-status" class="form-label"><strong>Status:</strong></label>
                        <select id="jobcart-status" class="form-select form-control" data-jobcart="{{ $jobcart->id }}">
                            @foreach (['open request', 'in progress', 'closed'] as $status)
                                <option value="{{ $status }}" {{ $jobcart->status == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button id="update-status-btn" style="height:38px;" class="btn btn-success mt-4 ml-2">Update
                        Status</button>
                    {{-- Job Cart Status --}}
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Type:</strong>
                        <p>{{ ucfirst($jobcart->type) }}</p>
                    </div>

                    <div class="col-md-4">
                        <strong>Created By:</strong>
                        <p>{{ $jobcart->creator->name ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-4">
                        <strong>Requested At:</strong>
                        <p>{{ $jobcart->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Remarks:</strong>
                    <p>{{ $jobcart->remarks ?? '-' }}</p>
                </div>

                {{-- Products --}}
                @foreach ($jobcart->items as $item)
                    @php
                        // Assigned qty comes directly from job_cart_assignments (product_id => total_assigned)
                        $alreadyAssignedQty = (int) ($assignedQtyMap[$item->product_id] ?? 0);
                        $remainingQty = $item->quantity - $alreadyAssignedQty;
                    @endphp

                    <h5 class="mt-4">
                        {{ $item->product->name }}
                        <small class="text-muted">
                            (Requested: {{ $item->quantity }})
                        </small>
                    </h5>

                    {{-- Fully assigned --}}
                    @if ($remainingQty <= 0)
                        <p class="text-success fw-bold">
                            ✔ Already assigned for this product
                        </p>
                        @continue
                    @endif

                    {{-- Inventory available --}}
                    @if (isset($inventories[$item->product_id]))
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Batch</th>
                                    <th>Available</th>
                                    <th>Assign Qty</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventories[$item->product_id] as $inventory)
                                    @php
                                        $maxAssignable = min($inventory->assigned_quantity, $remainingQty);
                                    @endphp

                                    @if ($maxAssignable > 0)
                                        <tr>
                                            <td>{{ $inventory->batch_number }}</td>
                                            <td>{{ $inventory->assigned_quantity }}</td>
                                            <td>
                                                <select class="form-control assign-qty" data-max="{{ $maxAssignable }}">
                                                    <option value="">Select</option>
                                                    @for ($i = 1; $i <= $maxAssignable; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </td>
                                            <td>{{ $inventory->price }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary assign-btn"
                                                    data-assignment="{{ $inventory->assignment_id }}"
                                                    data-inventory="{{ $inventory->inventory_id }}"
                                                    data-product="{{ $item->product_id }}"
                                                    data-jobcart-item="{{ $item->id }}"
                                                    data-jobcart="{{ $jobcart->id }}"
                                                    data-requested="{{ $item->quantity }}">
                                                    Assign
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-danger">No inventory available</p>
                    @endif
                @endforeach





            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $('#update-status-btn').on('click', function() {
            let status = $('#jobcart-status').val();
            let jobcartId = $('#jobcart-status').data('jobcart');

            if (!status) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Please select a status'
                });
                return;
            }

            $.ajax({
                url: "{{ route('master-warehouse.jobcarts.updateStatus') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: jobcartId,
                    status: status
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    }).then(() => location.reload());
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong'
                    });
                }
            });
        });

        $('.assign-btn').on('click', function() {
            let row = $(this).closest('tr');
            let quantity = row.find('.assign-qty').val();

            if (!quantity) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Please select quantity'
                });
                return;
            }

            $.ajax({
                url: "{{ route('master-warehouse.inventory.assign') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    assignment_id: $(this).data('assignment'),
                    inventory_id: $(this).data('inventory'),
                    product_id: $(this).data('product'),
                    jobcart_item_id: $(this).data('jobcart-item'),
                    jobcart_id: $(this).data('jobcart'),
                    quantity: quantity,
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Assigned',
                        text: response.message
                    }).then(() => location.reload());
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong'
                    });
                }
            });
        });
    </script>
@endpush
