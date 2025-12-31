<?php

namespace App\Http\Controllers\Admin;

use App\Models\Warehouse;
use App\Models\Warehouses;
use App\Models\JobCartItem;
use App\Models\ProductList;
use Illuminate\Http\Request;
use App\Models\InventoryRequest;
use App\Models\JobCartAssignment;
use Illuminate\Support\Facades\DB;
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
            ->paginate(10);

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

        // Validate the input data
        $validated = $request->validate([
            'product_id' => 'required|exists:products_list,id',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|decimal:0,2',
        ]);

        // Set default supplier_id if not provided
        $validated['supplier_id'] = 1;

        // Generate batch number if not provided
        if (empty($validated['batch_number'])) {
            $validated['batch_number'] = MasterWarehouseInventory::GetBatchNumber();
        }

        // Create a new inventory record
        MasterWarehouseInventory::create($validated);  // Save new inventory item

        return redirect()->route($role_slug . '.master_warehouse_inventory.index')->with('success', 'Inventory item added successfully!');
    }


    public function assignStock(Request $request)
    {
        $valid = $request->validate([
            'warehouse_id'         => 'required|exists:warehouses,id',
            'quantity'            => 'required|integer|min:1'
        ]);

        $masterwarehouse = Warehouse::where('type', 'master')->get();
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
        if (auth()->user()->role->slug == "master-warehouse" || auth()->user()->role->slug == "admin") {
            $assignments = WarehouseAssignment::with(['masterInventory.product', 'warehouse'])
                ->orderBy('assigned_at', 'desc')
                ->paginate(25);
            return view('admin.master_warehouse_inventory.assigned', compact('assignments'));
        } else {

            $subwarehouse = Warehouse::where('manager_id', auth()->user()->id)->get();

            if (!empty($subwarehouse) && count($subwarehouse) > 0) {
                $assignments = WarehouseAssignment::from('warehouse_assignments as wa')
                    ->join('master_warehouse_inventory as mwi', 'wa.master_inventory_id', '=', 'mwi.id')
                    ->join('products_list as pl', 'mwi.product_id', '=', 'pl.id')
                    ->where('wa.warehouse_id', $subwarehouse[0]->id)
                    ->select([
                        'pl.name',
                        'pl.serial_no',
                        'wa.quantity',
                        'mwi.batch_number',
                        'mwi.expiry_date',
                        'wa.price',
                        'wa.created_at',
                    ])
                    ->orderBy('quantity', 'asc')
                    ->paginate(10);
            }
            return view('subwarehouse.master_warehouse_inventory.assigned', compact('assignments'));
        }

        return view('admin.master_warehouse_inventory.assigned', compact('assignments'));
    }

    public function requestInventory()
    {
        // Get all inventory requested by current user
        $requestedInventoryIds = InventoryRequest::where('requested_by', auth()->id())
            ->pluck('master_inventory_id')
            ->toArray();


        $requestedInventoryMap = InventoryRequest::where('requested_by', auth()->id())
            ->where('status', 'pending')
            ->pluck('quantity', 'master_inventory_id')
            ->toArray();


        // Get available inventory with quantity > 0
        $availableInventory = MasterWarehouseInventory::with('product')
            ->where('quantity', '>', 0)
            ->paginate(10);

        return view('subwarehouse.master_warehouse_inventory.request', compact('availableInventory', 'requestedInventoryIds', 'requestedInventoryMap'));
    }

    public function request()
    {
        dd("here");
    }

    public function assign(Request $request)
    {
        $request->validate([
            'assignment_id'   => 'required|exists:warehouse_assignments,id',
            'inventory_id'    => 'required|exists:master_warehouse_inventory,id',
            'product_id'      => 'required|exists:products_list,id',
            'jobcart_item_id' => 'required|exists:job_cart_items,id',
            'jobcart_id'      => 'required|exists:job_carts,id',
            'quantity'        => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            // 🔒 Lock warehouse assignment
            $warehouseAssignment = WarehouseAssignment::lockForUpdate()
                ->findOrFail($request->assignment_id);

            // 1️⃣ Check warehouse stock
            if ($request->quantity > $warehouseAssignment->quantity) {
                throw new \Exception('Insufficient quantity in warehouse');
            }

            // 🔒 Lock job cart item
            $jobCartItem = JobCartItem::lockForUpdate()
                ->where('id', $request->jobcart_item_id)
                ->where('product_id', $request->product_id)
                ->firstOrFail();

            $requestedQty = $jobCartItem->quantity;

            // 🔒 Check existing job cart assignment
            $jobCartAssignment = JobCartAssignment::where('product_id', $request->product_id)
                ->where('job_cart_id', $request->jobcart_id) // new filter
                ->lockForUpdate()
                ->first();

            $alreadyAssignedQty = $jobCartAssignment?->quantity ?? 0;

            // 2️⃣ Enforce requested quantity limit
            if (($alreadyAssignedQty + $request->quantity) > $requestedQty) {
                throw new \Exception(
                    'Assigned quantity exceeds requested quantity'
                );
            }

            // 3️⃣ Deduct from warehouse
            $warehouseAssignment->decrement('quantity', $request->quantity);

            // 4️⃣ Update or create job cart assignment
            if ($jobCartAssignment) {
                $jobCartAssignment->increment('quantity', $request->quantity);
            } else {
                JobCartAssignment::create([
                    'job_cart_id'      => $request->jobcart_id, // new column
                    'assigned_by'     => auth()->id(),
                    'assigned_to'     => auth()->id(), // technician
                    'inventory_id'    => $request->inventory_id,
                    'product_id'      => $request->product_id,
                    'quantity'        => $request->quantity,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Job cart assignment processed successfully',
        ]);
    }
}
