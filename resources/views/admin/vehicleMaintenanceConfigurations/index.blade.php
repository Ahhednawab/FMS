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

        {{-- FILTERS --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('vehicleMaintenanceConfigurations.index') }}">
                    <div class="row">
                        <div class="col-md-2 form-group">
                            <label>Make</label>
                            <select name="make" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($makes as $make)
                                    <option value="{{ $make }}" {{ request('make') === $make ? 'selected' : '' }}>{{ $make }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 form-group">
                            <label>Model</label>
                            <select name="model" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($models as $model)
                                    <option value="{{ $model }}" {{ request('model') === $model ? 'selected' : '' }}>{{ $model }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Maintenance Item</label>
                            <select name="item" class="form-control select2">
                                <option value="">All</option>
                                @foreach (array_keys($items) as $itemName)
                                    <option value="{{ $itemName }}" {{ request('item') === $itemName ? 'selected' : '' }}>{{ $itemName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 form-group">
                            <label>Interval</label>
                            <select name="interval_status" class="form-control">
                                <option value="configured" {{ request('interval_status') !== 'missing' ? 'selected' : '' }}>Configured</option>
                                <option value="missing" {{ request('interval_status') === 'missing' ? 'selected' : '' }}>Missing</option>
                            </select>
                            <small class="text-muted">Applies to the selected item.</small>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Search make or model...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="{{ route('vehicleMaintenanceConfigurations.index') }}" class="btn btn-light">Clear Filters</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-3">
                    Each row defines the predictive maintenance intervals (in KM) for a Vehicle Make and Model.
                    Values are used to automatically calculate upcoming and due maintenance alerts.
                    <span class="d-block">Showing <strong>{{ $configurations->count() }}</strong> configuration(s).</span>
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
                                        @if (request()->hasAny(['make', 'model', 'item', 'search']))
                                            No configurations match the selected filters.
                                            <a href="{{ route('vehicleMaintenanceConfigurations.index') }}">Clear filters</a>
                                        @else
                                            No configurations found. Click "Add Configuration" to create one.
                                        @endif
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
        $(function() {
            $('.select2').select2({ width: '100%' });
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
