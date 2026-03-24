<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Driver;
use App\Models\Notification;
use Illuminate\Console\Command;
use App\Models\AlertVehicleStatus;
use App\Models\DailyMileageReport;
use App\Models\Vehicle;

class DailyNotification extends Command
{
    protected $signature = 'notification:daily';
    protected $description = 'Insert daily vehicle notifications for master data, maintenance and driver alerts';

    public function handle(): void
    {
        $today = Carbon::today();

        /* ======================================================
            MASTER DATA – VEHICLE
        ====================================================== */

        $vehicles = Vehicle::all();

        foreach ($vehicles as $vehicle) {

            // Insurance – 1 year → 15 days before
            if ($vehicle->insurance_expiry_date) {
                $expiry = Carbon::parse($vehicle->insurance_expiry_date);
                if ($expiry->copy()->subDays(15)->isSameDay($today)) {
                    $this->createNotification(
                        'Insurance Expiry Reminder',
                        "Insurance for vehicle {$vehicle->vehicle_no} will expire on {$expiry->toDateString()}",
                        Notification::TYPE_MASTER_DATA,
                        $vehicle->id
                    );
                }
            }

            // Inspection – 8 months → on expiry
            if ($vehicle->inspection_date) {
                $expiry = Carbon::parse($vehicle->inspection_date)->addMonths(8);
                if ($expiry->isSameDay($today)) {
                    $this->createNotification(
                        'Inspection Due',
                        "Inspection for vehicle {$vehicle->vehicle_no} is due today",
                        Notification::TYPE_MASTER_DATA,
                        $vehicle->id
                    );
                }
            }

            // Tax – 1 year → 1 month before
            if ($vehicle->next_tax_date) {
                $expiry = Carbon::parse($vehicle->next_tax_date);
                if ($expiry->copy()->subMonth()->isSameDay($today)) {
                    $this->createNotification(
                        'Tax Reminder',
                        "Tax for vehicle {$vehicle->vehicle_no} is due on {$expiry->toDateString()}",
                        Notification::TYPE_MASTER_DATA,
                        $vehicle->id
                    );
                }
            }

            // Route Permit – 3 years → 1 month before
            if ($vehicle->route_permit_expiry_date) {
                $expiry = Carbon::parse($vehicle->route_permit_expiry_date);
                if ($expiry->copy()->subMonth()->isSameDay($today)) {
                    $this->createNotification(
                        'Route Permit Reminder',
                        "Route permit for vehicle {$vehicle->vehicle_no} expires on {$expiry->toDateString()}",
                        Notification::TYPE_MASTER_DATA,
                        $vehicle->id
                    );
                }
            }

            // Fitness – 6 months (new) / 1 year (old) → 1 month before
            if ($vehicle->fitness_date) {
                $months = $vehicle->is_new_vehicle ? 6 : 12;
                $expiry = Carbon::parse($vehicle->fitness_date)->addMonths($months);

                if ($expiry->copy()->subMonth()->isSameDay($today)) {
                    $this->createNotification(
                        'Fitness Reminder',
                        "Fitness for vehicle {$vehicle->vehicle_no} expires on {$expiry->toDateString()}",
                        Notification::TYPE_MASTER_DATA,
                        $vehicle->id
                    );
                }
            }
        }

        /* ======================================================
            MAINTENANCE – KM BASED
        ====================================================== */

        $alertStatuses = AlertVehicleStatus::with(['alert', 'vehicle'])->get();

        foreach ($alertStatuses as $status) {

            $currentKm = DailyMileageReport::where('vehicle_id', $status->vehicle_id)
                ->orderByDesc('report_date')
                ->value('current_km');

            if (!$currentKm || $status->last_mileage === null) {
                $status->update(['last_mileage' => $currentKm]);
                continue;
            }

            $threshold = $status->alert->threshold;

            // 200 km before for King Pin, otherwise 500 km
            $warningKm = $threshold == 1500 ? 200 : 500;

            if (($currentKm - $status->last_mileage) >= ($threshold - $warningKm)) {

                $this->createNotification(
                    $status->alert->title,
                    "{$status->alert->title} due soon for vehicle {$status->vehicle->vehicle_no}. Current KM: {$currentKm}",
                    Notification::TYPE_MAINTENANCE,
                    $status->vehicle_id
                );

                $status->update(['last_mileage' => $currentKm]);
            }
        }

        /* ======================================================
            DRIVER ALERTS
        ====================================================== */

        $drivers = Driver::all();

        foreach ($drivers as $driver) {

            // CNIC expired
            if ($driver->cnic_expiry_date && Carbon::parse($driver->cnic_expiry_date)->lt($today)) {
                $this->createNotification(
                    'CNIC Expired',
                    "CNIC for driver {$driver->full_name} has expired",
                    Notification::TYPE_DRIVER,
                    $driver->id
                );
            }

            // License expired
            if ($driver->license_expiry_date && Carbon::parse($driver->license_expiry_date)->lt($today)) {
                $this->createNotification(
                    'License Expired',
                    "License for driver {$driver->full_name} has expired",
                    Notification::TYPE_DRIVER,
                    $driver->id
                );
            }

            // Uniform (1 year, alert 15 days before)
            if ($driver->uniform_issue_date) {

                $expiryDate = Carbon::parse($driver->uniform_issue_date)->addYear();
                $alertDate = $expiryDate->copy()->subDays(15);

                if ($today->between($alertDate, $expiryDate)) {
                    Notification::firstOrCreate(
                        [
                            'type' => Notification::TYPE_DRIVER,
                            'ref_id' => $driver->id,
                            'title' => 'Uniform Expiry Reminder',
                        ],
                        [
                            'message' => "Uniform for driver {$driver->full_name} will expire on {$expiryDate->toDateString()}."
                        ]
                    );
                }
            }

            // Sandals (6 months, alert 15 days before)
            if ($driver->sandal_issue_date) {

                $expiryDate = Carbon::parse($driver->sandal_issue_date)->addMonths(6);
                $alertDate = $expiryDate->copy()->subDays(15);

                if ($today->between($alertDate, $expiryDate)) {
                    Notification::firstOrCreate(
                        [
                            'type' => Notification::TYPE_DRIVER,
                            'ref_id' => $driver->id,
                            'title' => 'Sandal Expiry Reminder',
                        ],
                        [
                            'message' => "Sandals for driver {$driver->full_name} will expire on {$expiryDate->toDateString()}."
                        ]
                    );
                }
            }

        }

        $this->info('Daily notifications processed successfully.');
    }

    /* ======================================================
        HELPER – PREVENT DUPLICATES
    ====================================================== */
    private function createNotification($title, $message, $type, $refId)
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
