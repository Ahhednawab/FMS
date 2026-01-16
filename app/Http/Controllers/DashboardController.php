<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\WarehouseAssignment;
use App\Models\MasterWarehouseInventory;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (strtolower(auth()->user()->role->slug == "admin")) {
            return view('dashboard');
        } else if (strtolower(auth()->user()->role->slug) == "master-warehouse") {

            $lowStockInventory = MasterWarehouseInventory::with('product')
                ->whereBetween('quantity', [1, 10])
                ->orderBy('quantity', 'asc')
                ->paginate(10);
            return view('warehouse.dashboard', compact('lowStockInventory'));
        } else if (strtolower(auth()->user()->role->slug == "sub-warehouse")) {

            $subwarehouse = Warehouse::where('manager_id', auth()->user()->id)->get();

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
        } else if (strtolower(auth()->user()->role->slug == "maintainer")) {
            return view('admin.maintainer.index');
        } else {

            // here will come a general dashboard for other roles
            dd(auth()->user()->role->slug);
        }
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
