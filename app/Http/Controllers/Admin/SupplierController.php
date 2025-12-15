<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $role_slug = $request->get('roleSlug');

        $suppliers = Supplier::latest()->paginate(10);
        return view('admin.suppliers.index', compact('suppliers', 'role_slug'));
    }

    public function create(Request $request)
    {
        $role_slug = $request->get('roleSlug');

        return view('admin.suppliers.create', compact('role_slug'));
    }

    public function store(Request $request)
    {
        $role_slug = $request->get('roleSlug');

        $request->validate([
            'name' => 'required',
            'contact' => 'required',
            'address' => 'nullable'
        ]);

        Supplier::create($request->all());

        return redirect()->route($role_slug . '.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Request $request, Supplier $supplier)
    {
        $role_slug = $request->get('roleSlug');

        return view('admin.suppliers.show', compact('supplier', 'role_slug'));
    }

    public function edit(Request $request, Supplier $supplier)
    {
        $role_slug = $request->get('roleSlug');

        return view('admin.suppliers.edit', compact('supplier', 'role_slug'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $role_slug = $request->get('roleSlug');

        $request->validate([
            'name' => 'required',
            'contact' => 'required',
            'address' => 'nullable'
        ]);

        $supplier->update($request->all());

        return redirect()->route($role_slug . '.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        $role_slug = $request->get('roleSlug');

        $supplier->delete();

        return redirect()->route($role_slug . '.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
