<?php

namespace App\Http\Controllers\Admin;

use App\Models\Issue;
use App\Models\JobCart;
use App\Models\Vehicle;

use App\Models\JobCartItem;
use App\Models\ProductList;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\MasterWarehouseInventory;
use PhpParser\Node\Stmt\TryCatch;

class MaintainerController extends Controller
{
    public function index(Request $request)
    {

        return view('admin.maintainer.index');
    }

    public function issues()
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

        $inventory = MasterWarehouseInventory::with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        $issues = Issue::where('is_active', 1)
            ->orderBy('title')
            ->get();

        return view('admin.maintainer.issues', compact(
            'vehicles',
            'inventory',
            'issues'
        ));
    }

    public function jobcart()
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

        return view('admin.maintainer.jobcart', compact(
            'vehicles',
            'inventory',
            'issues'
        ));
    }

    public function createJobCart(Request $request)
    {
        try {
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'issue_id'   => 'required|exists:issues,id',
                'status'     => 'required|string',
                'type'       => 'required|string',
                'remarks'    => 'nullable|string',
                'inventory'  => 'required|array|min:1',
                'inventory.*.qty' => 'required|integer|min:1',
            ]);

            DB::transaction(function () use ($request, &$job) {
                $job = JobCart::create([
                    'vehicle_id' => $request->vehicle_id,
                    'issue_id'   => $request->issue_id,
                    'status'     => $request->status,
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
                'data'    => $job, // JobCart object
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
}
