<?php

namespace App\Services;

use App\Models\AttendanceStatus;
use App\Models\Driver;

/**
 * Keeps `drivers.is_available` and each vehicle's current driver in step with
 * the attendance that was just marked.
 *
 * Lifted out of DriversAttendanceController so the WhatsApp review screen can
 * do exactly what the manual screen does when it saves a row, rather than a
 * near-enough copy of it that drifts apart later.
 */
class DriverAvailabilitySync
{
    /** Statuses that mean the driver is not behind a wheel today. */
    private const UNAVAILABLE = [
        'absent',
        'leave',
        'off day',
        'off',
        'inspection',
        'under maintenance',
        'under maintanance',
    ];

    public function __construct(private VehicleDriverAssignmentService $vehicleDriverAssignmentService)
    {
    }

    public function forDriver(int $driverId, int $statusId): void
    {
        $driver = Driver::with(['primaryVehicles', 'poolVehicles'])->find($driverId);

        if (!$driver) {
            return;
        }

        $statusName = strtolower((string) AttendanceStatus::where('id', $statusId)->value('name'));

        $driver->is_available = !in_array($statusName, self::UNAVAILABLE, true);
        $driver->save();

        foreach ($driver->primaryVehicles as $vehicle) {
            $this->vehicleDriverAssignmentService->resolveCurrentDriver($vehicle);
        }

        foreach ($driver->poolVehicles as $vehicle) {
            $this->vehicleDriverAssignmentService->resolveCurrentDriver($vehicle);
        }
    }
}
