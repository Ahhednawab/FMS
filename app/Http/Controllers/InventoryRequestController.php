<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\InventoryRequest;
use Illuminate\Support\Facades\DB;
use App\Models\WarehouseAssignment;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterWarehouseInventory;


class InventoryRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'master_inventory_id' => 'required|exists:master_warehouse_inventory,id',
            'quantity' => 'required|integer|min:1'
        ]);

        return DB::transaction(function () use ($request) {

            $inventory = MasterWarehouseInventory::lockForUpdate()
                ->findOrFail($request->master_inventory_id);

            // Check if the user already has a pending request for this inventory
            $existingRequest = InventoryRequest::where('master_inventory_id', $inventory->id)
                ->where('requested_by', Auth::id())
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'You already have a pending request for this inventory'
                ], 422);
            }

            if ($request->quantity > $inventory->quantity) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'Requested quantity exceeds available stock'
                ], 422);
            }

            InventoryRequest::create([
                'master_inventory_id' => $inventory->id,
                'requested_by'        => Auth::id(),
                'product_id'          => $inventory->product_id,
                'quantity'            => $request->quantity,
                'price'               => $inventory->price,
                'status'              => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Inventory request submitted successfully'
            ]);
        });
    }


    // Optional: list requests for sub-warehouse
    public function index(Request $request)
    {
        // Allowed statuses
        $allowedStatuses = ['pending', 'approved', 'rejected'];

        // Get status from query and validate
        $status = $request->query('status', 'pending');
        if (!in_array($status, $allowedStatuses)) {
            $status = 'pending';
        }

        $requests = InventoryRequest::with('inventory.product')
            ->where('status', $status)
            ->latest()
            ->paginate(10)
            ->withQueryString(); // preserve query params in pagination

        return view('master-warehouse.inventory_requests.index', compact('requests', 'status'));
    }


    public function approve(Request $request)
    {
        $inventoryRequest = InventoryRequest::findOrFail($request->request_id);
        if (empty($inventoryRequest)) {
            // return error as json with success , data and message
        }
        $requestedUseDetails = User::with('warehouse')->find($inventoryRequest->requested_by);
        $warehouseId = $requestedUseDetails->warehouse->id;
        $inventory = $inventoryRequest->inventory()->lockForUpdate()->first();

        DB::transaction(function () use ($inventoryRequest, $inventory, $warehouseId) {
            if ($inventoryRequest->status !== 'pending') {
                throw new \Exception('Request already processed');
            }

            if ($inventoryRequest->quantity > $inventory->quantity) {
                throw new \Exception('Insufficient stock');
            }

            $inventory->decrement('quantity', $inventoryRequest->quantity);

            $inventoryRequest->update(['status' => 'approved']);

            \App\Models\WarehouseAssignment::create([
                'master_inventory_id' => $inventory->id,
                'warehouse_id'        => $warehouseId,
                'quantity'            => $inventoryRequest->quantity,
                'batch_number'        => $inventory->batch_number,
                'expiry_date'         => $inventory->expiry_date,
                'price'               => $inventory->price,
                'assigned_by'         => auth()->id(),
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Request approved successfully'
        ]);
    }




    public function reject(Request $request, InventoryRequest $inventoryRequest)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        if ($inventoryRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Request already processed'
            ], 200);
        }

        $inventoryRequest->update([
            'status' => 'rejected',
            'reason' => $request->reason
        ]);

        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Request rejected successfully'
        ]);
    }

    public function requestedInventoryHistory()
    {
        // Get the authenticated user's requested inventory
        $requests = InventoryRequest::with('inventory.product')
            ->where('requested_by', auth()->id())
            ->latest()
            ->paginate(10);

        return view('master-warehouse.inventory_requests.requested_inventory_history', compact('requests'));
    }
}
