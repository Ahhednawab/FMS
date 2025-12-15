<?php

namespace App\Http\Controllers\Admin;

use App\Models\Warehouses;
use App\Models\ProductList;
use Illuminate\Http\Request;
use App\Models\WarehouseAssignment;
use App\Http\Controllers\Controller;
use App\Models\MasterWarehouseInventory;

class MasterWarehouseInventoryController extends Controller
{

    public function index(Request $request)
    {
        $role_slug = $request->get('roleSlug');

        $inventory = MasterWarehouseInventory::with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        $warehouses = Warehouses::where('is_active', true)->where('type', 'sub')
            ->orderBy('name')
            ->get();

        return view('admin.master_warehouse_inventory.index', compact('inventory', 'warehouses', 'role_slug'));
    }

    public function create(Request $request)
    {
        $role_slug = $request->get('roleSlug');

        $products = ProductList::all();

        return view('admin.master_warehouse_inventory.create', compact('products', 'role_slug'));
    }

    public function store(Request $request)
    {
        $role_slug = $request->get('roleSlug');

        $validated = $request->validate([
            'product_id' => 'required|exists:products_list,id',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|decimal:0,2',
        ]);

        $validated['supplier_id'] = 1;

        MasterWarehouseInventory::create($validated);  // Save new inventory item

        return redirect()->route($role_slug . '.master_warehouse_inventory.index')->with('success', 'Inventory item added successfully!');
    }

    public function assignStock(Request $request)
    {
        $valid = $request->validate([
            'master_inventory_id' => 'required|exists:master_warehouse_inventory,id',
            'warehouse_id'         => 'required|exists:warehouses,id',
            'quantity'            => 'required|integer|min:1'
        ]);

        $master = MasterWarehouseInventory::with('product')->findOrFail($request->master_inventory_id);
        if ((int)$request->quantity > $master->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock! Only ' . $master->quantity . ' available.'
            ], 400);
        }
        // Create assignment record
        \App\Models\WarehouseAssignment::create([
            'master_inventory_id' => $master->id,
            'warehouse_id'        => $request->warehouse_id,
            'quantity'            => $request->quantity,
            'batch_number'        => $master->batch_number,
            'expiry_date'         => $master->expiry_date,
            'price'               => $master->price,
            'assigned_by'         => auth()->id(),
        ]);

        // Reduce stock in master inventory
        $master->decrement('quantity', $request->quantity);

        return response()->json([
            'success' => true,
            'message' => "Assigned {$request->quantity} × {$master->product->name} to warehouse!",
            'new_quantity' => $master->quantity
        ]);
    }
    public function assigned(Request $request)
    {
        $assignments = WarehouseAssignment::with(['masterInventory.product', 'warehouse'])
            ->orderBy('assigned_at', 'desc')
            ->paginate(25);

        return view('admin.master_warehouse_inventory.assigned', compact('assignments'));
    }
}
