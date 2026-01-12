<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAdvance;
use App\Models\Driver;
use Illuminate\Http\Request;

class EmployeeAdvanceController extends Controller
{
    /**
     * List all advances
     */
    // Show all issued advances
    public function index()
    {
        $advances = EmployeeAdvance::with('driver')
            ->orderBy('advance_date', 'desc')
            ->get();

        return view('admin.advances.index', compact('advances'));
    }

    // Show create advance form
    public function create()
    {
        $drivers = Driver::where('is_active', 1)->get();
        return view('admin.advances.create', compact('drivers'));
    }

    // Store new advance
    public function store(Request $request)
    {
        // First validate basic fields
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'advance_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'per_month_deduction' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string',
        ]);

        // Check if driver already has an open advance
        $existingAdvance = EmployeeAdvance::where('driver_id', $request->driver_id)
            ->where('is_closed', false)
            ->exists();

        if ($existingAdvance) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'driver_id' => 'This employee already has an active advance. Please close it before issuing a new one.'
                ]);
        }

        // Additional check: per_month_deduction <= amount
        if ($request->per_month_deduction > $request->amount) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'per_month_deduction' => 'Per month deduction cannot be greater than the total advance amount.'
                ]);
        }

        // Create advance
        EmployeeAdvance::create([
            'driver_id' => $request->driver_id,
            'advance_date' => $request->advance_date,
            'amount' => $request->amount,
            'remaining_amount' => $request->amount, // initially full amount
            'per_month_deduction' => $request->per_month_deduction,
            'remarks' => $request->remarks,
            'is_closed' => false,
        ]);

        return redirect()->route('advance.index')
            ->with('success', 'Advance issued successfully.');
    }

    /**
     * Show driver advance history
     */
    public function show(EmployeeAdvance $employeeAdvance)
    {
        return view('advances.show', compact('employeeAdvance'));
    }

    /**
     * Edit (optional)
     */
    public function edit(EmployeeAdvance $employeeAdvance)
    {
        return view('advances.edit', compact('employeeAdvance'));
    }

    /**
     * Update advance
     */
    public function update(Request $request, EmployeeAdvance $employeeAdvance)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $diff = $request->amount - $employeeAdvance->amount;

        $employeeAdvance->update([
            'amount' => $request->amount,
            'remaining_amount' => max(0, $employeeAdvance->remaining_amount + $diff),
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('employee-advances.index')
            ->with('success', 'Advance updated');
    }

    /**
     * Delete advance
     */
    public function destroy(EmployeeAdvance $employeeAdvance)
    {
        if ($employeeAdvance->remaining_amount < $employeeAdvance->amount) {
            return back()->withErrors('Advance already partially deducted');
        }

        $employeeAdvance->delete();

        return back()->with('success', 'Advance deleted');
    }
}
