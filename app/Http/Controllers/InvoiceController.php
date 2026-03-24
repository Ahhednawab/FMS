<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;
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
            'invoice_no' => 'required|string|max:255',
            'invoice_month' => 'required|date',
            'invoice_date' => 'required|date',
            'submission_date' => 'nullable|date',
            'po_no' => 'nullable|string|max:255',

            // 🚫 DO NOT validate these directly (they are arrays)
            'vehicles' => 'required|array|min:1',
            'vehicles.*.vehicle_qty' => 'required|integer|min:1',
            'vehicles.*.days' => 'required|integer|min:1',
            'vehicles.*.vehicle_rent' => 'required|numeric|min:0',
            'vehicles.*.monthly_rent' => 'required|numeric|min:0',

            'sunday_gazette' => 'nullable|numeric|min:0',
            'control_room_charges' => 'nullable|numeric|min:0',
            'total_claim' => 'nullable|numeric|min:0',

            'sales_tax' => 'nullable|numeric|min:0',
            'inclusive_sales_tax' => 'nullable|numeric|min:0',
            'tax_value' => 'nullable|numeric|min:0',
            'withholding_on_sales_tax' => 'nullable|numeric|min:0',

            'actual_payment' => 'nullable|numeric|min:0',
            'payment_received' => 'nullable|numeric|min:0',
            'agreed_deduction' => 'nullable|numeric|min:0',
            'cheque_value' => 'nullable|numeric|min:0',
            'cheque_no' => 'nullable|string|max:255',

            'diff' => 'nullable|numeric',
            'due_date' => 'nullable|date',
            'cheque_rec_date' => 'nullable|date',
            'payment_time_line_days' => 'nullable|integer|min:0',
            'payment_difference_in_days' => 'nullable|integer',
        ]);

        /* -----------------------------------------
       ✅ VEHICLE JSON PROCESSING (KEY FIX)
    ----------------------------------------- */

        $vehicles = $request->input('vehicles');

        $validated['vehicle_qty']  = array_column($vehicles, 'vehicle_qty');
        $validated['days']         = array_column($vehicles, 'days');
        $validated['vehicle_rent'] = array_column($vehicles, 'vehicle_rent');
        $validated['monthly_rent'] = array_column($vehicles, 'monthly_rent');

        // 🚫 Remove vehicles key (no DB column)
        unset($validated['vehicles']);

        /* -----------------------------------------
       ✅ YOUR EXISTING LOGIC (UNCHANGED)
    ----------------------------------------- */

        if (!empty($validated['submission_date'])) {
            $validated['due_date'] = Carbon::parse($validated['submission_date'])
                ->addDays(40)
                ->format('Y-m-d');
        }

        $validated['created_by'] = Auth::id();

        /* -----------------------------------------
       ✅ SAVE
    ----------------------------------------- */

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
        $validated = $request->validate([
            'dp_no' => 'nullable|string|max:255',
            'invoice_no' => 'required|string|max:255',
            'invoice_month' => 'required|date',
            'invoice_date' => 'required|date',
            'submission_date' => 'nullable|date',
            'po_no' => 'nullable|string|max:255',

            // ✅ vehicle JSON rows
            'vehicles' => 'required|array|min:1',
            'vehicles.*.vehicle_qty' => 'required|integer|min:1',
            'vehicles.*.days' => 'required|integer|min:1',
            'vehicles.*.vehicle_rent' => 'required|numeric|min:0',
            'vehicles.*.monthly_rent' => 'required|numeric|min:0',

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
            'payment_received' => 'nullable|numeric|min:0',
            'cheque_rec_date' => 'nullable|date',
            'payment_time_line_days' => 'nullable|integer|min:0',
            'payment_difference_in_days' => 'nullable|integer',
        ]);

        /* -----------------------------------------
       ✅ VEHICLE JSON REBUILD
    ----------------------------------------- */

        $vehicles = $validated['vehicles'];

        $validated['vehicle_qty']  = array_column($vehicles, 'vehicle_qty');
        $validated['days']         = array_column($vehicles, 'days');
        $validated['vehicle_rent'] = array_column($vehicles, 'vehicle_rent');
        $validated['monthly_rent'] = array_column($vehicles, 'monthly_rent');

        unset($validated['vehicles']);

        /* -----------------------------------------
       ✅ YOUR LOGIC (SAFE + FIXED)
    ----------------------------------------- */

        if (!empty($validated['submission_date'])) {
            $validated['due_date'] = Carbon::parse($validated['submission_date'])
                ->addDays(40)
                ->format('Y-m-d');
        }

        // Sunday Gazette calculation
        if ($request->filled('sunday_gazette')) {
            $validated['sunday_gazette'] = (int) $request->sunday_gazette * 1538;
        }

        /* -----------------------------------------
       ✅ UPDATE
    ----------------------------------------- */

        $invoice->update($validated);

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice updated successfully');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:invoices,id',
        ]);

        Invoice::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected invoices deleted successfully'
        ]);
    }

}
