<?php

namespace App\Console\Commands;

use App\Models\VehicleMaintenanceConfiguration;
use App\Models\VehicleMaintenanceSchedule;
use App\Services\VehicleMaintenanceScheduleService;
use Illuminate\Console\Command;

/**
 * Bring the existing predictive schedules back in line with the configuration.
 *
 * Two faults left bad rows behind, and fixing the code does not fix the rows
 * already in the table:
 *
 *  1. An item whose reading was cleared in Vehicle Maintenance Configuration
 *     kept its schedule row and went on alerting. King Pin Greasing and Fuel
 *     Filter (Large) on the HYUNDAI PORTERs are the live example.
 *
 *  2. The next due reading was rounded to the following multiple of the
 *     interval instead of a full interval later, so a service done shortly
 *     before the mark was due again almost immediately - 14,840 KM serviced,
 *     due again at 15,000.
 *
 * Run with --dry-run first to see what it would do.
 *
 *   php artisan maintenance:resync-schedules --dry-run
 *   php artisan maintenance:resync-schedules
 */
class ResyncMaintenanceSchedules extends Command
{
    protected $signature = 'maintenance:resync-schedules
                            {--dry-run : Show what would change without touching anything}';

    protected $description = 'Remove predictive schedules the configuration no longer backs and recompute due readings';

    public function __construct(private VehicleMaintenanceScheduleService $schedules)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run - nothing will be written.');
        }

        $configs = [];
        $deleted = 0;
        $recomputed = 0;
        $unchanged = 0;
        $deletedByItem = [];
        $examples = [];

        $schedules = VehicleMaintenanceSchedule::with('vehicle:id,vehicle_no,make,model,is_active')->get();

        $this->line('Checking ' . $schedules->count() . ' schedule rows...');

        foreach ($schedules as $schedule) {
            $vehicle = $schedule->vehicle;

            if (!$vehicle) {
                continue;
            }

            $key = $vehicle->make . '|' . $vehicle->model;

            if (!array_key_exists($key, $configs)) {
                $configs[$key] = VehicleMaintenanceConfiguration::forVehicle($vehicle->make, $vehicle->model);
            }

            $interval = $configs[$key]?->intervalFor($schedule->maintenance_item);

            // No active configuration, or no reading for this item.
            if ($interval === null || $interval <= 0) {
                $deleted++;
                $deletedByItem[$schedule->maintenance_item] = ($deletedByItem[$schedule->maintenance_item] ?? 0) + 1;

                if (!$dryRun) {
                    $schedule->delete();
                }

                continue;
            }

            if ($schedule->last_service_km === null) {
                $unchanged++;

                continue;
            }

            $correct = $this->schedules->nextDueFrom((int) $schedule->last_service_km, $interval);

            if ((int) $schedule->next_due_km === $correct && (int) $schedule->service_interval_km === $interval) {
                $unchanged++;

                continue;
            }

            if (count($examples) < 10) {
                $examples[] = sprintf(
                    '%-10s %-24s serviced %s, due %s -> %s',
                    $vehicle->vehicle_no,
                    $schedule->maintenance_item,
                    number_format((int) $schedule->last_service_km),
                    number_format((int) $schedule->next_due_km),
                    number_format($correct)
                );
            }

            $recomputed++;

            if (!$dryRun) {
                $schedule->update([
                    'service_interval_km' => $interval,
                    'alert_before_km' => VehicleMaintenanceConfiguration::UPCOMING_ALERT_KM,
                    'next_due_km' => $correct,
                ]);
            }
        }

        $this->newLine();
        $this->table(
            ['', 'rows'],
            [
                ['removed (no reading in configuration)', $deleted],
                ['due reading recomputed', $recomputed],
                ['already correct', $unchanged],
            ]
        );

        if ($deletedByItem !== []) {
            arsort($deletedByItem);
            $this->line('Removed by item:');
            foreach ($deletedByItem as $item => $count) {
                $this->line('  ' . str_pad($item, 26) . $count);
            }
        }

        if ($examples !== []) {
            $this->newLine();
            $this->line('Examples of the recomputed due readings:');
            foreach ($examples as $line) {
                $this->line('  ' . $line);
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('Dry run - nothing was written. Run again without --dry-run to apply.');
        }

        return self::SUCCESS;
    }

}
