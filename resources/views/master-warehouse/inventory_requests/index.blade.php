@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-boxes"></i> My Inventory Requests
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
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {

            function handleAction(button, url) {
                let requestId = button.data('id'); // Get the request ID

                button.prop('disabled', true).text('Processing...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        request_id: requestId // send request_id to controller
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

                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
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
                handleAction(button, "{{ url('master-warehouse/inventory-requests') }}/" + id + "/approve");
            });

            // Reject button click
            $('.reject-btn').click(function() {
                let button = $(this);
                let id = button.data('id');
                handleAction(button, "{{ url('master-warehouse/inventory-requests') }}/" + id + "/reject");
            });

        });
    </script>
@endpush
