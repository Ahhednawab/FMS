@extends('layouts.admin')

@section('title', 'Vehicle Maintenance Configuration')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i>
                    <span class="font-weight-semibold">Vehicle Maintenance Configuration</span>
                </h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>

            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('vehicleMaintenanceConfigurations.create') }}" class="btn btn-primary">
                        <span>Add Configuration <i class="icon-plus3 ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div class="content">
        @if ($message = Session::get('success'))
            <div id="alert-message" class="alert alert-success alert-dismissible alert-dismissible-2" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
            </div>
        @elseif ($message = Session::get('delete_msg'))
            <div id="alert-message" class="alert alert-danger alert-dismissible alert-dismissible-2" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-3">
                    Each row defines the predictive maintenance intervals (in KM) for a Vehicle Make and Model.
                    Values are used to automatically calculate upcoming and due maintenance alerts.
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Make</th>
                                <th>Model</th>
                                @foreach ($items as $name => $column)
                                    <th class="text-right text-nowrap">{{ $name }}</th>
                                @endforeach
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($configurations as $configuration)
                                <tr>
                                    <td class="text-nowrap font-weight-semibold">{{ $configuration->make }}</td>
                                    <td>{{ $configuration->model }}</td>
                                    @foreach ($items as $name => $column)
                                        <td class="text-right">
                                            {{ $configuration->{$column} !== null ? number_format($configuration->{$column}) : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="{{ route('vehicleMaintenanceConfigurations.edit', $configuration->id) }}" class="dropdown-item">
                                                        <i class="icon-pencil7"></i> Edit
                                                    </a>
                                                    <form method="POST" action="{{ route('vehicleMaintenanceConfigurations.destroy', $configuration->id) }}"
                                                        onsubmit="return confirm('Are you sure?');">
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
                            @empty
                                <tr>
                                    <td colspan="{{ count($items) + 3 }}" class="text-center text-muted">
                                        No configurations found. Click "Add Configuration" to create one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /content area -->

    <script>
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
