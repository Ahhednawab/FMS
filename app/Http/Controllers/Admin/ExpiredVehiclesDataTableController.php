<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpiredVehiclesExport;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\ExpiredVehiclesDashboardService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExpiredVehiclesDataTableController extends Controller
{
    public function index(Request $request, ExpiredVehiclesDashboardService $service)
    {
        $filters = [
            'reason' => (string) $request->get('reason', ''),
            'search' => (string) $request->get('search', ''),
            'page' => (int) $request->get('page', 1),
        ];

        $html = view('dashboard.partials.expired-vehicles-section', [
            'expiredVehicles' => $service->paginate($filters),
            'reasonList' => $service->reasonList(),
            'filters' => $filters,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function export(Request $request, ExpiredVehiclesDashboardService $service)
    {
        $filters = [
            'reason' => (string) $request->get('reason', ''),
            'search' => (string) $request->get('search', ''),
        ];

        return Excel::download(
            new ExpiredVehiclesExport($service->exportRows($filters)),
            'expired-vehicles-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
