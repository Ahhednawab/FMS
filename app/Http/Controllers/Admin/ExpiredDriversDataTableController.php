<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpiredDriversExport;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\ExpiredDriversDashboardService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExpiredDriversDataTableController extends Controller
{
    public function index(Request $request, ExpiredDriversDashboardService $service)
    {
        $filters = [
            'filter_reason' => (string) $request->get('filter_reason', ''),
            'search' => (string) $request->get('search', ''),
            'page' => (int) $request->get('page', 1),
        ];

        $html = view('dashboard.partials.expired-drivers-section', [
            'expiredDrivers' => $service->paginate($filters),
            'reasonList' => $service->reasonList(),
            'filters' => $filters,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function export(Request $request, ExpiredDriversDashboardService $service)
    {
        $filters = [
            'filter_reason' => (string) $request->get('filter_reason', ''),
            'search' => (string) $request->get('search', ''),
        ];

        return Excel::download(
            new ExpiredDriversExport($service->exportRows($filters)),
            'expired-drivers-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
