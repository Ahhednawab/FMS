<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleMaintenance;

class VehicleMaintenanceScheduleService
{
    public const DEFAULT_ITEMS = [
        ['maintenance_item' => 'Engine Oil', 'service_interval_km' => 5000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Oil Filter', 'service_interval_km' => 5000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Air Filter', 'service_interval_km' => 5000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Transmission Oil', 'service_interval_km' => 40000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Differential Oil', 'service_interval_km' => 40000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Wheel Bearing Greasing', 'service_interval_km' => 20000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Fuel Filter (Porter)', 'service_interval_km' => 5000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Fuel Filter Small (Master)', 'service_interval_km' => 5000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Fuel Filter Big (Master)', 'service_interval_km' => 20000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Power Steering Oil', 'service_interval_km' => 50000, 'alert_before_km' => 500],
        ['maintenance_item' => 'Brake Oil', 'service_interval_km' => 40000, 'alert_before_km' => 500],
        ['maintenance_item' => 'King Pin Greasing', 'service_interval_km' => 1500, 'alert_before_km' => 200],
    ];

    public function ensureDefaults(Vehicle $vehicle): void
    {
        foreach (self::DEFAULT_ITEMS as $item) {
            $vehicle->maintenanceSchedules()->updateOrCreate(
                ['maintenance_item' => $item['maintenance_item']],
                [
                    'service_interval_km' => $item['service_interval_km'],
                    'alert_before_km' => $item['alert_before_km'],
                    'next_due_km' => $item['service_interval_km'],
                ]
            );
        }
    }

    public function recordMaintenance(VehicleMaintenance $vehicleMaintenance): void
    {
        $vehicleMaintenance->loadMissing(['vehicle', 'maintenanceCategory']);

        if (!$vehicleMaintenance->vehicle || !$vehicleMaintenance->maintenanceCategory) {
            return;
        }

        $itemName = trim((string) $vehicleMaintenance->maintenanceCategory->category);
        if ($itemName === '') {
            return;
        }

        $schedule = $vehicleMaintenance->vehicle->maintenanceSchedules()
            ->where('maintenance_item', $itemName)
            ->first();

        if (!$schedule) {
            return;
        }

        $lastServiceKm = (int) $vehicleMaintenance->odometer_reading;

        // Prefer the alert-driven threshold/alert-before values when the user
        // explicitly linked an alert on this maintenance record.
        $intervalKm   = $vehicleMaintenance->threshold_km
            ? (int) $vehicleMaintenance->threshold_km
            : (int) $schedule->service_interval_km;

        $alertBeforeKm = $vehicleMaintenance->alert_before_km !== null
            ? (int) $vehicleMaintenance->alert_before_km
            : (int) $schedule->alert_before_km;

        $nextDueKm = $lastServiceKm + $intervalKm;

        $schedule->update([
            'last_service_km'    => $lastServiceKm,
            'last_service_date'  => $vehicleMaintenance->service_date,
            'service_interval_km'=> $intervalKm,
            'alert_before_km'    => $alertBeforeKm,
            'next_due_km'        => $nextDueKm,
            'last_alerted_at'    => null,  // reset so alert fires fresh next cycle
        ]);
    }
}
