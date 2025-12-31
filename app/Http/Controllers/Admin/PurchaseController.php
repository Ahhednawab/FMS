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

        // Validate the input data
        $validated = $request->validate([
            'product_id' => 'required|exists:products_list,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|decimal:0,2',
            'expiry_date' => 'nullable|date',
            'purchase_date' => 'required|date',
        ]);

        // Store the new purchase
        Purchase::create($validated);

        // Generate the batch number
        $batchNumber = MasterWarehouseInventory::GetBatchNumber();

        // Prepare the data to store in MasterWarehouseInventory
        $inventoryData = array_merge($validated, [
            'batch_number' => $batchNumber,
        ]);

        // Create the inventory record
        MasterWarehouseInventory::create($inventoryData);

        return redirect()->route($role_slug . '.purchases.index')->with('success', 'Purchase added successfully!');
    }
}
