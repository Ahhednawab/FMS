<?php

namespace App\Http\Controllers\Admin;

use App\Models\Vehicle;
use App\Models\Issue;
use App\Models\MasterWarehouseInventory;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

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
}
