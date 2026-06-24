@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Invoice</h5>
            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                <i class="icon-arrow-left52 mr-1"></i> Back
            </a>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('invoices.update', $invoice) }}">
                @csrf
                @method('PUT')

                {{-- BASIC INFO --}}
                <h6 class="font-weight-bold mb-3">Basic Information</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>DP No</label>
                        <input type="text" name="dp_no" class="form-control"
                            value="{{ old('dp_no', $invoice->dp_no) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Invoice No</label>
                        <input type="text" name="invoice_no" class="form-control"
                            value="{{ old('invoice_no', $invoice->invoice_no) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>PO No</label>
                        <input type="text" name="po_no" class="form-control"
                            value="{{ old('po_no', $invoice->po_no) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Clearance Indication</label>
                        <select name="clearance_indication" class="form-control @error('clearance_indication') is-invalid @enderror">
                            <option value="">Select Clearance</option>
                            @foreach ($clearanceIndications as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('clearance_indication', $invoice->clearance_indication) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('clearance_indication')
                            <label class="text-danger">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- DATES --}}
                <h6 class="font-weight-bold mb-3">Dates</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Invoice Month</label>
                        <input type="date" name="invoice_month" class="form-control"
                            value="{{ old('invoice_month', optional($invoice->invoice_month)->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Invoice Date</label>
                        <input type="date" name="invoice_date" class="form-control"
                            value="{{ old('invoice_date', optional($invoice->invoice_date)->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Submission Date</label>
                        <input type="date" name="submission_date" class="form-control"
                            value="{{ old('submission_date', optional($invoice->submission_date)->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control"
                            value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}">
                    </div>
                </div>

                {{-- VEHICLE DETAILS --}}
                <h6 class="font-weight-bold mb-3">Vehicle Details</h6>

                {{-- LABEL ROW --}}
                <div class="row font-weight-bold mb-2">
                    <div class="col-md-2">Vehicle Qty</div>
                    <div class="col-md-2">Days</div>
                    <div class="col-md-3">Vehicle Rent</div>
                    <div class="col-md-3">Monthly Rent</div>
                    <div class="col-md-2 text-center">Action</div>
                </div>

                <div id="vehicle-wrapper">
                    @php
                        // 🔒 Always normalize to arrays
                        $vehicleQty = (array) ($invoice->vehicle_qty ?? []);
                        $days = (array) ($invoice->days ?? []);
                        $vehicleRent = (array) ($invoice->vehicle_rent ?? []);
                        $monthlyRent = (array) ($invoice->monthly_rent ?? []);

                        // If empty, add one row
                        if (count($vehicleQty) === 0) {
                            $vehicleQty = [''];
                            $days = [''];
                            $vehicleRent = [''];
                            $monthlyRent = [''];
                        }
                    @endphp

                    @foreach ($vehicleQty as $i => $qty)
                        <div class="row vehicle-row mb-2">
                            <div class="col-md-2">
                                <input type="number" step="any" min="0" name="vehicles[{{ $i }}][vehicle_qty]" class="form-control invoice-vehicle-qty"
                                    value="{{ old("vehicles.$i.vehicle_qty", $qty) }}">
                            </div>

                            <div class="col-md-2">
                                <input type="number" step="any" min="0" name="vehicles[{{ $i }}][days]" class="form-control"
                                    value="{{ old("vehicles.$i.days", $days[$i] ?? '') }}">
                            </div>

                            <div class="col-md-3">
                                <input type="number" step="any" name="vehicles[{{ $i }}][vehicle_rent]"
                                    class="form-control invoice-vehicle-rent"
                                    value="{{ old("vehicles.$i.vehicle_rent", $vehicleRent[$i] ?? '') }}">
                            </div>

                            <div class="col-md-3">
                                <input type="number" step="any" name="vehicles[{{ $i }}][monthly_rent]"
                                    class="form-control invoice-monthly-rent"
                                    readonly
                                    value="{{ old("vehicles.$i.monthly_rent", $monthlyRent[$i] ?? '') }}">
                            </div>

                            <div class="col-md-2 text-center">
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
                        <input type="number" step="0.01" name="sunday_gazette" class="form-control invoice-sunday-gazette"
                            value="{{ old('sunday_gazette', $invoice->sunday_gazette) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Control Room Charges</label>
                        <input type="number" step="0.01" name="control_room_charges" class="form-control invoice-control-room"
                            value="{{ old('control_room_charges', $invoice->control_room_charges) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Total Claim</label>
                        <input type="number" step="0.01" name="total_claim" class="form-control invoice-total-claim" readonly
                            value="{{ old('total_claim', $invoice->total_claim) }}">
                    </div>
                </div>

                {{-- TAX DETAILS --}}
                <h6 class="font-weight-bold mb-3">Tax Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Sales Tax (15%)</label>
                        <input type="number" step="0.01" name="sales_tax" class="form-control invoice-sales-tax" readonly
                            value="{{ old('sales_tax', $invoice->sales_tax) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Inclusive Total</label>
                        <input type="number" step="0.01" name="inclusive_sales_tax" class="form-control invoice-inclusive-total" readonly
                            value="{{ old('inclusive_sales_tax', $invoice->inclusive_sales_tax) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Tax Value</label>
                        <input type="number" step="0.01" name="tax_value" class="form-control invoice-tax-value" readonly
                            value="{{ old('tax_value', $invoice->tax_value) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Withholding Tax</label>
                        <input type="number" step="0.01" name="withholding_on_sales_tax" class="form-control invoice-withholding-tax" readonly
                            value="{{ old('withholding_on_sales_tax', $invoice->withholding_on_sales_tax) }}">
                    </div>
                </div>

                {{-- PAYMENT DETAILS --}}
                <h6 class="font-weight-bold mb-3">Payment Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Net Payable Amount</label>
                        <input type="number" step="0.01" name="actual_payment" class="form-control invoice-net-payable" readonly
                            value="{{ old('actual_payment', $invoice->actual_payment) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Agreed Deduction</label>
                        <input type="number" step="0.01" name="agreed_deduction" class="form-control invoice-agreed-deduction"
                            value="{{ old('agreed_deduction', $invoice->agreed_deduction) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Amount Receivable</label>
                        <input type="number" step="0.01" name="cheque_value" class="form-control invoice-amount-receivable" readonly
                            value="{{ old('cheque_value', $invoice->cheque_value) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Payment Received</label>
                        <input type="number" step="0.01"
                            name="payment_received"
                            class="form-control invoice-payment-received"
                            value="{{ old('payment_received', $invoice->payment_received) }}">
                    </div>


                    {{-- <div class="col-md-3 mb-3">
                        <label>Cheque No</label>
                        <input type="text" name="cheque_no" class="form-control"
                            value="{{ old('cheque_no', $invoice->cheque_no) }}">
                    </div> --}}
                </div>

                {{-- PAYMENT TIMELINE --}}
                <h6 class="font-weight-bold mb-3">Payment Timeline</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Amount Received Date</label>
                        <input type="date" name="cheque_rec_date" class="form-control"
                            value="{{ old('cheque_rec_date', optional($invoice->cheque_rec_date)->format('Y-m-d')) }}">
                    </div>

                    {{-- <div class="col-md-3 mb-3">
                        <label>Payment Timeline Days</label>
                        <input type="number" name="payment_time_line_days" class="form-control"
                            value="{{ old('payment_time_line_days', $invoice->payment_time_line_days) }}">
                    </div> --}}

                    <div class="col-md-3 mb-3">
                        <label>Payment Difference</label>
                        <input type="number" name="payment_difference_in_days" class="form-control"
                            value="{{ old('payment_difference_in_days', $invoice->payment_difference_in_days) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Difference</label>
                        <input type="number" step="0.01" name="diff" class="form-control invoice-diff" readonly
                            value="{{ old('diff', $invoice->diff) }}">
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="mt-4 text-right">
                    <button class="btn btn-success">
                        <i class="icon-checkmark mr-1"></i> Update Invoice
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

    @push('scripts')
        <script>
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
                    const monthlyRent = roundMoney(qty * rent);

                    if (monthlyRentInput) {
                        monthlyRentInput.value = monthlyRent.toFixed(2);
                    }

                    totalMonthlyRent += monthlyRent;
                });

                const sundayGazette = toNumber(document.querySelector('.invoice-sunday-gazette')?.value);
                const controlRoomCharges = toNumber(document.querySelector('.invoice-control-room')?.value);
                const agreedDeduction = toNumber(document.querySelector('.invoice-agreed-deduction')?.value);
                const paymentReceived = toNumber(document.querySelector('.invoice-payment-received')?.value);

                const totalClaim = roundMoney(totalMonthlyRent + sundayGazette + controlRoomCharges);
                const salesTax = roundMoney(totalClaim * 0.15);
                const inclusiveTotal = roundMoney(totalClaim + salesTax);
                const taxValue = roundMoney(totalClaim * 0.03);
                const withholdingTax = roundMoney(inclusiveTotal * 0.06);
                const netPayable = roundMoney(inclusiveTotal - withholdingTax - taxValue - agreedDeduction);
                const diff = roundMoney(netPayable - paymentReceived);

                document.querySelector('.invoice-total-claim').value = totalClaim.toFixed(2);
                document.querySelector('.invoice-sales-tax').value = salesTax.toFixed(2);
                document.querySelector('.invoice-inclusive-total').value = inclusiveTotal.toFixed(2);
                document.querySelector('.invoice-tax-value').value = taxValue.toFixed(2);
                document.querySelector('.invoice-withholding-tax').value = withholdingTax.toFixed(2);
                document.querySelector('.invoice-net-payable').value = netPayable.toFixed(2);
                document.querySelector('.invoice-amount-receivable').value = netPayable.toFixed(2);
                document.querySelector('.invoice-diff').value = diff.toFixed(2);
            }

            document.addEventListener('click', function(e) {

                /* =======================
                   ADD ROW
                ======================= */
                if (e.target.classList.contains('add-row')) {

                    let wrapper = document.getElementById('vehicle-wrapper');
                    let rows = wrapper.querySelectorAll('.vehicle-row');
                    let index = rows.length;

                    let newRow = `
        <div class="row vehicle-row mb-2">
            <div class="col-md-2">
                <input type="number"
                       step="any"
                       min="0"
                       name="vehicles[${index}][vehicle_qty]"
                       class="form-control invoice-vehicle-qty">
            </div>

            <div class="col-md-2">
                <input type="number"
                       step="any"
                       min="0"
                       name="vehicles[${index}][days]"
                       class="form-control">
            </div>

            <div class="col-md-3">
                <input type="number"
                       step="any"
                       name="vehicles[${index}][vehicle_rent]"
                       class="form-control invoice-vehicle-rent">
            </div>

            <div class="col-md-3">
                <input type="number"
                       step="any"
                       name="vehicles[${index}][monthly_rent]"
                       class="form-control invoice-monthly-rent"
                       readonly>
            </div>

            <div class="col-md-2 text-center">
                <button type="button" class="btn btn-danger remove-row">−</button>
            </div>
        </div>`;

                    wrapper.insertAdjacentHTML('beforeend', newRow);
                    recalculateInvoice();
                }

                /* =======================
                   REMOVE ROW
                ======================= */
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
    @endpush
@endsection
