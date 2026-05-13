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
                        <input type="text" name="dp_no" class="form-control" value="{{ old('dp_no') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Invoice No</label>
                        <input type="text" name="invoice_no" class="form-control" value="{{ old('invoice_no') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>PO No</label>
                        <input type="text" name="po_no" class="form-control" value="{{ old('po_no') }}">
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
                            <label class="text-danger">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- DATES --}}
                <h6 class="font-weight-bold mb-3">Dates</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Invoice Month</label>
                        <input type="date" name="invoice_month" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Invoice Date</label>
                        <input type="date" name="invoice_date" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Submission Date</label>
                        <input type="date" name="submission_date" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>

                {{-- VEHICLE DETAILS --}}
                {{-- VEHICLE DETAILS --}}
                <h6 class="font-weight-bold mb-3">Vehicle Details</h6>

                <div id="vehicle-wrapper">

                    <div class="row vehicle-row mb-2" data-index="0">
                        <div class="col-md-2">
                            <input type="number" name="vehicles[0][vehicle_qty]" class="form-control invoice-vehicle-qty" placeholder="Qty">
                        </div>

                        <div class="col-md-2">
                            <input type="number" name="vehicles[0][days]" class="form-control" placeholder="Days">
                        </div>

                        <div class="col-md-3">
                            <input type="number" step="0.01" name="vehicles[0][vehicle_rent]" class="form-control invoice-vehicle-rent"
                                placeholder="Vehicle Rent">
                        </div>

                        <div class="col-md-3">
                            <input type="number" step="0.01" name="vehicles[0][monthly_rent]" class="form-control invoice-monthly-rent"
                                readonly
                                placeholder="Monthly Rent">
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-success add-row">+</button>
                        </div>
                    </div>

                </div>


                {{-- CHARGES --}}
                <h6 class="font-weight-bold mb-3">Charges</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Sunday Gazette</label>
                        <input type="number" step="0.01" name="sunday_gazette" class="form-control invoice-sunday-gazette">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Control Room Charges</label>
                        <input type="number" step="0.01" name="control_room_charges" class="form-control invoice-control-room">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Total Claim</label>
                        <input type="number" step="0.01" name="total_claim" class="form-control invoice-total-claim" readonly>
                    </div>
                </div>

                {{-- TAX DETAILS --}}
                <h6 class="font-weight-bold mb-3">Tax Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Sales Tax (15%)</label>
                        <input type="number" step="0.01" name="sales_tax" class="form-control invoice-sales-tax" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Inclusive Total</label>
                        <input type="number" step="0.01" name="inclusive_sales_tax" class="form-control invoice-inclusive-total" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Tax Value</label>
                        <input type="number" step="0.01" name="tax_value" class="form-control invoice-tax-value" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Withholding Tax</label>
                        <input type="number" step="0.01" name="withholding_on_sales_tax" class="form-control invoice-withholding-tax" readonly>
                    </div>
                </div>

                {{-- PAYMENT DETAILS --}}
                <h6 class="font-weight-bold mb-3">Payment Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Net Payable Amount</label>
                        <input type="number" step="0.01" name="actual_payment" class="form-control invoice-net-payable" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Agreed Deduction</label>
                        <input type="number" step="0.01" name="agreed_deduction" class="form-control invoice-agreed-deduction">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Amount Receivable</label>
                        <input type="number" step="0.01" name="cheque_value" class="form-control invoice-amount-receivable" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Payment Received</label>
                        <input type="number" step="0.01" name="payment_received" class="form-control invoice-payment-received">
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
                        <input type="date" name="cheque_rec_date" class="form-control">
                    </div>

                    {{-- <div class="col-md-3 mb-3">
                        <label>Payment Timeline Days</label>
                        <input type="number" name="payment_time_line_days" class="form-control">
                    </div> --}}

                    <div class="col-md-3 mb-3">
                        <label>Payment Difference</label>
                        <input type="number" name="payment_difference_in_days" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Difference</label>
                        <input type="number" step="0.01" name="diff" class="form-control invoice-diff" readonly>
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
        let rowIndex = 1;

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

            // ADD ROW
            if (e.target.classList.contains('add-row')) {
                const wrapper = document.getElementById('vehicle-wrapper');
                const newRow = document.querySelector('.vehicle-row').cloneNode(true);

                newRow.setAttribute('data-index', rowIndex);

                newRow.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    input.name = input.name.replace(/\[\d+\]/, `[${rowIndex}]`);
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
