@extends('layouts.admin')

@section('title', 'Saved Drafts')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Saved Drafts</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
        </div>
    </div>

    <div class="content">
        @if ($message = Session::get('success'))
            <div id="alert-message" class="alert alert-success alert-dismissible alert-dismissible-2" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path class="heroicon-ui"
                            d="M16.24 14.83a1 1 0 0 1-1.41 1.41L12 13.41l-2.83 2.83a1 1 0 0 1-1.41-1.41L10.59 12 7.76 9.17a1 1 0 0 1 1.41-1.41L12 10.59l2.83-2.83a1 1 0 0 1 1.41 1.41L13.41 12l2.83 2.83z">
                        </path>
                    </svg>
                </button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table datatable-colvis-basic dataTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Module</th>
                            <th>Files</th>
                            <th>Draft At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($drafts as $draft)
                            <tr>
                                <td>{{ $draft->id }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $draft->module)) }}</td>
                                <td>
                                    @if ($draft->file_info && count($draft->file_info) > 0)
                                        <span class="badge badge-success">{{ count($draft->file_info) }} file(s)</span>
                                        <div class="mt-1">
                                            @foreach ($draft->file_info as $fieldName => $fileInfo)
                                                <small class="text-muted d-block">
                                                    <i class="icon-file"></i>
                                                    <a href="{{ route('drafts.view', base64_encode($fileInfo['path'])) }}"
                                                        target="_blank" class="text-primary">
                                                        {{ $fileInfo['original_name'] }}
                                                    </a>
                                                    <span
                                                        class="text-muted">({{ number_format($fileInfo['size'] / 1024, 1) }}
                                                        KB)</span>
                                                </small>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge badge-secondary">No files</span>
                                    @endif
                                </td>
                                <td>{{ $draft->updated_at->format('Y-m-d H:i:s') }}</td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('drafts.edit', $draft) }}" class="dropdown-item">
                                                    <i class="icon-eye"></i> Edit Draft
                                                </a>
                                                <form action="{{ route('drafts.destroy', $draft) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger"
                                                        onclick="return confirm('Are you sure you want to delete this draft?')">
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
        </div>
    </div>

    <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>
    <script src="{{ asset('assets/js/demo_pages/datatables_extension_colvis.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.datatable-colvis-basic').DataTable();
        });

        setTimeout(function() {
            let alertBox = document.getElementById('alert-message');
            if (alertBox) {
                alertBox.style.transition = 'opacity 0.5s ease';
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }
        }, 3000);
    </script>
@endsection
