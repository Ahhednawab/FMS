@extends('layouts.admin')

@section('title', 'Create Advance')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content">
            <h4>Create New Advance</h4>
        </div>
    </div>

    <div class="content mt-3">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('advance.store') }}" method="POST">
                    @csrf

                    {{-- DRIVER --}}
                    <div class="mb-3">
                        <label>Driver</label>
                        <select name="driver_id" class="form-control select2" required>
                            <option value="">-- Select Driver --</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}">
                                    {{ $driver->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ADVANCE DATE --}}
                    <div class="mb-3">
                        <label>Advance Date</label>
                        <input type="date" name="advance_date" class="form-control" required>
                    </div>

                    {{-- AMOUNT --}}
                    <div class="mb-3">
                        <label>Advance Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>

                    {{-- PER MONTH DEDUCTION --}}
                    <div class="mb-3">
                        <label>Per Month Deduction</label>
                        <input type="number" step="0.01" name="per_month_deduction" class="form-control" required
                            id="per_month_deduction">
                        <small class="text-muted">
                            Amount to deduct from salary each month (cannot exceed total advance amount)
                        </small>
                    </div>


                    {{-- REMARKS --}}
                    <div class="mb-3">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            Save Advance
                        </button>
                        <a href="{{ route('advance.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        (function() {
            function loadSelect2AndInit(selector, options) {
                options = options || {
                    placeholder: '-- Select Driver --',
                    allowClear: true,
                    width: '100%'
                };

                if (typeof $().select2 === 'function') {
                    $(selector).select2(options);
                    return;
                }

                // Load CSS
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css';
                document.head.appendChild(link);

                // Load JS
                var script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
                script.onload = function() {
                    try {
                        $(selector).select2(options);
                    } catch (e) {
                        console.error('Select2 init failed', e);
                    }
                };
                document.body.appendChild(script);
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Select2 on driver dropdown (with CDN fallback)
                loadSelect2AndInit('.select2');

                const amountInput = document.querySelector('input[name="amount"]');
                const deductionInput = document.querySelector('input[name="per_month_deduction"]');

                function validateDeduction() {
                    const amount = parseFloat(amountInput.value) || 0;
                    const deduction = parseFloat(deductionInput.value) || 0;

                    if (deduction > amount) {
                        alert('Per month deduction cannot be greater than the total advance amount.');
                        deductionInput.value = amount; // reset to max allowed
                    }
                }

                // Validate whenever user types in the deduction field
                deductionInput.addEventListener('input', validateDeduction);

                // Also validate if the user changes the total amount
                amountInput.addEventListener('input', validateDeduction);
            });
        })();
    </script>
@endpush
