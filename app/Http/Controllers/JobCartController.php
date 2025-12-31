<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\JobCart;
use App\Models\Vehicle;
use App\Models\JobCartItem;
use App\Models\ProductList;
use Illuminate\Http\Request;
use App\Models\InventoryRequest;
use App\Models\JobCartAssignment;
use Illuminate\Support\Facades\DB;
use App\Models\WarehouseAssignment;
use App\Models\MasterWarehouseInventory;
use SebastianBergmann\Complexity\ComplexityCalculatingVisitor;

class JobCartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Base query with relationships
        $query = JobCart::with('items', 'vehicle', 'issue');

        if (auth()->user()->role->slug == "master-warehouse" || auth()->user()->role->slug == "admin") {
            // Apply status filter if selected
            if ($request->filled('status') && in_array($request->status, ['open request', 'in progress', 'closed'])) {
                $query->where('status', $request->status);
            }


            $jobCarts = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

            // Fetch assigned quantities per job cart & product
            $jobCartIds = $jobCarts->pluck('id');
            $assignedData = \DB::table('job_cart_assignments')
                ->select('job_cart_id', 'product_id', \DB::raw('SUM(quantity) as total_assigned'))
                ->whereIn('job_cart_id', $jobCartIds)
                ->groupBy('job_cart_id', 'product_id')
                ->get()
                ->groupBy('job_cart_id');


            return view('admin.jobcarts.index', compact('jobCarts', 'assignedData'));
        } else {
            // Non-admin users see only their created job carts
            $query->where('created_by', auth()->id());
            if ($request->filled('status') && in_array($request->status, ['open request', 'in progress', 'closed'])) {
                $query->where('status', $request->status);
            }


            $jobCarts = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
            $jobCartIds = $jobCarts->pluck('id');
            $assignedData = \DB::table('job_cart_assignments')
                ->select('job_cart_id', 'product_id', \DB::raw('SUM(quantity) as total_assigned'))
                ->whereIn('job_cart_id', $jobCartIds)
                ->groupBy('job_cart_id', 'product_id')
                ->get()
                ->groupBy('job_cart_id');

            return view('jobcarts.index', compact('jobCarts', 'assignedData'));
        }
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicles = Vehicle::with([
            'vehicleType',
            'station',
            'ibcCenter',
            'fabricationVendor',
            'shiftHours'
        ])
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get();

        $inventory = ProductList::where('is_active', 1)->orderBy('created_at', 'desc')
            ->get();

        $issues = Issue::where('is_active', 1)
            ->orderBy('title')
            ->get();

        return view('jobcarts.create', compact('vehicles', 'issues', 'inventory'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'issue_id'   => 'required|exists:issues,id',
                'type'       => 'required|string',
                'remarks'    => 'nullable|string',
                'inventory'  => 'required|array|min:1',
                'inventory.*.qty' => 'required|integer|min:1',
            ]);

            DB::transaction(function () use ($request, &$job) {
                $job = JobCart::create([
                    'vehicle_id' => $request->vehicle_id,
                    'issue_id'   => $request->issue_id,
                    'status'     => 'open request',
                    'type'       => $request->type,
                    'remarks'    => $request->remarks,
                    'created_by' => auth()->id(),
                ]);

                foreach ($request->inventory as $inventoryId => $data) {
                    JobCartItem::create([
                        'job_cart_id' => $job->id,
                        'product_id'  => $inventoryId,
                        'quantity'    => $data['qty'],
                    ]);
                }
            });

            // Return JSON response for AJAX
            return response()->json([
                'success' => true,
                'data' => [
                    'redirect_url' => route('maintainer.jobcarts.index')
                ],
                'message' => 'Job cart created successfully!'
            ], 200);
        } catch (\Throwable $th) {
            // Return error JSON response
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(JobCart $jobcart)
    {
        $jobcart->load([
            'vehicle',
            'issue',
            'creator',
            'items.product',
        ]);

        // 🔑 Assigned qty per job_cart + product
        $assignedQtyMap = JobCartAssignment::query()
            ->where('job_cart_id', $jobcart->id)
            ->selectRaw('product_id, SUM(quantity) as total_assigned')
            ->groupBy('product_id')
            ->pluck('total_assigned', 'product_id');

        // Only products NOT fully assigned
        $pendingProductIds = $jobcart->items
            ->filter(function ($item) use ($assignedQtyMap) {
                $alreadyAssigned = $assignedQtyMap[$item->product_id] ?? 0;
                return $alreadyAssigned < $item->quantity;
            })
            ->pluck('product_id');

        // Inventories only for pending products
        $inventories = WarehouseAssignment::query()
            ->join(
                'master_warehouse_inventory',
                'warehouse_assignments.master_inventory_id',
                '=',
                'master_warehouse_inventory.id'
            )
            ->whereIn('master_warehouse_inventory.product_id', $pendingProductIds)
            ->where('warehouse_assignments.quantity', '>', 0)
            ->orderBy('master_warehouse_inventory.expiry_date')
            ->select([
                'warehouse_assignments.id as assignment_id',
                'warehouse_assignments.quantity as assigned_quantity',
                'master_warehouse_inventory.id as inventory_id',
                'master_warehouse_inventory.product_id',
                'master_warehouse_inventory.batch_number',
                'master_warehouse_inventory.expiry_date',
                'master_warehouse_inventory.price',
            ])
            ->get()
            ->groupBy('product_id');
        return view(
            'admin.jobcarts.show',
            compact('jobcart', 'inventories', 'assignedQtyMap')
        );
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:job_carts,id',
            'status' => 'required|in:open request,in progress,closed',
        ]);

        $jobcart = JobCart::findOrFail($request->id);
        $jobcart->status = $request->status;
        $jobcart->save();

        return response()->json([
            'success' => true,
            'message' => 'Job Cart status updated successfully'
        ]);
    }
}
