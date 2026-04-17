<?php

namespace App\Console\Commands;

use App\Models\DailyMileageReport;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceSchedule;
use App\Services\VehicleDriverAssignmentService;
use App\Services\VehicleMaintenanceScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DailyNotification extends Command
{
    protected $signature = 'notification:daily';
    protected $description = 'Insert daily vehicle notifications for master data, maintenance and driver alerts';

    public function __construct(
        private VehicleDriverAssignmentService $vehicleDriverAssignmentService,
        private VehicleMaintenanceScheduleService $vehicleMaintenanceScheduleService
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $today = Carbon::today();

        $vehicles = Vehicle::with(['primaryDriver', 'poolDrivers'])->where('is_active', 1)->get();

        foreach ($vehicles as $vehicle) {
            $this->vehicleMaintenanceScheduleService->ensureDefaults($vehicle);
            $this->vehicleDriverAssignmentService->resolveCurrentDriver($vehicle);

            if ($vehicle->insurance_expiry_date) {
                $this->notifyWithinWindow(
                    $today,
                    Carbon::parse($vehicle->insurance_expiry_date),
                    15,
                    'Insurance Expiry Reminder',
                    "Insurance for vehicle {$vehicle->vehicle_no} will expire on {$vehicle->insurance_expiry_date}",
                    Notification::TYPE_MASTER_DATA,
                    $vehicle->id
                );
            }

            if ($vehicle->next_inspection_date) {
                $inspectionDate = Carbon::parse($vehicle->next_inspection_date);
                if ($today->gte($inspectionDate)) {
                    $this->createNotification(
                        'Inspection Due',
                        "Inspection for vehicle {$vehicle->vehicle_no} is due on {$inspectionDate->toDateString()}",
                        Notification::TYPE_MASTER_DATA,
                        $vehicle->id
                    );
                }
            }

            if ($vehicle->next_tax_date) {
                $this->notifyWithinWindow(
                    $today,
                    Carbon::parse($vehicle->next_tax_date),
                    30,
                    'Tax Reminder',
                    "Tax for vehicle {$vehicle->vehicle_no} is due on {$vehicle->next_tax_date}",
                    Notification::TYPE_MASTER_DATA,
                    $vehicle->id
                );
            }

            if ($vehicle->route_permit_expiry_date) {
                $this->notifyWithinWindow(
                    $today,
                    Carbon::parse($vehicle->route_permit_expiry_date),
                    30,
                    'Route Permit Reminder',
                    "Route permit for vehicle {$vehicle->vehicle_no} expires on {$vehicle->route_permit_expiry_date}",
                    Notification::TYPE_MASTER_DATA,
                    $vehicle->id
                );
            }

            if ($vehicle->next_fitness_date) {
                $this->notifyWithinWindow(
                    $today,
                    Carbon::parse($vehicle->next_fitness_date),
                    30,
                    'Fitness Reminder',
                    "Fitness for vehicle {$vehicle->vehicle_no} expires on {$vehicle->next_fitness_date}",
                    Notification::TYPE_MASTER_DATA,
                    $vehicle->id
                );
            }
        }

        $maintenanceSchedules = VehicleMaintenanceSchedule::with('vehicle')
            ->whereHas('vehicle', fn ($query) => $query->where('is_active', 1))
            ->get();

        foreach ($maintenanceSchedules as $schedule) {
            $currentKm = DailyMileageReport::where('vehicle_id', $schedule->vehicle_id)
                ->orderByDesc('report_date')
                ->value('current_km');

            if ($currentKm === null) {
                continue;
            }

            if ($schedule->next_due_km === null) {
                $schedule->update([
                    'next_due_km' => $currentKm + (int) $schedule->service_interval_km,
                ]);
                continue;
            }

            $alertAtKm = (int) $schedule->next_due_km - (int) $schedule->alert_before_km;
            if ($currentKm >= $alertAtKm && $schedule->last_alerted_at === null) {
                $this->createNotification(
                    $schedule->maintenance_item . ' Maintenance Reminder',
                    "{$schedule->maintenance_item} is due soon for vehicle {$schedule->vehicle->vehicle_no}. Current KM: {$currentKm}, Due KM: {$schedule->next_due_km}.",
                    Notification::TYPE_MAINTENANCE,
                    $schedule->vehicle_id
                );

                $schedule->update(['last_alerted_at' => now()]);
            }
        }

        $drivers = Driver::where('is_active', 1)->get();

        foreach ($drivers as $driver) {
            if ($driver->cnic_expiry_date && Carbon::parse($driver->cnic_expiry_date)->lt($today)) {
                $this->createNotification(
                    'CNIC Expired',
                    "CNIC for driver {$driver->full_name} has expired",
                    Notification::TYPE_DRIVER,
                    $driver->id
                );
            }

            if ($driver->license_expiry_date && Carbon::parse($driver->license_expiry_date)->lt($today)) {
                $this->createNotification(
                    'License Expired',
                    "License for driver {$driver->full_name} has expired",
                    Notification::TYPE_DRIVER,
                    $driver->id
                );
            }

            if ($driver->uniform_issue_date) {
                $expiryDate = Carbon::parse($driver->uniform_issue_date)->addYear();
                $this->notifyWithinWindow(
                    $today,
                    $expiryDate,
                    15,
                    'Uniform Expiry Reminder',
                    "Uniform for driver {$driver->full_name} will expire on {$expiryDate->toDateString()}.",
                    Notification::TYPE_DRIVER,
                    $driver->id
                );
            }

            if ($driver->sandal_issue_date) {
                $expiryDate = Carbon::parse($driver->sandal_issue_date)->addMonths(6);
                $this->notifyWithinWindow(
                    $today,
                    $expiryDate,
                    15,
                    'Sandal Expiry Reminder',
                    "Sandals for driver {$driver->full_name} will expire on {$expiryDate->toDateString()}.",
                    Notification::TYPE_DRIVER,
                    $driver->id
                );
            }
        }

        $this->info('Daily notifications processed successfully.');
    }

    private function notifyWithinWindow(
        Carbon $today,
        Carbon $expiry,
        int $daysBefore,
        string $title,
        string $message,
        string $type,
        int $refId
    ): void {
        if ($today->betweenIncluded($expiry->copy()->subDays($daysBefore), $expiry)) {
            $this->createNotification($title, $message, $type, $refId);
        }
    }

    private function createNotification(string $title, string $message, string $type, int $refId): void
    {
        Notification::firstOrCreate(
            [
                'title' => $title,
                'type' => $type,
                'ref_id' => $refId,
                'is_read' => false,
            ],
            [
                'message' => $message,
            ]
        );
    }
}
