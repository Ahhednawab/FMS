<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Models\WarehouseAssignment;
use App\Models\MasterWarehouseInventory;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicleId = $request->get('vehicle_id');
        $driverId = $request->get('driver_id'); // optional filter for drivers

        // Load drivers for dropdown
        $drivers = Driver::select('id', 'full_name', 'cnic_no')
            ->orderBy('full_name')
            ->get();
        if (strtolower(auth()->user()->role->slug) == "admin") {

            // Load vehicles for dropdown
            $vehicles = Vehicle::select('id', 'vehicle_no')->orderBy('vehicle_no')->get();

            return view('dashboard', compact('vehicles', 'drivers', 'vehicleId', 'driverId'));
        } else if (strtolower(auth()->user()->role->slug) == "master-warehouse") {

            $vehicles = Vehicle::select('id', 'vehicle_no')->orderBy('vehicle_no')->get();

            $lowStockInventory = MasterWarehouseInventory::with('product')
                ->when($vehicleId, function ($q) use ($vehicleId) {
                    $q->where('vehicle_id', $vehicleId);
                })
                ->whereBetween('quantity', [1, 10])
                ->orderBy('quantity', 'asc')
                ->paginate(10)
                ->withQueryString();

            return view('warehouse.dashboard', compact(
                'lowStockInventory',
                'vehicles',
                'drivers',
                'vehicleId',
                'driverId'
            ));
        } else if (strtolower(auth()->user()->role->slug) == "sub-warehouse") {

            $vehicles = Vehicle::select('id', 'vehicle_no')->orderBy('vehicle_no')->get();

            $subwarehouse = Warehouse::where('manager_id', auth()->user()->id)->first();

            $lowStockInventory = [];

            if ($subwarehouse) {
                $lowStockInventory = WarehouseAssignment::from('warehouse_assignments as wa')
                    ->join('master_warehouse_inventory as mwi', 'wa.master_inventory_id', '=', 'mwi.id')
                    ->join('products_list as pl', 'mwi.product_id', '=', 'pl.id')
                    ->when($vehicleId, function ($q) use ($vehicleId) {
                        $q->where('wa.vehicle_id', $vehicleId);
                    })
                    ->where('wa.warehouse_id', $subwarehouse->id)
                    ->whereBetween('wa.quantity', [1, 10])
                    ->select([
                        'pl.name',
                        'pl.serial_no',
                        'wa.quantity',
                        'wa.price',
                        'wa.created_at',
                    ])
                    ->orderBy('wa.quantity', 'asc')
                    ->paginate(10)
                    ->withQueryString();
            }

            return view('subwarehouse.dashboard', compact(
                'lowStockInventory',
                'vehicles',
                'drivers',
                'vehicleId',
                'driverId'
            ));
        } else if (strtolower(auth()->user()->role->slug) == "maintainer") {

            return view('admin.maintainer.index', compact('drivers'));
        }

        abort(403);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort(404);
    }
}
