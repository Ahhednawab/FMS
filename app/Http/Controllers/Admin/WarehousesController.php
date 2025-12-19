<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryIssue;
use App\Models\InventoryRequest;
use App\Models\Warehouses;
use Illuminate\Http\Request;

class WarehousesController extends Controller
{
    public function createWarehouse(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:master,sub',
        ]);

        Warehouses::create($validated);

        return redirect()->route('admin.warehouses.index');
    }

    public function requestInventory(Request $request)
    {
        $validated = $request->validate([
            'sub_warehouse_id' => 'required|exists:warehouses,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        InventoryRequest::create($validated);

        return redirect()->route('admin.warehouses.index');
    }

    public function issueInventory(Request $request)
    {
        $validated = $request->validate([
            'master_warehouse_id' => 'required|exists:warehouses,id',
            'sub_warehouse_id' => 'required|exists:warehouses,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        InventoryIssue::create($validated);

        return redirect()->route('admin.warehouses.index');
    }

    public function assignWarehouse() {
        
    }
}
