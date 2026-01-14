@extends('layouts.admin')

@section('title', 'Add Salary')

@section('content')

    <div class="page-header page-header-light">
        <div class="page-header-content">
            <h4>Add Salary</h4>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <div class="card-body">

                {{-- FILTERS --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Salary Month</label>
                        <input type="text" name="month" class="form-control date-picker" autocomplete="off"
                            value="{{ request('month') }}">
                    </div>

                    <div class="col-md-3">
                        <label>Driver</label>
                        <select name="driver_id" class="form-control filter">
                            <option value="">-- All Drivers --</option>
                            @foreach ($allDrivers as $driver)
                                <option value="{{ $driver->id }}" {{ $driver->id == $driverId ? 'selected' : '' }}>
                                    {{ $driver->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Rows per page</label>
                        <select name="per_page" class="form-control filter">
                            @foreach ([5, 10, 25, 50, 100] as $count)
                                <option value="{{ $count }}" {{ $count == $perPage ? 'selected' : '' }}>
                                    {{ $count }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- TABLE --}}
                <div id="salary-table">
                    @include('admin.salaries.partials.table')
                </div>

            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .table-responsive {
            overflow-x: auto;
        }

        /* fixed width inputs */
        .table-responsive input.form-control,
        .table-responsive select.form-control {
            width: 200px !important;
            min-width: 200px;
            max-width: 200px;
        }

        /* remarks wider */
        .table-responsive td:nth-child(21) input {
            width: 250px !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>

    <script>
        /* -----------------------------
                                                                            DATE PICKER (MONTH)
                                                                        ----------------------------- */
        $('.date-picker').datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true, // show Done button
            dateFormat: 'yy-mm',
            onClose: function(dateText, inst) {
                let month = inst.selectedMonth + 1;
                let year = inst.selectedYear;
                month = month < 10 ? '0' + month : month;
                $(this).val(year + '-' + month);
                loadTable();
            },
            beforeShow: function(input, inst) {
                // Hide the calendar grid
                $(inst.dpDiv).addClass('month-year-only');
            }
        });

        /* -----------------------------
           LOAD TABLE (AJAX)
        ----------------------------- */
        function loadTable(params = {}) {
            let mergedParams = {
                month: $('.date-picker').val(),
                driver_id: $('select[name="driver_id"]').val(),
                per_page: $('select[name="per_page"]').val()
            };

            params = Object.assign(mergedParams, params);

            $.get("{{ route('salaries.create') }}", params, function(html) {
                $('#salary-table').html(html);
                initCalculations();
            });
        }

        $(document).on('change', '.filter', function() {
            loadTable({
                page: 1
            });
        });

        $(document).on('click', '#salary-table .pagination a', function(e) {
            e.preventDefault();
            let url = new URL($(this).attr('href'));
            loadTable({
                page: url.searchParams.get('page')
            });
        });

        /* -----------------------------
           CALCULATE (INDEX-BASED)
        ----------------------------- */
        function calculate(row) {
            let driverId = row.data('driver-id');

            if (!driverId) return;

            let basic = parseFloat(
                $('[name="drivers[' + driverId + '][basic]"]').val()
            ) || 0;

            let deduction = parseFloat(
                $('[name="drivers[' + driverId + '][deduction]"]').val()
            ) || 0;

            let advance = parseFloat(
                $('[name="drivers[' + driverId + '][advance_deduction]"]').val()
            ) || 0;
            // Clamp advance deduction to the issued advance for this row
            let advanceIssued = parseFloat(row.data('advance-issued')) || 0;
            if (advance > advanceIssued) {
                advance = advanceIssued;
                let $advanceInput = $('[name="drivers[' + driverId + '][advance_deduction]"]');
                $advanceInput.val(advance).addClass('is-invalid');
                setTimeout(function() {
                    $advanceInput.removeClass('is-invalid');
                }, 2000);
            } else {
                $('[name="drivers[' + driverId + '][advance_deduction]"]').removeClass('is-invalid');
            }

            let overtime = parseFloat(
                $('[name="drivers[' + driverId + '][overtime]"]').val()
            ) || 0;

            let extra = parseFloat(
                $('[name="drivers[' + driverId + '][extra]"]').val()
            ) || 0;

            let gross = basic - deduction - advance + overtime + extra;
            gross = Math.max(0, gross);


            $('[name="drivers[' + driverId + '][gross]"]').val(gross);
            console.log($('[name="drivers[' + driverId + '][gross]"]').parent().children('.display-text').text());
            console.log(gross);
            $('[name="drivers[' + driverId + '][gross]"]').parent().children('.display-text').text(gross);


            let totalRecovered = parseFloat(
                $('[name="drivers[' + driverId + '][total_recovered]"]').val()
            ) || 0;

            let remaining = gross;

            $('[name="drivers[' + driverId + '][remaining_amount]"]').closest('.display-text').text(remaining);
            $('[name="drivers[' + driverId + '][remaining_amount]"]').val(remaining);
            // $('.remaining_amount[data-driver-id="' + driverId + '"]')
            //     .text(remaining);
        }

        /* -----------------------------
           AUTOSAVE
        ----------------------------- */
        function autoSave(row) {
            let driverId = row.data('driver-id');
            let month = $('.date-picker').val();
            if (!driverId || !month) return;

            let gross = parseFloat(
                $('.gross[data-driver-id="' + driverId + '"]').text()
            ) || 0;

            let remaining = parseFloat(
                $('.remaining_amount[data-driver-id="' + driverId + '"]').text()
            ) || 0;

            $.post("{{ route('salaries.save-single') }}", {
                _token: "{{ csrf_token() }}",
                salary_month: month,
                driver_id: driverId,

                overtime: $('[name="drivers[' + driverId + '][overtime]"]').val(),
                deduction: $('[name="drivers[' + driverId + '][deduction]"]').val(),
                advance_deduction: $('[name="drivers[' + driverId + '][advance_deduction]"]').val(),
                extra: $('[name="drivers[' + driverId + '][extra]"]').val(),
                paid_absent: $('[name="drivers[' + driverId + '][paid_absent]"]').val(),
                total_recovered: $('[name="drivers[' + driverId + '][total_recovered]"]').val(),
                gross_salary: gross,
                remaining_amount: remaining,
                remarks: $('[name="drivers[' + driverId + '][remarks]"]').val(),
                status: $('[name="drivers[' + driverId + '][status]"]').val()
            });
        }

        /* -----------------------------
           INIT EVENTS
        ----------------------------- */
        function initCalculations() {
            $('#salary-table')
                .off('input.salary change.salary')
                .on('input.salary change.salary',
                    '.salary-row input.calc, .salary-row input[name$="[total_recovered]"]',
                    function() {
                        let row = $(this).closest('.salary-row');
                        calculate(row);
                        autoSave(row);
                    }
                );

            $('#salary-table')
                .off('change.remarks')
                .on('change.remarks',
                    '.salary-row select[name$="[status]"], .salary-row input[name$="[remarks]"]',
                    function() {
                        let row = $(this).closest('.salary-row');

                        // Handle status change - show/hide inputs
                        if ($(this).is('select[name$="[status]"]')) {
                            let status = $(this).val();
                            let isPaid = status === 'paid';

                            // Toggle display for all input fields and text spans
                            row.find('.display-text').each(function() {
                                $(this).css('display', isPaid ? 'inline' : 'none');
                            });

                            row.find(
                                'input[name$="[paid_absent]"], input[name$="[overtime]"], input[name$="[deduction]"], input[name$="[extra]"], input[name$="[advance_issued]"], input[name$="[advance_deduction]"], input[name$="[total_recovered]"], input[name$="[remaining_amount]"], input[name$="[remarks]"], input[name$="[gross]"]'
                            ).each(function() {
                                $(this).css('display', isPaid ? 'none' : 'block');
                            });
                        }

                        autoSave(row);
                    }
                );

            $('.salary-row').each(function() {
                calculate($(this));
            });
        }

        initCalculations();
    </script>
@endpush
