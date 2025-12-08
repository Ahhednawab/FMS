<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier')->get();
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        return view('admin.purchases.create', compact('suppliers'));
    }

    public function store(Request $request)
    {

        // try {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|decimal:0,2',
            'purchase_date' => 'required|date',
        ]);

        Purchase::create($validated);  // Store the new purchase

        return redirect()->route('admin.purchases.index')->with('success', 'Purchase added successfully!');
        // } catch (\Throwable $th) {
        //     return redirect()->route('admin.purchases.create')->with('error', 'Something went wrong!');
        // }
    }
}
