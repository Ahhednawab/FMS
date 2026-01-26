@extends('layouts.admin')


@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Alerts</h3>
            <a href="{{ route('alerts.create') }}" class="btn btn-primary">
                Add Alert
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Threshold (KM)</th>
                    <th>Created At</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                    <tr>
                        <td>{{ $alert->title }}</td>
                        <td>{{ number_format($alert->threshold) }}</td>
                        <td>{{ $alert->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('alerts.edit', $alert->id) }}" class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            <form action="{{ route('alerts.destroy', $alert->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this alert?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No alerts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $alerts->links() }}
    </div>
@endsection
