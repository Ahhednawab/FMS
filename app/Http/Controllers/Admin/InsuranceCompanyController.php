<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsuranceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class InsuranceCompanyController extends Controller
{

    public function __construct()
    {

        if (!auth()->user()->hasPermission('users')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    public function index()
    {
        $companies = InsuranceCompany::where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get();
        return view('admin.insurance-companies.index', compact('companies'));
    }

    public function create()
    {
        $serial_no = InsuranceCompany::getSerialNumber();
        return view('admin.insurance-companies.create', compact('serial_no'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Name is required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        InsuranceCompany::create([
            'serial_no' => $request->serial_no,
            'name' => $request->name,
            'is_active' => true
        ]);

        return redirect()->route('admin.insurance-companies.index')
            ->with('success', 'Insurance Company created successfully.');
    }

    public function edit(InsuranceCompany $insurance_company)
    {
        return view('admin.insurance-companies.edit', compact('insurance_company'));
    }

    public function update(Request $request, InsuranceCompany $insurance_company)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Name is required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $insurance_company->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.insurance-companies.index')
            ->with('success', 'Insurance Company updated successfully.');
    }

    public function show(InsuranceCompany $insurance_company)
    {
        return view('admin.insurance-companies.show', compact('insurance_company'));
    }

    public function destroy(InsuranceCompany $insurance_company)
    {
        $insurance_company->update(['is_active' => false]);
        return redirect()->route('admin.insurance-companies.index')
            ->with('success', 'Insurance Company deleted successfully.');
    }
}
