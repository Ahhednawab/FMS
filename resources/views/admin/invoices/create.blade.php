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
                        <input type="text" name="dp_no" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Invoice No</label>
                        <input type="text" name="invoice_no" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>PO No</label>
                        <input type="text" name="po_no" class="form-control">
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
                <h6 class="font-weight-bold mb-3">Vehicle Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Vehicle Qty</label>
                        <input type="number" name="vehicle_qty" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Days</label>
                        <input type="number" name="days" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Vehicle Rent</label>
                        <input type="number" step="0.01" name="vehicle_rent" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Monthly Rent</label>
                        <input type="number" step="0.01" name="monthly_rent" class="form-control">
                    </div>
                </div>

                {{-- CHARGES --}}
                <h6 class="font-weight-bold mb-3">Charges</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Sunday Gazette</label>
                        <input type="number" step="0.01" name="sunday_gazette" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Control Room Charges</label>
                        <input type="number" step="0.01" name="control_room_charges" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Total Claim</label>
                        <input type="number" step="0.01" name="total_claim" class="form-control">
                    </div>
                </div>

                {{-- TAX DETAILS --}}
                <h6 class="font-weight-bold mb-3">Tax Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Sales Tax</label>
                        <input type="number" step="0.01" name="sales_tax" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Inclusive Sales Tax</label>
                        <input type="number" step="0.01" name="inclusive_sales_tax" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Tax Value</label>
                        <input type="number" step="0.01" name="tax_value" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Withholding on Sales Tax</label>
                        <input type="number" step="0.01" name="withholding_on_sales_tax" class="form-control">
                    </div>
                </div>

                {{-- PAYMENT DETAILS --}}
                <h6 class="font-weight-bold mb-3">Payment Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Actual Payment</label>
                        <input type="number" step="0.01" name="actual_payment" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Agreed Deduction</label>
                        <input type="number" step="0.01" name="agreed_deduction" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Cheque Value</label>
                        <input type="number" step="0.01" name="cheque_value" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Cheque No</label>
                        <input type="text" name="cheque_no" class="form-control">
                    </div>
                </div>

                {{-- PAYMENT TIMELINE --}}
                <h6 class="font-weight-bold mb-3">Payment Timeline</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Cheque Received Date</label>
                        <input type="date" name="cheque_rec_date" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Payment Timeline Days</label>
                        <input type="number" name="payment_time_line_days" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Payment Difference (Days)</label>
                        <input type="number" name="payment_difference_in_days" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Difference</label>
                        <input type="number" step="0.01" name="diff" class="form-control">
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
@endsection
