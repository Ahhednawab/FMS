@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Add Invoice</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('invoices.store') }}">
                @csrf

                {{-- BASIC INFO --}}
                <h6 class="font-weight-bold mb-3">Basic Information</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>DP No</label>
                        <input type="text" name="dp_no" class="form-control @error('dp_no') is-invalid @enderror"
                            value="{{ old('dp_no') }}">
                        @error('dp_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Invoice No</label>
                        <input type="text" name="invoice_no" class="form-control @error('invoice_no') is-invalid @enderror"
                            value="{{ old('invoice_no') }}">
                        @error('invoice_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>PO No</label>
                        <input type="text" name="po_no" class="form-control @error('po_no') is-invalid @enderror"
                            value="{{ old('po_no') }}">
                        @error('po_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Clearance Indication</label>
                        <select name="clearance_indication" class="form-control @error('clearance_indication') is-invalid @enderror">
                            <option value="">Select Clearance</option>
                            @foreach ($clearanceIndications as $value => $label)
                                <option value="{{ $value }}" {{ old('clearance_indication') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('clearance_indication')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- DATES --}}
                <h6 class="font-weight-bold mb-3">Dates</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Invoice Month</label>
                        <input type="date" name="invoice_month" class="form-control @error('invoice_month') is-invalid @enderror"
                            value="{{ old('invoice_month') }}">
                        @error('invoice_month')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Invoice Date</label>
                        <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                            value="{{ old('invoice_date') }}">
                        @error('invoice_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Submission Date</label>
                        <input type="date" name="submission_date" class="form-control @error('submission_date') is-invalid @enderror"
                            value="{{ old('submission_date') }}">
                        @error('submission_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                            value="{{ old('due_date') }}">
                        @error('due_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- VEHICLE DETAILS --}}
                {{-- VEHICLE DETAILS --}}
                <h6 class="font-weight-bold mb-3">Vehicle Details</h6>

                <div id="vehicle-wrapper">
                    @php
                        $vehicleRows = old('vehicles');
                        if (!is_array($vehicleRows) || count($vehicleRows) === 0) {
                            $vehicleRows = [[
                                'vehicle_qty' => '',
                                'days' => '',
                                'vehicle_rent' => '',
                                'monthly_rent' => '',
                            ]];
                        }
                    @endphp

                    @foreach ($vehicleRows as $i => $vehicleRow)
                        @php $vehicleRow = is_array($vehicleRow) ? $vehicleRow : []; @endphp
                        <div class="row vehicle-row mb-2" data-index="{{ $i }}">
                            <div class="col-md-2">
                                <input type="number" step="any" min="0" name="vehicles[{{ $i }}][vehicle_qty]"
                                    class="form-control invoice-vehicle-qty @error("vehicles.$i.vehicle_qty") is-invalid @enderror"
                                    placeholder="Qty"
                                    value="{{ old("vehicles.$i.vehicle_qty", $vehicleRow['vehicle_qty'] ?? '') }}">
                                @error("vehicles.$i.vehicle_qty")
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <input type="number" step="any" min="0" name="vehicles[{{ $i }}][days]"
                                    class="form-control @error("vehicles.$i.days") is-invalid @enderror"
                                    placeholder="Days"
                                    value="{{ old("vehicles.$i.days", $vehicleRow['days'] ?? '') }}">
                                @error("vehicles.$i.days")
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <input type="number" step="any" name="vehicles[{{ $i }}][vehicle_rent]"
                                    class="form-control invoice-vehicle-rent @error("vehicles.$i.vehicle_rent") is-invalid @enderror"
                                    placeholder="Vehicle Rent"
                                    value="{{ old("vehicles.$i.vehicle_rent", $vehicleRow['vehicle_rent'] ?? '') }}">
                                @error("vehicles.$i.vehicle_rent")
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <input type="number" step="any" name="vehicles[{{ $i }}][monthly_rent]"
                                    class="form-control invoice-monthly-rent @error("vehicles.$i.monthly_rent") is-invalid @enderror"
                                    readonly
                                    placeholder="Monthly Rent"
                                    value="{{ old("vehicles.$i.monthly_rent", $vehicleRow['monthly_rent'] ?? '') }}">
                                @error("vehicles.$i.monthly_rent")
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                @if ($i === 0)
                                    <button type="button" class="btn btn-success add-row">+</button>
                                @else
                                    <button type="button" class="btn btn-danger remove-row">−</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>


                {{-- CHARGES --}}
                <h6 class="font-weight-bold mb-3">Charges</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Sunday Gazette</label>
                        <input type="number" step="0.01" name="sunday_gazette"
                            class="form-control invoice-sunday-gazette @error('sunday_gazette') is-invalid @enderror"
                            value="{{ old('sunday_gazette') }}">
                        @error('sunday_gazette')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Control Room Charges</label>
                        <input type="number" step="0.01" name="control_room_charges"
                            class="form-control invoice-control-room @error('control_room_charges') is-invalid @enderror"
                            value="{{ old('control_room_charges') }}">
                        @error('control_room_charges')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Total Claim</label>
                        <input type="number" step="0.01" name="total_claim" class="form-control invoice-total-claim" readonly
                            value="{{ old('total_claim') }}">
                    </div>
                </div>

                {{-- TAX DETAILS --}}
                <h6 class="font-weight-bold mb-3">Tax Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Sales Tax (15%)</label>
                        <input type="number" step="0.01" name="sales_tax" class="form-control invoice-sales-tax" readonly
                            value="{{ old('sales_tax') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Inclusive Total</label>
                        <input type="number" step="0.01" name="inclusive_sales_tax" class="form-control invoice-inclusive-total" readonly
                            value="{{ old('inclusive_sales_tax') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Tax Value</label>
                        <input type="number" step="0.01" name="tax_value" class="form-control invoice-tax-value" readonly
                            value="{{ old('tax_value') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Withholding Tax</label>
                        <input type="number" step="0.01" name="withholding_on_sales_tax"
                            class="form-control invoice-withholding-tax" readonly
                            value="{{ old('withholding_on_sales_tax') }}">
                    </div>
                </div>

                {{-- PAYMENT DETAILS --}}
                <h6 class="font-weight-bold mb-3">Payment Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Net Payable Amount</label>
                        <input type="number" step="0.01" name="actual_payment" class="form-control invoice-net-payable" readonly
                            value="{{ old('actual_payment') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Agreed Deduction</label>
                        <input type="number" step="0.01" name="agreed_deduction"
                            class="form-control invoice-agreed-deduction @error('agreed_deduction') is-invalid @enderror"
                            value="{{ old('agreed_deduction') }}">
                        @error('agreed_deduction')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Amount Receivable</label>
                        <input type="number" step="0.01" name="cheque_value" class="form-control invoice-amount-receivable" readonly
                            value="{{ old('cheque_value') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Payment Received</label>
                        <input type="number" step="0.01" name="payment_received"
                            class="form-control invoice-payment-received @error('payment_received') is-invalid @enderror"
                            value="{{ old('payment_received') }}">
                        @error('payment_received')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- <div class="col-md-3 mb-3">
                        <label>Cheque No</label>
                        <input type="text" name="cheque_no" class="form-control">
                    </div> --}}
                </div>

                {{-- PAYMENT TIMELINE --}}
                <h6 class="font-weight-bold mb-3">Payment Timeline</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Amount Received Date</label>
                        <input type="date" name="cheque_rec_date" class="form-control @error('cheque_rec_date') is-invalid @enderror"
                            value="{{ old('cheque_rec_date') }}">
                        @error('cheque_rec_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- <div class="col-md-3 mb-3">
                        <label>Payment Timeline Days</label>
                        <input type="number" name="payment_time_line_days" class="form-control">
                    </div> --}}

                    <div class="col-md-3 mb-3">
                        <label>Payment Difference</label>
                        <input type="number" name="payment_difference_in_days"
                            class="form-control @error('payment_difference_in_days') is-invalid @enderror"
                            value="{{ old('payment_difference_in_days') }}">
                        @error('payment_difference_in_days')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Difference</label>
                        <input type="number" step="0.01" name="diff" class="form-control invoice-diff" readonly
                            value="{{ old('diff') }}">
                    </div>
                </div>

                {{-- ACTION --}}
                <div class="mt-4 text-right">
                    <button class="btn btn-success">Save Invoice</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Back</a>
                </div>

            </form>
        </div>
    </div>

    <script>
        // Tax rates come from InvoiceController so this live preview always
        // matches what the server recalculates and stores on save.
        const INVOICE_RATES = @json($invoiceRates);

        let rowIndex = {{ count($vehicleRows) }};

        function toNumber(value) {
            const parsed = parseFloat(value);
            return Number.isFinite(parsed) ? parsed : 0;
        }

        function roundMoney(value) {
            return Math.round((value + Number.EPSILON) * 100) / 100;
        }

        function recalculateInvoice() {
            let totalMonthlyRent = 0;

            document.querySelectorAll('.vehicle-row').forEach((row) => {
                const qtyInput = row.querySelector('.invoice-vehicle-qty');
                const rentInput = row.querySelector('.invoice-vehicle-rent');
                const monthlyRentInput = row.querySelector('.invoice-monthly-rent');

                const qty = toNumber(qtyInput?.value);
                const rent = toNumber(rentInput?.value);
                const monthlyRent = qty * rent;

                if (monthlyRentInput) {
                    monthlyRentInput.value = String(monthlyRent);
                }

                totalMonthlyRent += monthlyRent;
            });

            const sundayGazette = toNumber(document.querySelector('.invoice-sunday-gazette')?.value);
            const controlRoomCharges = toNumber(document.querySelector('.invoice-control-room')?.value);
            const agreedDeduction = toNumber(document.querySelector('.invoice-agreed-deduction')?.value);
            const paymentReceived = toNumber(document.querySelector('.invoice-payment-received')?.value);

            const totalClaim = roundMoney(totalMonthlyRent + sundayGazette + controlRoomCharges);
            const salesTax = roundMoney(totalClaim * INVOICE_RATES.salesTax);
            const inclusiveTotal = roundMoney(totalClaim + salesTax);
            const taxValue = roundMoney(inclusiveTotal * INVOICE_RATES.taxValue);
            const withholdingTax = roundMoney(salesTax * INVOICE_RATES.withholding);
            const netPayable = roundMoney(inclusiveTotal - withholdingTax - taxValue - agreedDeduction);
            const diff = roundMoney(netPayable - paymentReceived);

            document.querySelector('.invoice-total-claim').value = String(totalClaim);
            document.querySelector('.invoice-sales-tax').value = String(salesTax);
            document.querySelector('.invoice-inclusive-total').value = String(inclusiveTotal);
            document.querySelector('.invoice-tax-value').value = String(taxValue);
            document.querySelector('.invoice-withholding-tax').value = String(withholdingTax);
            document.querySelector('.invoice-net-payable').value = String(netPayable);
            document.querySelector('.invoice-amount-receivable').value = String(netPayable);
            document.querySelector('.invoice-diff').value = String(diff);
        }

        document.addEventListener('click', function(e) {

            // ADD ROW
            if (e.target.classList.contains('add-row')) {
                const wrapper = document.getElementById('vehicle-wrapper');
                const newRow = document.querySelector('.vehicle-row').cloneNode(true);

                newRow.setAttribute('data-index', rowIndex);

                newRow.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    input.name = input.name.replace(/\[\d+\]/, `[${rowIndex}]`);
                    if (input.type === 'number') {
                        input.step = 'any';
                    }
                });

                newRow.querySelector('.add-row').outerHTML =
                    '<button type="button" class="btn btn-danger remove-row">−</button>';

                wrapper.appendChild(newRow);
                rowIndex++;
                recalculateInvoice();
            }

            // REMOVE ROW
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('.vehicle-row').remove();
                recalculateInvoice();
            }

        });

        document.addEventListener('input', function(e) {
                if (e.target.matches('.invoice-vehicle-qty, .invoice-vehicle-rent, .invoice-sunday-gazette, .invoice-control-room, .invoice-agreed-deduction, .invoice-payment-received')) {
                    recalculateInvoice();
                }
            });

        recalculateInvoice();
    </script>
@endsection
