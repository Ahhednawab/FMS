<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriversAttendance;
use App\Models\Notification;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class VehicleDriverAssignmentService
{
    public function syncAssignments(Vehicle $vehicle, int $primaryDriverId, array $poolDriverIds = []): void
    {
        $poolDriverIds = collect($poolDriverIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn ($id) => $id === $primaryDriverId)
            ->values()
            ->all();

        Vehicle::where('primary_driver_id', $primaryDriverId)
            ->where('id', '!=', $vehicle->id)
            ->update([
                'primary_driver_id' => null,
                'current_driver_id' => null,
            ]);

        Vehicle::where('current_driver_id', $primaryDriverId)
            ->where('id', '!=', $vehicle->id)
            ->update(['current_driver_id' => null]);

        Driver::where('vehicle_id', $vehicle->id)
            ->where('id', '!=', $primaryDriverId)
            ->update(['vehicle_id' => null]);

        Driver::whereKey($primaryDriverId)->update(['vehicle_id' => $vehicle->id]);

        $vehicle->poolDrivers()->sync($poolDriverIds);
        $vehicle->forceFill([
            'primary_driver_id' => $primaryDriverId,
        ])->save();

        $this->resolveCurrentDriver($vehicle->fresh(['primaryDriver', 'poolDrivers']));
    }

    public function resolveCurrentDriver(Vehicle $vehicle): ?Driver
    {
        $vehicle->loadMissing(['primaryDriver.attendances.attendanceStatus', 'poolDrivers.attendances.attendanceStatus']);

        $primary = $vehicle->primaryDriver;
        if ($primary && $this->isDriverAvailable($primary)) {
            return $this->switchToDriver($vehicle, $primary, 'Primary driver is available again.');
        }

        $poolDriver = $this->firstAvailablePoolDriver($vehicle->poolDrivers);
        if ($poolDriver) {
            return $this->switchToDriver($vehicle, $poolDriver, 'Primary driver is unavailable. Vehicle switched to an available pool driver.');
        }

        if ($vehicle->current_driver_id !== null) {
            $vehicle->forceFill(['current_driver_id' => null])->save();
        }

        return null;
    }

    public function isDriverAvailable(Driver $driver): bool
    {
        if (!$driver->is_active || !$driver->is_available) {
            return false;
        }

        $latestAttendance = DriversAttendance::with('attendanceStatus')
            ->where('driver_id', $driver->id)
            ->where('is_active', 1)
            ->latest('date')
            ->latest('id')
            ->first();

        if (!$latestAttendance || !$latestAttendance->attendanceStatus) {
            return true;
        }

        $status = strtolower(trim($latestAttendance->attendanceStatus->name));
        $unavailableStatuses = [
            'absent',
            'leave',
            'off day',
            'inspection',
            'under maintenance',
            'under maintanance',
        ];

        return !in_array($status, $unavailableStatuses, true);
    }

    protected function firstAvailablePoolDriver(Collection $poolDrivers): ?Driver
    {
        return $poolDrivers
            ->sortBy('id')
            ->first(fn (Driver $driver) => $this->isDriverAvailable($driver));
    }

    protected function switchToDriver(Vehicle $vehicle, Driver $driver, string $reason): Driver
    {
        if ((int) $vehicle->current_driver_id !== (int) $driver->id) {
            $vehicle->forceFill(['current_driver_id' => $driver->id])->save();

            Notification::firstOrCreate(
                [
                    'title' => 'Driver Assignment Switched',
                    'type' => Notification::TYPE_DRIVER,
                    'ref_id' => $vehicle->id,
                    'is_read' => false,
                ],
                [
                    'message' => "Vehicle {$vehicle->vehicle_no} is now assigned to {$driver->full_name}. {$reason}",
                ]
            );
        }

        return $driver;
    }
}
