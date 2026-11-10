@extends('layouts.admin')

@section('title', 'Alerts')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-bell2 mr-2"></i> <span class="font-weight-semibold">Alerts</span></h4>
            </div>
            <div class="header-elements d-none">
                <a href="{{ route('alerts.settings') }}" class="btn btn-light">
                    <i class="icon-cog3 mr-1"></i> Alert Settings
                </a>
            </div>
        </div>
    </div>

    <div class="content">
        @if (session('success'))
            <div id="alert-message" class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3 alert-filter-card">
            <div class="card-body">
                <form method="GET" action="{{ route('alerts.index') }}" id="alertFilterForm">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label><strong>Alert Type</strong></label>
                            <select name="type" id="alertType" class="form-control select2">
                                @foreach ($types as $key => $label)
                                    <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label><strong>{{ $titleLabel }}</strong></label>
                            <select name="title" class="form-control select2">
                                <option value="">All</option>
                                @foreach ($titleOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ in_array((string) $value, $filters['title'], true) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if ($referenceFilter)
                            <div class="col-md-3 form-group">
                                <label><strong>{{ $referenceFilter === 'driver' ? 'Driver' : 'Vehicle' }}</strong></label>
                                <select name="reference_id" class="form-control select2">
                                    <option value="">All</option>
                                    @foreach ($referenceOptions as $id => $label)
                                        <option value="{{ $id }}"
                                            {{ (string) $filters['reference_id'] === (string) $id ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-3 form-group">
                            <label><strong>Search</strong></label>
                            @if ($itemOptions)
                                {{-- Maintenance alerts have a fixed list of alert names, so the
                                     search box offers them instead of free text. --}}
                                <select name="item[]" class="form-control select2-multiple" multiple>
                                    @foreach ($itemOptions as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ in_array((string) $value, $filters['item'], true) ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control"
                                    placeholder="Vehicle, driver or message...">
                            @endif
                        </div>

                        <div class="col-md-2 form-group">
                            <label><strong>Show</strong></label>
                            <select name="per_page" class="form-control">
                                @foreach ([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 form-group d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">Search</button>
                            <a href="{{ route('alerts.index', ['type' => $type]) }}" class="btn btn-light mr-2">Clear Filters</a>
                            <a href="{{ route('alerts.export', array_filter(['type' => $type, 'title' => $filters['title'], 'item' => $filters['item'], 'reference_id' => $filters['reference_id'], 'search' => $filters['search']])) }}"
                                class="btn btn-success">
                                <i class="icon-file-excel mr-1"></i> Export Report
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 font-weight-semibold">{{ $types[$type] }}</h6>
                    <span class="text-muted">{{ number_format($alerts->total()) }} alert(s)</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Alert</th>
                                <th>Message</th>
                                <th style="width: 12%;">{{ $referenceLabel }}</th>
                                <th style="width: 9%;">Current</th>
                                <th style="width: 9%;">Due</th>
                                <th style="width: 10%;">Difference</th>
                                <th style="width: 13%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($alerts as $row)
                                <tr>
                                    <td><span class="badge badge-{{ $row['badge'] }}">{{ $row['title'] }}</span></td>
                                    <td>{{ $row['message'] }}</td>
                                    <td>{{ $row['reference'] }}</td>
                                    <td>{{ $row['current'] }}</td>
                                    <td>{{ $row['due'] }}</td>
                                    <td>{{ $row['difference'] }}</td>
                                    <td class="text-center">
                                        @if ($row['action_url'])
                                            <a href="{{ $row['action_url'] }}" class="btn btn-primary btn-sm mb-1">
                                                {{ $row['action_label'] }}
                                            </a>
                                        @endif

                                        @if ($row['mark_done'])
                                            <form action="{{ route('alerts.markDone') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="type" value="{{ $row['mark_done']['type'] }}">
                                                <input type="hidden" name="title" value="{{ $row['mark_done']['title'] }}">
                                                <input type="hidden" name="reference_id"
                                                    value="{{ $row['mark_done']['reference_id'] }}">
                                                <button class="btn btn-success btn-sm mb-1"
                                                    onclick="return confirm('Mark this alert as done?')">
                                                    Mark as Done
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No alerts found for this filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($alerts->hasPages())
                    <div class="mt-3">
                        {{ $alerts->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $('.alert-filter-card select.select2').select2({
                width: '100%',
                allowClear: false
            });

            // The search box lists the alert names and takes several at once.
            // Stock select2 moves the search input inline into the field for
            // multi-selects, so the adapters are composed explicitly to keep it
            // inside the dropdown while the chosen alerts show as chips above.
            $.fn.select2.amd.require([
                'select2/utils',
                'select2/selection/multiple',
                'select2/selection/placeholder',
                'select2/selection/allowClear',
                'select2/selection/eventRelay',
                'select2/dropdown',
                'select2/dropdown/search',
                'select2/dropdown/attachBody'
            ], function (Utils, MultipleSelection, Placeholder, AllowClear, EventRelay, Dropdown, DropdownSearch, AttachBody) {
                var SelectionAdapter = Utils.Decorate(MultipleSelection, Placeholder);
                SelectionAdapter = Utils.Decorate(SelectionAdapter, AllowClear);
                SelectionAdapter = Utils.Decorate(SelectionAdapter, EventRelay);

                var DropdownAdapter = Utils.Decorate(Utils.Decorate(Dropdown, DropdownSearch), AttachBody);

                $('.alert-filter-card select.select2-multiple').select2({
                    width: '100%',
                    placeholder: 'Search alerts...',
                    allowClear: true,
                    selectionAdapter: SelectionAdapter,
                    dropdownAdapter: DropdownAdapter
                });
            });

            // Changing the type loads that type's own list: the other filters
            // belong to the previous type, so none of them are carried over.
            $('#alertType').on('change', function () {
                window.location = '{{ route('alerts.index') }}?type=' + encodeURIComponent(this.value);
            });

            setTimeout(() => $('#alert-message').fadeOut(), 4000);
        });
    </script>

    <style>
        .alert-filter-card label {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .alert-filter-card .form-control,
        .alert-filter-card .select2-container--default .select2-selection--single {
            height: 36px;
            min-height: 36px;
            font-size: 13px;
        }

        .alert-filter-card .select2-container--default .select2-selection--single {
            padding-top: 3px;
        }

        /* Multi-select filter: same height and border as the other inputs
           (select2 keeps its darker border while a field has keyboard focus) */
        .alert-filter-card .select2-container--default .select2-selection--multiple {
            position: relative;
            min-height: 36px;
            font-size: 13px;
        }

        .alert-filter-card .select2-container--default:not(.select2-container--focus) .select2-selection--multiple {
            border-color: #ddd;
        }

        /* "All" placeholder lined up with the other inputs' placeholder text,
           positioned absolutely so it can never change the field's height. */
        .alert-filter-card .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
            position: absolute;
            top: 0;
            left: 14px;
            margin: 0;
            line-height: 34px;
            color: #999;
            list-style: none;
        }
    </style>
@endsection
