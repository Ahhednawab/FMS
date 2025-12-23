<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Masterwarehouse;
use App\Models\MasterWarehouseInventory;
use App\Models\Warehouse;
use App\Models\WarehouseAssignment;

class MasterwarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if (auth()->user()->role->slug == "master-warehouse") {
            $lowStockInventory = MasterWarehouseInventory::with('product')
                ->whereBetween('quantity', [1, 10])
                ->orderBy('quantity', 'asc')
                ->paginate(10);
            return view('warehouse.dashboard', compact('lowStockInventory'));
        } else {
            // i need inventroy of sub warehouse from MasterWarehouseInventory where the warehouse manager_id is current user id and also bring product name masterwarehouseinventrory

            $subwarehouse = Warehouse::where('manager_id', $user->id)->get();

            if (!empty($subwarehouse) && count($subwarehouse) > 0) {
                $lowStockInventory = WarehouseAssignment::from('warehouse_assignments as wa')
                    ->join('master_warehouse_inventory as mwi', 'wa.master_inventory_id', '=', 'mwi.id')
                    ->join('products_list as pl', 'mwi.product_id', '=', 'pl.id')
                    ->where('wa.warehouse_id', $subwarehouse[0]->id)
                    ->whereBetween('wa.quantity', [1, 10])
                    ->select([
                        'pl.name',
                        'pl.serial_no',
                        'wa.quantity',
                        'wa.price',
                        'wa.created_at',
                    ])
                    ->orderBy('quantity', 'asc')
                    ->paginate(10);
            }
            return view('subwarehouse.dashboard', compact('lowStockInventory'));
        }

        return view('warehouse.dashboard', compact('lowStockInventory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Masterwarehouse $masterwarehouse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Masterwarehouse $masterwarehouse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Masterwarehouse $masterwarehouse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Masterwarehouse $masterwarehouse)
    {
        //
    }
}
