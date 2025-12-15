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
    public function index()
    {
        $purchases = Purchase::with('supplier')->paginate(10);
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = ProductList::all();
        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {

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

        return redirect()->route('admin.purchases.index')->with('success', 'Purchase added successfully!');
        // } catch (\Throwable $th) {
        //     return redirect()->route('admin.purchases.create')->with('error', 'Something went wrong!');
        // }
    }
}
