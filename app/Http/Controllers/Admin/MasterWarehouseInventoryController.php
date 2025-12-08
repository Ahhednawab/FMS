<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterWarehouseInventory;
use Illuminate\Http\Request;

class MasterWarehouseInventoryController extends Controller
{
    public function index()
    {
        $inventory = MasterWarehouseInventory::all();  // Get all inventory items
        return view('admin.master_warehouse_inventory.index', compact('inventory'));
    }

    public function create()
    {
        return view('admin.master_warehouse_inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
        ]);

        MasterWarehouseInventory::create($validated);  // Save new inventory item

        return redirect()->route('master_warehouse_inventory.index')->with('success', 'Inventory item added successfully!');
    }
}
