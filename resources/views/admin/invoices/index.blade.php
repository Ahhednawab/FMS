@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Invoices</h5>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="icon-plus2 mr-1"></i> Add Invoice
            </a>
        </div>

        <!-- Filters -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('invoices.index') }}" class="d-flex align-items-center gap-3 flex-wrap">
                <div class="mx-1">
                    <label class="mb-0 font-weight-bold ">Search:</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Invoice No, DP No, PO No, Cheque No..." value="{{ request('search', '') }}"
                        style="width: 280px;">
                </div>
                <div class="mx-1">
                    <label class="mb-0 font-weight-bold">Invoice Month:</label>
                    <input type="text" id="invoice_month_picker" name="invoice_month"
                        class="form-control form-control-sm" placeholder="MM/YYYY"
                        value="{{ request('invoice_month', '') }}" style="width: 150px;" readonly>
                </div>
                <div class="mx-1">
                    <label class="mb-0 font-weight-bold">Show:</label>
                    <select name="per_page" class="form-control form-control-sm" style="width: 100px;">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary mx-1 mt-3">Search</button>
            </form>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Serial No</th>
                        <th>Invoice No</th>
                        <th>DP No</th>
                        <th>Invoice Month</th>
                        <th>Invoice Date</th>
                        <th>Total Claim</th>
                        <th>Cheque Value</th>
                        <th width="160">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoices->firstItem() + $loop->index }}</td>
                            <td>{{ $invoice->serial_no }}</td>
                            <td>{{ $invoice->invoice_no }}</td>
                            <td>{{ $invoice->dp_no ?? '—' }}</td>
                            <td>
                                {{ optional($invoice->invoice_month)->format('M Y') ?? '—' }}
                            </td>
                            <td>
                                {{ optional($invoice->invoice_date)->format('d-M-Y') ?? '—' }}
                            </td>
                            <td class="text-right">
                                {{ number_format($invoice->total_claim ?? 0, 2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($invoice->cheque_value ?? 0, 2) }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-info btn-sm">
                                    <i class="icon-eye"></i>
                                </a>

                                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning btn-sm">
                                    <i class="icon-pencil7"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        {{-- FALLBACK WHEN NO RECORDS --}}
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="icon-file-empty icon-2x d-block mb-2"></i>
                                No invoices found.<br>
                                <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary mt-2">
                                    Add your first invoice
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- PAGINATION --}}
            @if ($invoices->hasPages())
                <div class="mt-3">
                    {{ $invoices->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">

    <script>
        $(document).ready(function() {
            $('#invoice_month_picker').datepicker({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                dateFormat: 'mm/yy',
                onClose: function(dateText, inst) {
                    var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                    var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                    $(this).val($.datepicker.formatDate('mm/yy', new Date(year, month, 1)));
                    $(this).closest('form').submit();
                },
                beforeShow: function(input, inst) {
                    if ((selDate = $(this).val()).length > 0) {
                        var dateA = selDate.split('/');
                        inst.selectedMonth = parseInt(dateA[0]) - 1;
                        inst.selectedYear = parseInt(dateA[1]);
                    }
                }
            });

            // Hide calendar button if jQuery UI is loaded
            $('#ui-datepicker-div').css('display', 'none');
        });
    </script>

    <style>
        .ui-datepicker {
            width: auto !important;
        }

        .ui-datepicker table {
            display: none;
        }

        .ui-datepicker-header {
            margin-bottom: 20px;
        }
    </style>
@endsection
