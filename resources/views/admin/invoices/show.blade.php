@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Invoice Details</h5>
            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                <i class="icon-arrow-left52 mr-1"></i> Back
            </a>
        </div>

        <div class="card-body">

            {{-- BASIC INFORMATION --}}
            <h6 class="font-weight-bold mb-3">Basic Information</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="25%">Serial No</th>
                    <td>{{ $invoice->serial_no }}</td>
                </tr>
                <tr>
                    <th>DP No</th>
                    <td>{{ $invoice->dp_no }}</td>
                </tr>
                <tr>
                    <th>Invoice No</th>
                    <td>{{ $invoice->invoice_no }}</td>
                </tr>
                <tr>
                    <th>PO No</th>
                    <td>{{ $invoice->po_no }}</td>
                </tr>
            </table>

            {{-- DATES --}}
            <h6 class="font-weight-bold mt-4 mb-3">Dates</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="25%">Invoice Month</th>
                    <td>{{ optional($invoice->invoice_month)->format('M Y') }}</td>
                </tr>
                <tr>
                    <th>Invoice Date</th>
                    <td>{{ optional($invoice->invoice_date)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <th>Submission Date</th>
                    <td>{{ optional($invoice->submission_date)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <th>Due Date</th>
                    <td>{{ optional($invoice->due_date)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <th>Cheque Received Date</th>
                    <td>{{ optional($invoice->cheque_rec_date)->format('d M Y') }}</td>
                </tr>
            </table>

            {{-- VEHICLE DETAILS --}}
            <h6 class="font-weight-bold mt-4 mb-3">Vehicle Details</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="25%">Vehicle Quantity</th>
                    <td>{{ $invoice->vehicle_qty }}</td>
                </tr>
                <tr>
                    <th>Days</th>
                    <td>{{ $invoice->days }}</td>
                </tr>
                <tr>
                    <th>Vehicle Rent</th>
                    <td>{{ number_format($invoice->vehicle_rent, 2) }}</td>
                </tr>
                <tr>
                    <th>Monthly Rent</th>
                    <td>{{ number_format($invoice->monthly_rent, 2) }}</td>
                </tr>
            </table>

            {{-- CHARGES --}}
            <h6 class="font-weight-bold mt-4 mb-3">Charges</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="25%">Sunday Gazette</th>
                    <td>{{ number_format($invoice->sunday_gazette, 2) }}</td>
                </tr>
                <tr>
                    <th>Control Room Charges</th>
                    <td>{{ number_format($invoice->control_room_charges, 2) }}</td>
                </tr>
                <tr>
                    <th>Total Claim</th>
                    <td class="font-weight-bold">{{ number_format($invoice->total_claim, 2) }}</td>
                </tr>
            </table>

            {{-- TAX DETAILS --}}
            <h6 class="font-weight-bold mt-4 mb-3">Tax Details</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="25%">Sales Tax</th>
                    <td>{{ number_format($invoice->sales_tax, 2) }}</td>
                </tr>
                <tr>
                    <th>Inclusive Sales Tax</th>
                    <td>{{ number_format($invoice->inclusive_sales_tax, 2) }}</td>
                </tr>
                <tr>
                    <th>Tax Value</th>
                    <td>{{ number_format($invoice->tax_value, 2) }}</td>
                </tr>
                <tr>
                    <th>Withholding on Sales Tax</th>
                    <td>{{ number_format($invoice->withholding_on_sales_tax, 2) }}</td>
                </tr>
            </table>

            {{-- PAYMENT DETAILS --}}
            <h6 class="font-weight-bold mt-4 mb-3">Payment Details</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="25%">Actual Payment</th>
                    <td>{{ number_format($invoice->actual_payment, 2) }}</td>
                </tr>
                <tr>
                    <th>Agreed Deduction</th>
                    <td>{{ number_format($invoice->agreed_deduction, 2) }}</td>
                </tr>
                <tr>
                    <th>Cheque Value</th>
                    <td class="font-weight-bold">{{ number_format($invoice->cheque_value, 2) }}</td>
                </tr>
                <tr>
                    <th>Cheque No</th>
                    <td>{{ $invoice->cheque_no }}</td>
                </tr>
                <tr>
                    <th>Difference</th>
                    <td>{{ number_format($invoice->diff, 2) }}</td>
                </tr>
            </table>

            {{-- PAYMENT TIMELINE --}}
            <h6 class="font-weight-bold mt-4 mb-3">Payment Timeline</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="25%">Payment Timeline Days</th>
                    <td>{{ $invoice->payment_time_line_days }}</td>
                </tr>
                <tr>
                    <th>Payment Difference (Days)</th>
                    <td>{{ $invoice->payment_difference_in_days }}</td>
                </tr>
            </table>

            {{-- AUDIT --}}
            <h6 class="font-weight-bold mt-4 mb-3">Audit</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="25%">Created By</th>
                    <td>{{ $invoice->creator->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>{{ $invoice->created_at->format('d M Y h:i A') }}</td>
                </tr>
            </table>

        </div>
    </div>
@endsection
