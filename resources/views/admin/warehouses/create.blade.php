@extends('layouts.admin')

@section('title', 'Warehouse Management')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Warehouse Management</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route($role_slug . '.warehouses.index') }}" class="btn btn-primary">
                        <span>View Warehouse <i class="icon-list ml-2"></i></span>
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
                        <form action="{{ route($role_slug . '.warehouses.store') }}" method="POST">
                            @csrf
                            @if (isset($draftId))
                                <input type="hidden" name="draft_id" value="{{ $draftId }}">
                            @endif

                            <div class="row">
                                <!-- Serial No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Serial No</strong>
                                        <input type="text" name="serial_no" class="form-control"
                                            value="{{ $serial_no }}" readonly>
                                    </div>
                                </div>

                                <!-- warehouse name -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Warehouse Name</strong>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ $draftData['name'] ?? old('name') }}">
                                        @if ($errors->has('name'))
                                            <label class="text-danger">{{ $errors->first('name') }}</label>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Type</strong>
                                        <select name="type" id="type" class="form-control warehouse-type">
                                            <option selected value="">--Select--</option>
                                            @foreach ($types as $value)
                                                <option value="{{ $value }}">
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('type'))
                                            <label class="text-danger">{{ $errors->first('type') }}</label>
                                        @endif
                                    </div>
                                </div>
                                <!-- Stations -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Stations</strong>
                                        <select name="station_id" id="station_id" class="form-control">
                                            <option value="">--Select--</option>
                                            @foreach ($stations as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ ($draftData['station_id'] ?? old('station_id')) == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('station_id'))
                                            <label class="text-danger">{{ $errors->first('station_id') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Warehouse Manager -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Warehouse Manager</strong>
                                        <select name="manager_id" id="manager_id" class="form-control">
                                            <option value="">--Select--</option>

                                        </select>
                                        @if ($errors->has('manager_id'))
                                            <label class="text-danger">{{ $errors->first('manager_id') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Buttons -->
                                <div class="col-md-12">
                                    <label for=""></label>
                                    <div class="text-right">
                                        <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                                            <i class="icon-save"></i> Draft
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="icon-check"></i> Save
                                        </button>
                                        <a href="{{ route($role_slug . '.warehouses.index') }}"
                                            class="btn btn-warning">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /content area -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country_id');
            const citySelect = document.getElementById('city_id');

            function filterCities() {
                const selectedCountry = countrySelect.value;
                [...citySelect.options].forEach(option => {
                    if (!option.value) return; // keep default
                    option.style.display = option.getAttribute('data-country') === selectedCountry ?
                        'block' : 'none';
                });
                citySelect.value = '';
            }

            countrySelect.addEventListener('change', filterCities);
            filterCities(); // run on load in case of edit
        });
    </script>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {

            $('.warehouse-type').on('change', function() {
                let type = $(this).val();
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

                        if (!response.success) {
                            alert(response.message);
                            return;
                        }

                        if ($.isEmptyObject(response.data)) {
                            managerSelect.append(
                                '<option value="">No managers available</option>');
                            return;
                        }

                        $.each(response.data, function(id, name) {
                            managerSelect.append(
                                `<option value="${id}">${name}</option>`
                            );
                        });
                    },
                    error: function() {
                        managerSelect.html('<option value="">Server error</option>');
                    }
                });
            });

        });
    </script>
@endpush
