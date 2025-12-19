@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-boxes"></i> My Inventory Requests
                        </h3>

                        <!-- Status filter dropdown as a button with Select2 -->
                        <form method="GET" class="d-flex align-items-center">
                            <label for="status" class="me-2 text-white mb-0 mx-2">Filter:</label>
                            <select name="status" id="status" class="form-select form-select-sm select2-status"
                                style="width: 150px;">
                                @php
                                    $statuses = [
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                    ];
                                @endphp
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ ($status ?? 'pending') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

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
                                            <th>Requested At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($requests as $request)
                                            <tr>
                                                <td>{{ $loop->iteration + ($requests->currentPage() - 1) * $requests->perPage() }}
                                                </td>

                                                <td>
                                                    <strong>{{ $request->inventory->product->name ?? 'N/A' }}</strong>
                                                </td>

                                                <td>{{ $request->quantity }}</td>

                                                <td>Rs. {{ number_format($request->price, 2) }}</td>

                                                <td>
                                                    @if ($request->status == 'pending')
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @elseif($request->status == 'approved')
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($request->status == 'rejected')
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    {{ \Carbon\Carbon::parse($request->created_at)->format('d M Y h:i A') }}
                                                </td>

                                                <td>
                                                    @if ($request->status === 'pending')
                                                        <button class="btn btn-sm btn-success approve-btn"
                                                            data-id="{{ $request->id }}">
                                                            Approve
                                                        </button>
                                                        <button class="btn btn-sm btn-danger reject-btn"
                                                            data-id="{{ $request->id }}">
                                                            Reject
                                                        </button>
                                                    @else
                                                        <span class="text-muted">Processed</span>
                                                    @endif
                                                </td>
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

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 on the status filter
            $('.select2-status').select2({
                theme: 'bootstrap-5', // optional, if you use Bootstrap 5
                minimumResultsForSearch: Infinity // disables search box
            });

            // Submit form on change
            $('.select2-status').on('change', function() {
                $(this).closest('form').submit();
            });
        });
    </script>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {

            function handleAction(button, url) {
                let requestId = button.data('id');

                button.prop('disabled', true).text('Processing...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        request_id: requestId
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Slide up row and remove
                            button.closest('tr').slideUp(500, function() {
                                $(this).remove();
                            });

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message
                            });
                            button.prop('disabled', false).text(button.hasClass('approve-btn') ?
                                'Approve' : 'Reject');
                        }
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Something went wrong';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                        button.prop('disabled', false).text(button.hasClass('approve-btn') ? 'Approve' :
                            'Reject');
                    }
                });
            }

            // Approve button click
            $('.approve-btn').click(function() {
                let button = $(this);
                let id = button.data('id');
                handleAction(button, "{{ url('master-warehouse/inventory-requests') }}/" + id +
                    "/approve");
            });

            // Reject button click
            $('.reject-btn').click(function() {
                let button = $(this);
                let requestId = button.data('id');

                Swal.fire({
                    title: 'Reason for rejection',
                    input: 'textarea',
                    inputPlaceholder: 'Enter reason...',
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    preConfirm: (reason) => {
                        if (!reason) {
                            Swal.showValidationMessage('Reason is required');
                        }
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/master-warehouse/inventory-requests/${requestId}/reject`,
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                reason: result.value
                            },
                            dataType: "json",
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Rejected',
                                        text: res.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    button.closest('tr').slideUp();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: res.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON?.message ||
                                    'Something went wrong';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: msg
                                });
                            }
                        });
                    }
                });
            });


        });
    </script>
@endpush
