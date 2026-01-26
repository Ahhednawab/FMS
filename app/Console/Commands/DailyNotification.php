<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Driver;
use App\Models\MileageAlert;
use App\Models\Notification;
use App\Models\DailyFuelReport;
use Illuminate\Console\Command;
use App\Models\AlertVehicleStatus;
use App\Models\DailyMileageReport;
use App\Models\Vehicle; // Make sure you have this model

class DailyNotification extends Command
{
    protected $signature = 'notification:daily';
    protected $description = 'Insert daily vehicle notifications for maintenance events';

    public function handle(): void
    {
        $today = Carbon::today();

        // ----------------------
        // Insurance Expired
        // ----------------------
        $insuranceVehicles = Vehicle::whereNotNull('insurance_expiry_date')->get();

        foreach ($insuranceVehicles as $vehicle) {
            $expiry = Carbon::parse($vehicle->insurance_expiry_date);
            $reminderDate = $expiry->copy()->subDays(15);

            if ($reminderDate->isSameDay($today)) {
                Notification::create([
                    'title' => 'Insurance Expiry Reminder',
                    'message' => "Insurance for vehicle {$vehicle->vehicle_no} will expire on {$expiry->toDateString()}.",
                    'type' => Notification::TYPE_MASTER_DATA,
                    'ref_id' => $vehicle->id,
                ]);
            }
        }

        // ----------------------
        // Inspection Due
        // ----------------------
        $inspectionVehicles = Vehicle::whereNotNull('next_inspection_date')->get();

        foreach ($inspectionVehicles as $vehicle) {
            $expiry = Carbon::parse($vehicle->next_inspection_date);
            $reminderDate = $expiry->copy()->subDays(15);

            if ($reminderDate->isSameDay($today)) {
                Notification::create([
                    'title' => 'Inspection Reminder',
                    'message' => "Inspection for vehicle {$vehicle->vehicle_no} is due on {$expiry->toDateString()}.",
                    'type' => Notification::TYPE_MASTER_DATA,
                    'ref_id' => $vehicle->id,
                ]);
            }
        }

        // ----------------------
        // Taxation Due
        // ----------------------
        $taxVehicles = Vehicle::whereNotNull('next_tax_date')->get();

        foreach ($taxVehicles as $vehicle) {
            $expiry = Carbon::parse($vehicle->next_tax_date);
            $reminderDate = $expiry->copy()->subMonth();

            if ($reminderDate->isSameDay($today)) {
                Notification::create([
                    'title' => 'Tax Reminder',
                    'message' => "Tax for vehicle {$vehicle->vehicle_no} is due on {$expiry->toDateString()}.",
                    'type' => Notification::TYPE_MASTER_DATA,
                    'ref_id' => $vehicle->id,
                ]);
            }
        }

        // ----------------------
        // Route Permit Expired
        // ----------------------
        $routePermitVehicles = Vehicle::whereNotNull('route_permit_expiry_date')->get();

        foreach ($routePermitVehicles as $vehicle) {
            $expiry = Carbon::parse($vehicle->route_permit_expiry_date);
            $reminderDate = $expiry->copy()->subMonth();

            if ($reminderDate->isSameDay($today)) {
                Notification::create([
                    'title' => 'Route Permit Reminder',
                    'message' => "Route permit for vehicle {$vehicle->vehicle_no} will expire on {$expiry->toDateString()}.",
                    'type' => Notification::TYPE_MASTER_DATA,
                    'ref_id' => $vehicle->id,
                ]);
            }
        }

        // ----------------------
        // Fitness Due
        // ----------------------
        $fitnessVehicles = Vehicle::whereNotNull('next_fitness_date')->get();

        foreach ($fitnessVehicles as $vehicle) {
            $expiry = Carbon::parse($vehicle->next_fitness_date);
            $reminderDate = $expiry->copy()->subMonth();

            if ($reminderDate->isSameDay($today)) {
                Notification::create([
                    'title' => 'Fitness Reminder',
                    'message' => "Fitness for vehicle {$vehicle->vehicle_no} is due on {$expiry->toDateString()}.",
                    'type' => Notification::TYPE_MASTER_DATA,
                    'ref_id' => $vehicle->id,
                ]);
            }
        }



        // ----------------------
        // Mileage-based Alerts
        // ----------------------
        $alertStatuses = AlertVehicleStatus::with(['alert', 'vehicle'])->get();

        foreach ($alertStatuses as $status) {

            $currentMileage = DailyMileageReport::where('vehicle_id', $status->vehicle_id)
                ->orderByDesc('report_date')
                ->value('current_km');

            if (!$currentMileage || $status->last_mileage === null) {
                $status->update(['last_mileage' => $currentMileage]);
                continue;
            }

            $difference = $currentMileage - $status->last_mileage;

            if ($difference >= $status->alert->threshold) {

                Notification::create([
                    'title' => $status->alert->title,
                    'message' => "The {$status->alert->title} alert has triggered for vehicle {$status->vehicle->vehicle_no}. Current mileage: {$currentMileage} km.",
                    'type' => Notification::TYPE_MAINTENANCE,
                    'ref_id' => $status->vehicle_id,
                ]);

                // Update last mileage
                $status->update([
                    'last_mileage' => $currentMileage,
                ]);
            }
        }


        // ----------------------
        // Driver-related Alerts
        // ----------------------
        $drivers = Driver::all();

        foreach ($drivers as $driver) {

            // CNIC Expired
            if ($driver->cnic_expiry_date && Carbon::parse($driver->cnic_expiry_date)->lt($today)) {
                Notification::create([
                    'title' => 'CNIC Expired',
                    'message' => "CNIC for driver {$driver->full_name} expired on {$driver->cnic_expiry_date}.",
                    'type' => Notification::TYPE_DRIVER,
                    'ref_id' => $driver->id,
                ]);
            }

            // License Expired
            if ($driver->license_expiry_date && Carbon::parse($driver->license_expiry_date)->lt($today)) {
                Notification::create([
                    'title' => 'License Expired',
                    'message' => "Driving license for driver {$driver->full_name} expired on {$driver->license_expiry_date}.",
                    'type' => Notification::TYPE_DRIVER,
                    'ref_id' => $driver->id,
                ]);
            }

            // Uniform Expiry Reminder (15 days before, but show actual expiry date)
            if ($driver->uniform_issue_date) {
                $uniformExpiryDate = Carbon::parse($driver->uniform_issue_date);
                $uniformReminderDate = $uniformExpiryDate->subDays(15);

                if ($uniformReminderDate->isSameDay($today)) {
                    Notification::create([
                        'title' => 'Uniform Expiry Reminder',
                        'message' => "Uniform for driver {$driver->full_name} will expire on {$uniformExpiryDate->toDateString()}.",
                        'type' => Notification::TYPE_DRIVER,
                        'ref_id' => $driver->id,
                    ]);
                }
            }

            // Sandal Expiry Reminder (15 days before, but show actual expiry date)
            if ($driver->sandal_issue_date) {
                $sandalExpiryDate = Carbon::parse($driver->sandal_issue_date);
                $sandalReminderDate = $sandalExpiryDate->subDays(15);

                if ($sandalReminderDate->isSameDay($today)) {
                    Notification::create([
                        'title' => 'Sandal Expiry Reminder',
                        'message' => "Sandal for driver {$driver->full_name} will expire on {$sandalExpiryDate->toDateString()}.",
                        'type' => Notification::TYPE_DRIVER,
                        'ref_id' => $driver->id,
                    ]);
                }
            }
        }

        $this->info('Daily vehicle notifications inserted successfully.');
    }
}
