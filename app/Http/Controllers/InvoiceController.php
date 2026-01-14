<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $invoice_month = $request->query('invoice_month', '');
        $per_page = (int)$request->query('per_page', 10);

        $query = Invoice::query();

        // Search across multiple fields
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('dp_no', 'like', "%{$search}%")
                    ->orWhere('po_no', 'like', "%{$search}%")
                    ->orWhere('cheque_no', 'like', "%{$search}%");
            });
        }

        // Filter by invoice month (format: MM/YYYY)
        if (!empty($invoice_month)) {
            $dateParts = explode('/', $invoice_month);
            if (count($dateParts) === 2) {
                $month = $dateParts[0];
                $year = $dateParts[1];
                $query->whereYear('invoice_month', '=', $year)
                    ->whereMonth('invoice_month', '=', $month);
            }
        }

        $invoices = $query->latest()->paginate($per_page);
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('admin.invoices.create');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'dp_no' => 'nullable|string|max:255',
            'invoice_no' => 'required|string|max:255', //'invoice_no' => 'required|string|max:255|unique:invoices,invoice_no', for unique invoice numbers
            'invoice_month' => 'required|date',
            'invoice_date' => 'required|date',
            'submission_date' => 'nullable|date', //'submission_date' => 'nullable|date|after_or_equal:invoice_date', for submission date after or equal to invoice date
            'po_no' => 'nullable|string|max:255',
            'vehicle_qty' => 'nullable|integer|min:0',
            'days' => 'nullable|integer|min:0',

            'vehicle_rent' => 'nullable|numeric|min:0',
            'monthly_rent' => 'nullable|numeric|min:0',
            'sunday_gazette' => 'nullable|numeric|min:0',
            'control_room_charges' => 'nullable|numeric|min:0',

            'total_claim' => 'nullable|numeric|min:0',
            'sales_tax' => 'nullable|numeric|min:0',
            'inclusive_sales_tax' => 'nullable|numeric|min:0',
            'tax_value' => 'nullable|numeric|min:0',
            'withholding_on_sales_tax' => 'nullable|numeric|min:0',

            'actual_payment' => 'nullable|numeric|min:0',
            'agreed_deduction' => 'nullable|numeric|min:0',

            'cheque_value' => 'nullable|numeric|min:0',
            'cheque_no' => 'nullable|string|max:255',

            'diff' => 'nullable|numeric',

            'due_date' => 'nullable|date',
            'cheque_rec_date' => 'nullable|date', //'cheque_rec_date' => 'nullable|date|after_or_equal:due_date', for cheque received date after or equal to due date
            'payment_time_line_days' => 'nullable|integer|min:0',
            'payment_difference_in_days' => 'nullable|integer',
        ]);

        $validated['created_by'] = Auth::id();

        Invoice::create($validated);

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice created successfully');
    }


    public function show(Invoice $invoice)
    {
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        return view('admin.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $invoice->update($request->all());

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice updated successfully');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully');
    }
}
