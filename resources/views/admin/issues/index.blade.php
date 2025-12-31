@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Issues Management</h4>
                {{-- Filter by Status --}}
                <div class="mb-3 d-flex align-items-end">
                    <a href="{{ route('admin.issues.create') }}" class="btn btn-primary">Create New Issue</a>
                </div>
            </div>

            <div class="card-body">
                @if ($issues->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-capitalize">Issue Title</th>
                                    <th class="text-capitalize">Status</th>
                                    <th class="text-capitalize">Created At</th>
                                    <th class="text-capitalize">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($issues as $issue)
                                    <tr>
                                        <td>{{ $issue->title ?? 'N/A' }}</td>
                                        <td>{{ $issue->is_active ? 'Active' : 'In Active' }}</td>


                                        <td>{{ $issue->created_at->format('d M Y, H:i') }}</td>

                                        <td class="text-center">
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                        <i class="icon-menu9"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">

                                                        <a href="{{ route($role_slug . '.issues.edit', $issue->id) }}"
                                                            class="dropdown-item">
                                                            <i class="icon-pencil7"></i> Edit
                                                        </a>
                                                        <form
                                                            action="{{ route($role_slug . '.issues.destroy', $issue->id) }}"
                                                            method="POST" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="icon-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-end my-2">
                        {{ $issues->links() }}
                    </div>
                @else
                    <p class="text-center">No Job Carts found.</p>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('status-filter').addEventListener('change', function() {
            let status = this.value;
            let url = new URL(window.location.href);

            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }

            window.location.href = url.toString();
        });
    </script>
@endpush
