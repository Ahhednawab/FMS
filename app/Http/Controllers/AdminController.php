<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\Dashboard\ExpiredDriversDashboardService;
use App\Services\Dashboard\ExpiredVehiclesDashboardService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(
        ExpiredDriversDashboardService $expiredDriversService,
        ExpiredVehiclesDashboardService $expiredVehiclesService
    )
    {
        $drivers = Driver::select('id', 'full_name', 'cnic_no')
            ->orderBy('full_name')
            ->get();

        $vehicles = Vehicle::select('id', 'vehicle_no')
            ->orderBy('vehicle_no')
            ->get();

        return view('dashboard', [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'vehicleId' => null,
            'driverId' => null,
            'expiredDrivers' => $expiredDriversService->paginate([
                'filter_reason' => '',
                'search' => '',
                'page' => 1,
            ]),
            'expiredVehicles' => $expiredVehiclesService->paginate([
                'reason' => '',
                'search' => '',
                'page' => 1,
            ]),
            'driverReasonList' => $expiredDriversService->reasonList(),
            'vehicleReasonList' => $expiredVehiclesService->reasonList(),
        ]);
    }
}
