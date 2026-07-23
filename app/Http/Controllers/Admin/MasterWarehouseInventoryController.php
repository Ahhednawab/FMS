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
        if (!auth()->user()->hasPermission('master_warehouse_inventory')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $inventory = MasterWarehouseInventory::with('product.unit')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // ✅ FIX: Use the correct Warehouse model with scopeSubWarehouses()
        // The old code used Warehouses (legacy model) which queries a different table/columns.
        $warehouses = Warehouse::subWarehouses()->orderBy('name')->get();

        // Pass the current master warehouse so the view can show it
        $masterWarehouse = Warehouse::master()->first();
        return view('admin.master_warehouse_inventory.index', compact('inventory', 'warehouses', 'masterWarehouse'));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->hasPermission('master_warehouse_inventory')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $role_slug = auth()->user()->role->slug;

        $products = ProductList::with('unit')->orderBy('name')->get();

        // Pass the master warehouse so the view can show a warning if none is set
        $masterWarehouse = Warehouse::master()->first();

        return view('admin.master_warehouse_inventory.create', compact('products', 'role_slug', 'masterWarehouse'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('master_warehouse_inventory')) {
            abort(403, 'You do not have permission to access this page.');
        }

        // ✅ Guard: inventory must always go to the Master Warehouse first
        $masterWarehouse = Warehouse::master()->first();
        if (!$masterWarehouse) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No Master Warehouse is configured. Please set a Master Warehouse before adding inventory.');
        }

        // Validate the input data
        $validated = $request->validate([
            'product_id'  => 'required|exists:products_list,id',
            'expiry_date' => 'nullable|date',
            'quantity'    => 'required|numeric|min:0.01',
            'price'       => 'required|decimal:0,2',
        ]);

        // Set default supplier_id if not provided
        $validated['supplier_id'] = 1;

        // Generate batch number if not provided
        if (empty($validated['batch_number'])) {
            $validated['batch_number'] = MasterWarehouseInventory::GetBatchNumber();
        }

        // ✅ Auto-inject the Master Warehouse ID — inventory always goes to master first
        $validated['warehouse_id'] = $masterWarehouse->id;

        // Create a new inventory record
        MasterWarehouseInventory::create($validated);

        return redirect()->route('master_warehouse_inventory.index')
            ->with('success', 'Inventory item added successfully to Master Warehouse: ' . $masterWarehouse->name);
    }


    public function assignStock(Request $request)
    {
        $allowed_roles = ['admin', 'master-warehouse'];

        if (!in_array(auth()->user()->role->slug, $allowed_roles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        if (!auth()->user()->hasPermission('master_warehouse_inventory')) {
            abort(403, 'You do not have permission to access this page.');
        }

        // ✅ Guard: assignments must originate from the Master Warehouse
        $masterWarehouse = Warehouse::master()->first();
        if (!$masterWarehouse) {
            return response()->json([
                'success' => false,
                'message' => 'No Master Warehouse is configured. Stock cannot be transferred.'
            ], 422);
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'warehouse_id' => 'required|exists:warehouses,id',
                'quantity'     => 'required|numeric|min:0.01'
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validate the target warehouse is NOT the master (can't assign to itself)
        if ((int) $request->warehouse_id === $masterWarehouse->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot transfer stock to the Master Warehouse itself. Please select a Sub-Warehouse.'
            ], 422);
        }

        $master = MasterWarehouseInventory::with('product.unit')->findOrFail($request->master_inventory_id);

        if ((float) $request->quantity > $master->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock! Only ' . $master->quantity . ' available in Master Warehouse.'
            ], 400);
        }

        // Create assignment record
        WarehouseAssignment::create([
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

        $unitName = $master->product?->unit?->name;

        return response()->json([
            'success'      => true,
            'message'      => "Assigned {$request->quantity}" . ($unitName ? " {$unitName}" : '') . " of {$master->product->name} to warehouse!",
            'new_quantity' => $master->quantity
        ]);
    }

    public function assigned(Request $request)
    {
        if (!auth()->user()->hasPermission('assigned_inventory')) {
            abort(403, 'You do not have permission to access this page.');
        }
        if (auth()->user()->role->slug == "master-warehouse" || auth()->user()->role->slug == "admin") {
            $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;

            $assignments = WarehouseAssignment::with(['masterInventory.product.unit', 'warehouse', 'assignedBy'])
                ->when($request->filled('q'), function ($query) use ($request) {
                    $query->whereHas('masterInventory.product', fn ($product) => $product->where('name', 'like', '%' . $request->q . '%'));
                })
                ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->warehouse_id))
                ->when($request->filled('assigned_by'), fn ($query) => $query->where('assigned_by', $request->assigned_by))
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('assigned_at', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('assigned_at', '<=', $request->date_to))
                ->orderBy('assigned_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();

            $stockSummary = $this->assignedStockSummary();

            // When searching by product, show every warehouse where the
            // product is currently available or has been assigned.
            $productWarehouses = collect();
            if ($request->filled('q')) {
                $productWarehouses = WarehouseAssignment::query()
                    ->join('master_warehouse_inventory as mwi', 'warehouse_assignments.master_inventory_id', '=', 'mwi.id')
                    ->join('products_list as pl', 'mwi.product_id', '=', 'pl.id')
                    ->join('warehouses as w', 'warehouse_assignments.warehouse_id', '=', 'w.id')
                    ->leftJoin('units', 'pl.unit_id', '=', 'units.id')
                    ->where('pl.name', 'like', '%' . $request->q . '%')
                    ->groupBy('pl.id', 'pl.name', 'w.id', 'w.name', 'units.name')
                    ->orderBy('pl.name')
                    ->orderBy('w.name')
                    ->get([
                        'pl.name as product_name',
                        'w.name as warehouse_name',
                        DB::raw('units.name as unit_name'),
                        DB::raw('SUM(warehouse_assignments.quantity) as current_stock'),
                    ]);
            }

            return view('admin.master_warehouse_inventory.assigned', [
                'assignments' => $assignments,
                'stockSummary' => $stockSummary,
                'productWarehouses' => $productWarehouses,
                'filterWarehouses' => Warehouse::subWarehouses()->orderBy('name')->get(['id', 'name']),
                'filterUsers' => \App\Models\User::whereIn('id', WarehouseAssignment::whereNotNull('assigned_by')->distinct()->pluck('assigned_by'))->orderBy('name')->get(['id', 'name']),
                'perPage' => $perPage,
            ]);
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

    /**
     * Save the Low Stock Limit for a product (from the Assigned Inventory page).
     */
    public function updateLowStockLimit(Request $request)
    {
        if (!auth()->user()->hasPermission('assigned_inventory')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $validated = $request->validate([
            'product_id'      => 'required|exists:products_list,id',
            'low_stock_limit' => 'nullable|numeric|min:0',
        ]);

        ProductList::where('id', $validated['product_id'])
            ->update(['low_stock_limit' => $validated['low_stock_limit'] !== null && $validated['low_stock_limit'] !== '' ? $validated['low_stock_limit'] : null]);

        return response()->json(['success' => true]);
    }

    /**
     * Current stock per product across all sub-warehouse assignments,
     * with its low-stock status.
     *
     * Status rules: 0 → out, <= half the limit → critical,
     * <= limit → low, otherwise ok (no limit set → ok).
     */
    private function assignedStockSummary()
    {
        return WarehouseAssignment::query()
            ->join('master_warehouse_inventory as mwi', 'warehouse_assignments.master_inventory_id', '=', 'mwi.id')
            ->join('products_list as pl', 'mwi.product_id', '=', 'pl.id')
            ->leftJoin('units', 'pl.unit_id', '=', 'units.id')
            ->groupBy('mwi.product_id', 'pl.name', 'pl.low_stock_limit', 'units.name')
            ->orderBy('pl.name')
            ->get([
                'mwi.product_id as id',
                'pl.name',
                'pl.low_stock_limit',
                DB::raw('units.name as unit_name'),
                DB::raw('SUM(warehouse_assignments.quantity) as current_stock'),
            ])
            ->map(function ($row) {
                $row->current_stock = (float) $row->current_stock;
                $row->low_stock_limit = $row->low_stock_limit !== null ? (float) $row->low_stock_limit : null;
                $row->status = $this->stockStatus($row->current_stock, $row->low_stock_limit);

                return $row;
            });
    }

    /**
     * Current Stock Levels page — per-product stock with filters
     * and server-side pagination.
     */
    public function stockLevels(Request $request)
    {
        if (!auth()->user()->hasPermission('assigned_inventory')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 25;

        $query = WarehouseAssignment::query()
            ->join('master_warehouse_inventory as mwi', 'warehouse_assignments.master_inventory_id', '=', 'mwi.id')
            ->join('products_list as pl', 'mwi.product_id', '=', 'pl.id')
            ->join('warehouses as w', 'warehouse_assignments.warehouse_id', '=', 'w.id')
            ->leftJoin('units', 'pl.unit_id', '=', 'units.id')
            ->leftJoin('product_category as pc', 'pl.product_category_id', '=', 'pc.id')
            ->leftJoin('brands', 'pl.brand_id', '=', 'brands.id')
            ->when($request->filled('q'), fn ($q) => $q->where('pl.name', 'like', '%' . $request->q . '%'))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_assignments.warehouse_id', $request->warehouse_id))
            ->when($request->filled('product_category_id'), fn ($q) => $q->where('pl.product_category_id', $request->product_category_id))
            ->when($request->filled('brand_id'), fn ($q) => $q->where('pl.brand_id', $request->brand_id))
            ->groupBy('mwi.product_id', 'pl.name', 'pl.low_stock_limit', 'units.name', 'pc.name', 'brands.name')
            ->orderBy('pl.name')
            ->select([
                'mwi.product_id as id',
                'pl.name',
                'pl.low_stock_limit',
                DB::raw('units.name as unit_name'),
                DB::raw('pc.name as category_name'),
                DB::raw('brands.name as brand_name'),
                DB::raw('SUM(warehouse_assignments.quantity) as current_stock'),
                DB::raw("GROUP_CONCAT(DISTINCT w.name ORDER BY w.name SEPARATOR ', ') as warehouse_names"),
            ]);

        // Stock status filter works on the aggregated stock vs the limit.
        if ($request->stock_status === 'out') {
            $query->havingRaw('current_stock <= 0');
        } elseif ($request->stock_status === 'low') {
            $query->havingRaw('current_stock > 0 AND pl.low_stock_limit > 0 AND current_stock <= pl.low_stock_limit');
        } elseif ($request->stock_status === 'in') {
            $query->havingRaw('current_stock > 0 AND (pl.low_stock_limit IS NULL OR pl.low_stock_limit <= 0 OR current_stock > pl.low_stock_limit)');
        }

        $stockLevels = $query->paginate($perPage)->withQueryString();

        $stockLevels->getCollection()->transform(function ($row) {
            $row->current_stock = (float) $row->current_stock;
            $row->low_stock_limit = $row->low_stock_limit !== null ? (float) $row->low_stock_limit : null;
            $row->status = $this->stockStatus($row->current_stock, $row->low_stock_limit);

            return $row;
        });

        return view('admin.master_warehouse_inventory.stock_levels', [
            'stockLevels' => $stockLevels,
            'filterWarehouses' => Warehouse::subWarehouses()->orderBy('name')->get(['id', 'name']),
            'filterCategories' => \App\Models\ProductCategory::orderBy('name')->get(['id', 'name']),
            'filterBrands' => \App\Models\Brand::orderBy('name')->get(['id', 'name']),
            'perPage' => $perPage,
        ]);
    }

    private function stockStatus(float $stock, ?float $limit): string
    {
        if ($stock <= 0) {
            return 'out';
        }

        if ($limit === null || $limit <= 0) {
            return 'ok';
        }

        if ($stock <= $limit / 2) {
            return 'critical';
        }

        return $stock <= $limit ? 'low' : 'ok';
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
                    'job_cart_id'  => $request->jobcart_id,
                    'assigned_by'  => auth()->id(),
                    'assigned_to'  => auth()->id(), // technician
                    'inventory_id' => $request->inventory_id,
                    'product_id'   => $request->product_id,
                    'quantity'     => $request->quantity,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Job cart assignment processed successfully',
        ]);
    }
}
