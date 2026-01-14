@extends('layouts.admin')

@section('title', 'Issued Advances')

@section('content')
    <div class="page-header page-header-light d-flex justify-content-between align-items-center">
        <h4>Issued Advances</h4>
        <a href="{{ route('advance.create') }}" class="btn btn-primary">Create New Advance</a>
    </div>

    <div class="content mt-3">
        <div class="card">
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($advances->count())
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Driver</th>
                                <th>Advance Date</th>
                                <th>Amount</th>
                                <th>Remaining</th>
                                <th>Remarks</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($advances as $key => $advance)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $advance->driver->full_name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($advance->advance_date)->format('d M, Y') }}</td>
                                    <td>{{ number_format($advance->amount, 2) }}</td>
                                    <td>{{ number_format($advance->remaining_amount, 2) }}</td>
                                    <td>{{ $advance->remarks }}</td>
                                    <td>
                                        @if ($advance->is_closed)
                                            <span class="badge bg-success">Closed</span>
                                        @else
                                            <span class="badge bg-warning">Open</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">No advances issued yet.</div>
                @endif

            </div>
        </div>
    </div>
@endsection
