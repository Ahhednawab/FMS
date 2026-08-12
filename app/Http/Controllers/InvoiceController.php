<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Invoice tax rates.
     *
     * These are the single source of truth: the server always recalculates the
     * derived totals from them on save, so the Create and Edit forms must use
     * the same values (they are shared with the views via invoiceRates()).
     */
    public const SALES_TAX_RATE = 0.15;

    /** Tax value: 7% of the sales-tax-inclusive total. */
    public const TAX_VALUE_RATE = 0.07;

    /** Withholding on sales tax: 20% of the sales tax amount. */
    public const WITHHOLDING_RATE = 0.20;

    /**
     * Rates handed to the Blade forms so their live preview matches exactly
     * what the server will store.
     *
     * @return array<string, float>
     */
    private function invoiceRates(): array
    {
        return [
            'salesTax'    => self::SALES_TAX_RATE,
            'taxValue'    => self::TAX_VALUE_RATE,
            'withholding' => self::WITHHOLDING_RATE,
        ];
    }

    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $invoiceMonth = $request->query('invoice_month', '');
        $perPage = (int) $request->query('per_page', 10);

        $query = Invoice::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('dp_no', 'like', "%{$search}%")
                    ->orWhere('po_no', 'like', "%{$search}%")
                    ->orWhere('cheque_no', 'like', "%{$search}%");
            });
        }

        if ($invoiceMonth !== '') {
            $dateParts = explode('/', $invoiceMonth);
            if (count($dateParts) === 2) {
                $query->whereYear('invoice_month', (int) $dateParts[1])
                    ->whereMonth('invoice_month', (int) $dateParts[0]);
            }
        }

        $invoices = $query->latest()->paginate($perPage);

        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('admin.invoices.create', [
            'clearanceIndications' => $this->clearanceIndications(),
            'invoiceRates'         => $this->invoiceRates(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);
        $validated = $this->prepareInvoicePayload($validated);
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
        return view('admin.invoices.edit', [
            'invoice' => $invoice,
            'clearanceIndications' => $this->clearanceIndications(),
            'invoiceRates'         => $this->invoiceRates(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $this->validateInvoice($request);
        $validated = $this->prepareInvoicePayload($validated);

        $invoice->update($validated);

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice updated successfully');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()
            ->route('invoices.index')
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
            'message' => 'Selected invoices deleted successfully',
        ]);
    }

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'dp_no' => 'nullable|string|max:255',
            'invoice_no' => 'required|string|max:255',
            'invoice_month' => 'required|date',
            'invoice_date' => 'required|date',
            'submission_date' => 'nullable|date',
            'po_no' => 'nullable|string|max:255',
            'vehicles' => 'required|array|min:1',
            'vehicles.*.vehicle_qty' => 'required|numeric|min:0',
            'vehicles.*.days' => 'required|numeric|min:0',
            'vehicles.*.vehicle_rent' => 'required|numeric|min:0',
            'vehicles.*.monthly_rent' => 'nullable|numeric|min:0',
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
            'clearance_indication' => 'nullable|in:paid,unpaid',
            'diff' => 'nullable|numeric',
            'due_date' => 'nullable|date',
            'cheque_rec_date' => 'nullable|date',
            'payment_time_line_days' => 'nullable|integer|min:0',
            'payment_difference_in_days' => 'nullable|integer',
        ]);
    }

    private function prepareInvoicePayload(array $validated): array
    {
        $vehicles = $validated['vehicles'];

        $validated['vehicle_qty'] = array_column($vehicles, 'vehicle_qty');
        $validated['days'] = array_column($vehicles, 'days');
        $validated['vehicle_rent'] = array_column($vehicles, 'vehicle_rent');
        $validated['monthly_rent'] = array_column($vehicles, 'monthly_rent');

        unset($validated['vehicles']);

        if (empty($validated['due_date']) && ! empty($validated['submission_date'])) {
            $validated['due_date'] = Carbon::parse($validated['submission_date'])
                ->addDays(40)
                ->format('Y-m-d');
        }

        return $this->recalculateInvoiceValues($validated);
    }

    private function recalculateInvoiceValues(array $validated): array
    {
        $vehicleQty = array_map(fn ($value) => $this->normalizeDecimalValue($value), (array) ($validated['vehicle_qty'] ?? []));
        $days = array_map(fn ($value) => $this->normalizeDecimalValue($value), (array) ($validated['days'] ?? []));
        $vehicleRent = array_map(fn ($value) => $this->normalizeDecimalValue($value), (array) ($validated['vehicle_rent'] ?? []));
        $vehicleQtyNumbers = array_map(fn ($value) => max(0, $this->toFloat($value)), $vehicleQty);
        $vehicleRentNumbers = array_map(fn ($value) => $this->toFloat($value), $vehicleRent);

        $monthlyRent = [];
        foreach ($vehicleQtyNumbers as $index => $qty) {
            $monthlyRent[] = $qty * ($vehicleRentNumbers[$index] ?? 0);
        }

        $sundayGazette = $this->normalizeDecimalValue($validated['sunday_gazette'] ?? 0);
        $controlRoomCharges = $this->normalizeDecimalValue($validated['control_room_charges'] ?? 0);
        $agreedDeduction = $this->normalizeDecimalValue($validated['agreed_deduction'] ?? 0);
        $paymentReceived = $this->normalizeDecimalValue($validated['payment_received'] ?? 0);
        $sundayGazetteNumber = $this->toFloat($sundayGazette);
        $controlRoomChargesNumber = $this->toFloat($controlRoomCharges);
        $agreedDeductionNumber = $this->toFloat($agreedDeduction);
        $paymentReceivedNumber = $this->toFloat($paymentReceived);

        $totalMonthlyRent = array_sum($monthlyRent);
        $totalClaim = $totalMonthlyRent + $sundayGazetteNumber + $controlRoomChargesNumber;
        $salesTax = $totalClaim * self::SALES_TAX_RATE;
        $inclusiveTotal = $totalClaim + $salesTax;
        $taxValue = $inclusiveTotal * self::TAX_VALUE_RATE;
        $withholdingTax = $salesTax * self::WITHHOLDING_RATE;
        $netPayable = $inclusiveTotal - $withholdingTax - $taxValue - $agreedDeductionNumber;

        $validated['vehicle_qty'] = $vehicleQty;
        $validated['days'] = $days;
        $validated['vehicle_rent'] = $vehicleRent;
        $validated['monthly_rent'] = $monthlyRent;
        $validated['sunday_gazette'] = $sundayGazette;
        $validated['control_room_charges'] = $controlRoomCharges;
        $validated['total_claim'] = $totalClaim;
        $validated['sales_tax'] = $salesTax;
        $validated['inclusive_sales_tax'] = $inclusiveTotal;
        $validated['tax_value'] = $taxValue;
        $validated['withholding_on_sales_tax'] = $withholdingTax;
        $validated['actual_payment'] = $netPayable;
        $validated['agreed_deduction'] = $agreedDeduction;
        $validated['cheque_value'] = $netPayable;
        $validated['payment_received'] = $paymentReceived;
        $validated['diff'] = $netPayable - $paymentReceivedNumber;

        return $validated;
    }

    private function clearanceIndications(): array
    {
        return [
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
        ];
    }

    private function normalizeDecimalValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return (string) $value;
    }

    private function toFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }
}
