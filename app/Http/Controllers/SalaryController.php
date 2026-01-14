<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\Driver;
use App\Models\EmployeeAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function index()
    {
        $months = Salary::selectRaw('DATE_FORMAT(salary_month, "%Y-%m") as month')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        return view('admin.salaries.index', compact('months'));
    }

    /**
     * Show salary create form (month-wise)
     */

    public function create(Request $request)
    {
        $perPage  = $request->get('per_page', 10);
        $driverId = $request->get('driver_id');
        $date     = $request->get('month');

        /* -----------------------------
       NORMALIZE MONTH
    ----------------------------- */
        $salaryMonth = $date ? Carbon::parse($date)->startOfMonth() : null;

        /* -----------------------------
       DRIVERS QUERY - Only load if date is selected
    ----------------------------- */
        $drivers = collect();
        $salaries = collect();
        $totalRecovered = collect();

        if ($salaryMonth) {
            $driverQuery = Driver::query();

            $driverQuery->where(function ($q) use ($salaryMonth) {
                $q->whereNull('last_date')
                    ->orWhereDate('last_date', '>=', $salaryMonth);
            });

            if ($driverId) {
                $driverQuery->where('id', $driverId);
            }

            // eager load advance for each driver
            $drivers = $driverQuery->with(['advance' => function ($q) use ($salaryMonth) {
                if ($salaryMonth) {
                    $q->whereDate('advance_date', '<=', $salaryMonth)
                        ->where('is_closed', false);
                }
            }])->paginate($perPage)->withQueryString();

            /* -----------------------------
           SALARIES (KEYED BY DRIVER)
        ----------------------------- */
            $salaries = Salary::where('salary_month', $salaryMonth->toDateString())
                ->get()
                ->keyBy('driver_id');

            /* -----------------------------
           TOTAL RECOVERED (amount - remaining_amount)
        ----------------------------- */
            foreach ($drivers as $driver) {
                $advance = $driver->advance;
                $totalRecovered[$driver->id] = $advance
                    ? ($advance->amount - $advance->remaining_amount)
                    : 0;
            }
        }

        /* -----------------------------
       AJAX RESPONSE
    ----------------------------- */
        if ($request->ajax()) {
            return view(
                'admin.salaries.partials.table',
                compact('drivers', 'salaries', 'totalRecovered')
            )->render();
        }

        /* -----------------------------
       DRIVER DROPDOWN
    ----------------------------- */
        $allDrivers = Driver::whereNull('last_date')->get();


        return view(
            'admin.salaries.create',
            compact('drivers', 'salaries', 'allDrivers', 'driverId', 'perPage', 'totalRecovered')
        );
    }




    /**
     * Store salary for multiple drivers
     */
    public function store(Request $request)
    {
        $request->validate([
            'salary_month' => 'required|date',
            'drivers' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->drivers as $driverId => $data) {

                $driver = Driver::find($driverId);
                if (!$driver) continue;

                $basic     = $driver->salary;
                $overtime  = $data['overtime'] ?? 0;
                $deduction = $data['deduction'] ?? 0;

                $advanceBalance = $driver->advance_balance;
                $advanceInput   = $data['advance'] ?? 0;
                $advanceDeduct  = min($advanceInput, $advanceBalance);

                $gross = $basic + $overtime - $deduction - $advanceDeduct;

                Salary::updateOrCreate(
                    [
                        'driver_id' => $driverId,
                        'salary_month' => Carbon::parse($request->salary_month)->format('Y-m-d'),
                    ],
                    [
                        'overtime_amount'   => $overtime,
                        'deduction_amount'  => $deduction,
                        'advance_deduction' => $advanceDeduct,
                        'gross_salary'      => max(0, $gross),
                        'remarks'           => $data['remarks'] ?? null,

                    ]
                );

                $this->settleAdvance($driverId, $advanceDeduct);
            }
        });

        return redirect()->route('salaries.index')
            ->with('success', 'Salaries saved successfully');
    }


    public function getByMonth($month)
    {
        return Salary::where('salary_month', $month)->get();
    }

    /**
     * Show salaries of a month
     */
    public function show(Request $request, $month)
    {
        $salaryMonth = \Carbon\Carbon::parse($month)->startOfMonth()->toDateString();
        $status = $request->get('status');
        $perPage = $request->get('per_page', 10);

        // Get drivers with salary for the month (or null)
        $driversQuery = \App\Models\Driver::with(['salaries' => function ($q) use ($salaryMonth) {
            $q->where('salary_month', $salaryMonth);
        }]);

        // If filtering by status
        if ($status === 'paid') {
            $driversQuery->whereHas('salaries', function ($q) use ($salaryMonth) {
                $q->where('salary_month', $salaryMonth)
                    ->where('status', 'paid');
            });
        } elseif ($status === 'pending') {
            // include drivers with no salary or salary pending
            $driversQuery->whereDoesntHave('salaries', function ($q) use ($salaryMonth) {
                $q->where('salary_month', $salaryMonth)
                    ->where('status', 'paid'); // exclude paid
            });
        }

        $drivers = $driversQuery->paginate($perPage)->withQueryString();

        return view('admin.salaries.show', compact('drivers', 'salaryMonth', 'status', 'perPage'));
    }


    /**
     * Edit not required (handled via create)
     */
    public function edit(Salary $salary)
    {
        abort(404);
    }

    public function update(Request $request, Salary $salary)
    {
        abort(404);
    }

    /**
     * Delete a salary record
     */
    public function destroy(Salary $salary)
    {
        $salary->delete();
        return back()->with('success', 'Salary record deleted');
    }

    /**
     * Advance settlement (FIFO)
     */
    /**
     * Settle the advance for a driver by deducting the given amount
     */
    private function settleAdvance(int $driverId, float $amount): void
    {
        if ($amount <= 0) return;

        // Get all open advances in order of oldest first
        $advances = EmployeeAdvance::where('driver_id', $driverId)
            ->where('is_closed', false)
            ->orderBy('advance_date')
            ->get();

        foreach ($advances as $advance) {
            if ($amount <= 0) break;

            $remaining = (float) $advance->remaining_amount;

            if ($remaining <= $amount) {
                // Deduct full remaining amount and close the advance
                $amount -= $remaining;
                $advance->update([
                    'remaining_amount' => 0,
                    'is_closed' => true,
                ]);
            } else {
                // Deduct partially
                $advance->update([
                    'remaining_amount' => $remaining - $amount,
                ]);
                $amount = 0;
            }
        }
    }

    /**
     * Save a single salary record
     */
    public function saveSingle(Request $request)
    {
        $request->validate([
            'salary_month' => 'required|date',
            'driver_id'    => 'required|exists:drivers,id',
        ]);

        DB::transaction(function () use ($request) {

            $driver = Driver::findOrFail($request->driver_id);

            // Normalize salary month
            $salaryMonth = Carbon::parse($request->salary_month)->startOfMonth()->toDateString();

            // Safe cast input values
            $basic            = (float) $driver->salary;
            $overtime         = (float) ($request->overtime ?? 0);
            $deduction        = (float) ($request->deduction ?? 0);
            $extra            = (float) ($request->extra ?? 0);
            $paidAbsent       = (float) ($request->paid_absent ?? 0);
            $advanceDeduction = (float) ($request->advance_deduction ?? 0); // Deduction input
            $remarks          = $request->remarks ?? '';
            $status           = $request->status ?? 'pending';

            // Advance balance protection
            $advanceBalance = $driver->advance_balance;
            if ($advanceDeduction > $advanceBalance) {
                $advanceDeduction = $advanceBalance;
            }

            // Calculate gross salary
            $grossSalary = $basic + $overtime + $extra - $deduction - $advanceDeduction;
            $grossSalary = max(0, $grossSalary);

            // Total recovered and remaining amount
            $totalRecovered  = 0;
            $remainingAmount = $grossSalary;

            if ($status === 'paid' && $advanceDeduction > 0) {
                // Deduct advance from employee advance table
                $this->settleAdvance($driver->id, $advanceDeduction);

                // Calculate total recovered and remaining amount after deduction
                $advance = EmployeeAdvance::where('driver_id', $driver->id)
                    ->where('is_closed', true)
                    ->latest('id')
                    ->first();

                if ($advance) {
                    $totalRecovered = $advance->amount - $advance->remaining_amount;
                    $remainingAmount = $grossSalary - $totalRecovered;
                }
            }

            // Save or update salary
            Salary::updateOrCreate(
                [
                    'driver_id'    => $driver->id,
                    'salary_month' => $salaryMonth,
                ],
                [
                    'overtime_amount'   => $overtime,
                    'deduction_amount'  => $deduction,
                    'extra'             => $extra,
                    'paid_absent'       => $paidAbsent,
                    'advance_deduction' => $advanceDeduction,
                    'total_recovered'   => $totalRecovered,
                    'remaining_amount'  => $remainingAmount,
                    'gross_salary'      => $grossSalary,
                    'remarks'           => $remarks,
                    'status'            => $status,
                ]
            );
        });

        return response()->json(['success' => true]);
    }
}
