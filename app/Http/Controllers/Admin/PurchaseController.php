<?php

namespace App\Http\Controllers\Admin;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\ProductList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterWarehouseInventory;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $role_slug = $request->get('roleSlug');

        $purchases = Purchase::with('supplier')->get();
        return view('admin.purchases.index', compact('purchases', 'role_slug'));
    }

    public function create(Request $request)
    {
        $role_slug = $request->get('roleSlug');

        $suppliers = Supplier::all();
        $products = ProductList::all();
        return view('admin.purchases.create', compact('suppliers', 'products', 'role_slug'));
    }

    public function store(Request $request)
    {
        $role_slug = $request->get('roleSlug');

        // try {
        $validated = $request->validate([
            'product_id' => 'required|exists:products_list,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|decimal:0,2',
            'expiry_date' => 'nullable|date',
            'purchase_date' => 'required|date',
        ]);


        Purchase::create($validated);  // Store the new purchase

        MasterWarehouseInventory::create($validated);

        return redirect()->route($role_slug . '.purchases.index')->with('success', 'Purchase added successfully!');
        // } catch (\Throwable $th) {
        //     return redirect()->route('admin.purchases.create')->with('error', 'Something went wrong!');
        // }
    }
}
