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
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Vehicle Qty</label>
                        <input type="number" name="vehicle_qty" class="form-control"
                            value="{{ old('vehicle_qty', $invoice->vehicle_qty) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Days</label>
                        <input type="number" name="days" class="form-control"
                            value="{{ old('days', $invoice->days) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Vehicle Rent</label>
                        <input type="number" step="0.01" name="vehicle_rent" class="form-control"
                            value="{{ old('vehicle_rent', $invoice->vehicle_rent) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Monthly Rent</label>
                        <input type="number" step="0.01" name="monthly_rent" class="form-control"
                            value="{{ old('monthly_rent', $invoice->monthly_rent) }}">
                    </div>
                </div>

                {{-- CHARGES --}}
                <h6 class="font-weight-bold mb-3">Charges</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Sunday Gazette</label>
                        <input type="number" step="0.01" name="sunday_gazette" class="form-control"
                            value="{{ old('sunday_gazette', $invoice->sunday_gazette) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Control Room Charges</label>
                        <input type="number" step="0.01" name="control_room_charges" class="form-control"
                            value="{{ old('control_room_charges', $invoice->control_room_charges) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Total Claim</label>
                        <input type="number" step="0.01" name="total_claim" class="form-control"
                            value="{{ old('total_claim', $invoice->total_claim) }}">
                    </div>
                </div>

                {{-- TAX DETAILS --}}
                <h6 class="font-weight-bold mb-3">Tax Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Sales Tax</label>
                        <input type="number" step="0.01" name="sales_tax" class="form-control"
                            value="{{ old('sales_tax', $invoice->sales_tax) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Inclusive Sales Tax</label>
                        <input type="number" step="0.01" name="inclusive_sales_tax" class="form-control"
                            value="{{ old('inclusive_sales_tax', $invoice->inclusive_sales_tax) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Tax Value</label>
                        <input type="number" step="0.01" name="tax_value" class="form-control"
                            value="{{ old('tax_value', $invoice->tax_value) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Withholding on Sales Tax</label>
                        <input type="number" step="0.01" name="withholding_on_sales_tax" class="form-control"
                            value="{{ old('withholding_on_sales_tax', $invoice->withholding_on_sales_tax) }}">
                    </div>
                </div>

                {{-- PAYMENT DETAILS --}}
                <h6 class="font-weight-bold mb-3">Payment Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Actual Payment</label>
                        <input type="number" step="0.01" name="actual_payment" class="form-control"
                            value="{{ old('actual_payment', $invoice->actual_payment) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Agreed Deduction</label>
                        <input type="number" step="0.01" name="agreed_deduction" class="form-control"
                            value="{{ old('agreed_deduction', $invoice->agreed_deduction) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Cheque Value</label>
                        <input type="number" step="0.01" name="cheque_value" class="form-control"
                            value="{{ old('cheque_value', $invoice->cheque_value) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Cheque No</label>
                        <input type="text" name="cheque_no" class="form-control"
                            value="{{ old('cheque_no', $invoice->cheque_no) }}">
                    </div>
                </div>

                {{-- PAYMENT TIMELINE --}}
                <h6 class="font-weight-bold mb-3">Payment Timeline</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Cheque Received Date</label>
                        <input type="date" name="cheque_rec_date" class="form-control"
                            value="{{ old('cheque_rec_date', optional($invoice->cheque_rec_date)->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Payment Timeline Days</label>
                        <input type="number" name="payment_time_line_days" class="form-control"
                            value="{{ old('payment_time_line_days', $invoice->payment_time_line_days) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Payment Difference (Days)</label>
                        <input type="number" name="payment_difference_in_days" class="form-control"
                            value="{{ old('payment_difference_in_days', $invoice->payment_difference_in_days) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Difference</label>
                        <input type="number" step="0.01" name="diff" class="form-control"
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
@endsection
