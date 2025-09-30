<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DraftController extends Controller
{
    public function index()
    {
        $drafts = Draft::where('created_by', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.drafts.index', compact('drafts'));
    }

    public function edit(Draft $draft)
    {
        // Ensure user can only edit their own drafts
        if ($draft->created_by !== Auth::id()) {
            abort(403);
        }

        // Map module to create route
        $routeMap = [
            'users' => 'admin.users.create',
            'cities' => 'admin.cities.create',
            'stations' => 'admin.stations.create',
            'ibc_centers' => 'admin.ibcCenters.create',
            'warehouses' => 'admin.warehouses.create',
            'vehicles' => 'admin.vehicles.create',
            'drivers' => 'admin.drivers.create',
            'vendors' => 'admin.vendors.create',
            'inventory_demands' => 'admin.inventoryDemands.create',
        ];

        $route = $routeMap[$draft->module] ?? null;
        
        if (!$route) {
            abort(404, 'Module not found');
        }

        return redirect()->route($route, ['draft_id' => $draft->id]);
    }

    public function destroy(Draft $draft)
    {
        // Ensure user can only delete their own drafts
        if ($draft->created_by !== Auth::id()) {
            abort(403);
        }

        $draft->delete();

        return redirect()->route('admin.drafts.index')->with('success', 'Draft deleted successfully.');
    }
}