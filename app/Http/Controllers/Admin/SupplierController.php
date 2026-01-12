<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {

        $suppliers = Supplier::latest()->paginate(10);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create(Request $request)
    {

        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'contact' => 'required',
            'address' => 'nullable'
        ]);

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Request $request, Supplier $supplier)
    {

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Request $request, Supplier $supplier)
    {

        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {

        $request->validate([
            'name' => 'required',
            'contact' => 'required',
            'address' => 'nullable'
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
