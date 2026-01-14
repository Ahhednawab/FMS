@extends('layouts.admin')

@section('title', 'Edit Warehouse')

@section('content')

    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4>
                    <i class="icon-arrow-left52 mr-2"></i>
                    <span class="font-weight-semibold">Edit Warehouse</span>
                </h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none">
                    <i class="icon-more"></i>
                </a>
            </div>

            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('warehouses.index') }}" class="btn btn-primary">
                        View Warehouse <i class="icon-list ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">

                                <!-- Serial No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Serial No</strong>
                                        <input type="text" class="form-control" value="{{ $warehouse->serial_no }}"
                                            readonly>
                                    </div>
                                </div>

                                <!-- Warehouse Name -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Warehouse Name</strong>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $warehouse->name) }}">
                                        @error('name')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Warehouse Type -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Type</strong>
                                        <select name="type" class="form-control warehouse-type">
                                            <option value="">--Select--</option>
                                            @foreach ($types as $type)
                                                <option value="{{ $type }}">
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('type')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Stations -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Stations</strong>
                                        <select name="station_id" class="form-control">
                                            <option value="">--Select--</option>
                                            @foreach ($stations as $id => $name)
                                                <option value="{{ $id }}"
                                                    {{ old('station_id', $warehouse->station_id) == $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('station_id')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Warehouse Manager -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Warehouse Manager</strong>
                                        <select name="manager_id" id="manager_id" class="form-control">
                                            <option value="">--Select--</option>
                                        </select>
                                        @error('manager_id')
                                            <label class="text-danger">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                            <!-- Buttons -->
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="icon-check"></i> Update
                                    </button>
                                    <a href="{{ route('warehouses.index') }}" class="btn btn-warning">
                                        Cancel
                                    </a>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /content area -->

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            let selectedType = "{{ old('type', $warehouse->type) }}";
            let selectedManager = "{{ old('manager_id', $warehouse->manager_id) }}";

            function loadManagers(type, selectedManagerId = null) {
                let managerSelect = $('#manager_id');

                managerSelect.html('<option value="">Loading...</option>');

                if (!type) {
                    managerSelect.html('<option value="">--Select--</option>');
                    return;
                }

                $.ajax({
                    url: "{{ route('users.getmanagers') }}",
                    type: "GET",
                    data: {
                        type: type
                    },
                    success: function(response) {

                        managerSelect.empty();
                        managerSelect.append('<option value="">--Select--</option>');

                        if (!response.success || $.isEmptyObject(response.data)) {
                            managerSelect.append('<option value="">No managers available</option>');
                            return;
                        }

                        $.each(response.data, function(id, name) {
                            let selected = selectedManagerId == id ? 'selected' : '';
                            managerSelect.append(
                                `<option value="${id}" ${selected}>${name}</option>`
                            );
                        });
                    },
                    error: function() {
                        managerSelect.html('<option value="">Server error</option>');
                    }
                });
            }

            // Load on type change
            $('.warehouse-type').on('change', function() {
                loadManagers($(this).val());
            });

            // Load managers on edit page load
            if (selectedType) {
                loadManagers(selectedType, selectedManager);
            }
        });
    </script>
@endpush
