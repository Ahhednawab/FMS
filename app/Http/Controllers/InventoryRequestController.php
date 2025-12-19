<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\InventoryRequest;
use Illuminate\Support\Facades\DB;
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
    public function index()
    {
        $requests = InventoryRequest::with('inventory.product')->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('master-warehouse.inventory_requests.index', compact('requests'));
    }


    public function approve(Request $request)
    {
        $inventoryRequest = InventoryRequest::findOrFail($request->request_id);
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
        if ($inventoryRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Request already processed'
            ], 200);
        }

        $inventoryRequest->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Request rejected successfully'
        ]);
    }
}
