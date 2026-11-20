<?php

namespace App\Services;

use App\Models\DailyMileageReport;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenanceConfiguration;
use App\Models\VehicleMaintenanceSchedule;
use Illuminate\Support\Collection;

/**
 * Predictive Maintenance engine.
 *
 * Drives the per-vehicle maintenance "schedule" baselines from the
 * Vehicle Maintenance Configuration (interval per make + model + predefined
 * item) and computes the Upcoming / Due alerts shown on the dashboard.
 *
 *   Due Mileage      = last service mileage + configured interval
 *   Upcoming Mileage = Due Mileage - 200 KM
 *
 * Vehicles with no maintenance history start monitoring from their current
 * mileage; vehicles with history always continue from the latest completed
 * predictive maintenance for each item.
 */
class VehicleMaintenanceScheduleService
{
    /**
     * Predefined predictive maintenance item names.
     *
     * @return array<int, string>
     */
    public function predefinedItems(): array
    {
        return VehicleMaintenanceConfiguration::itemNames();
    }

    /**
     * Agla due reading: pehle poora interval, phir agla round number.
     *
     * Due readings round hi rehti hain (5,000 / 10,000 / 15,000 …) — lekin
     * sirf agla multiple lena ghalat tha. Agar service due se thora pehle ho
     * jaye, to agla multiple foran saamne hota hai: 14,840 par service hui to
     * 15,000 agla due ban jata tha, yani 160 KM baad hi alert dobara. Client
     * ki shikayat "maintenance kar di, alert phir bhi aa raha hai" yahi thi.
     *
     * Ab reference mein poora interval jorte hain, phir use agle 1,000 par gol
     * karte hain: 14,840 + 5,000 = 19,840 → 20,000.
     *
     * Gol karne ke liye 1,000 liya hai, interval ka multiple nahi. Interval ke
     * multiple par gol karna bare intervals ke liye khatarnak hai: Power
     * Steering Oil ka interval 50,000 hai, to 27,293 par service ke baad
     * 77,293 banta hai aur 50,000 ke multiple par gol karne se 100,000 ban
     * jata — yani service 22,707 KM der se. 1,000 par gol karne se reading
     * saaf bhi rehti hai aur kabhi 999 KM se zyada aage nahi jati.
     *
     * Jahan interval khud 1,000 se chhota ho wahan gol bilkul nahi karte.
     */
    public const DUE_ROUNDING_KM = 1000;

    public function nextDueFrom(int $reference, int $interval): int
    {
        if ($interval <= 0) {
            return $reference;
        }

        $target = max(0, $reference) + $interval;

        if ($interval < self::DUE_ROUNDING_KM) {
            return $target;
        }

        return (intdiv($target - 1, self::DUE_ROUNDING_KM) + 1) * self::DUE_ROUNDING_KM;
    }

    /**
     * Latest recorded mileage (KM) for a vehicle, or null when unknown.
     */
    public function currentMileage(int $vehicleId): ?int
    {
        $value = DailyMileageReport::where('vehicle_id', $vehicleId)
            ->where('is_active', 1)
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->value('current_km');

        return $value !== null ? (int) $value : null;
    }

    /**
     * Ensure a schedule (baseline) row exists for every predefined item that
     * has a configured interval for this vehicle's make + model.
     *
     * A new row is seeded only once — from the latest completed predictive
     * maintenance if one exists, otherwise from the vehicle's current mileage.
     * Existing rows keep their baseline; only the interval / due mileage are
     * refreshed when the configuration changes.
     */
    public function syncVehicle(Vehicle $vehicle, ?int $currentKm = null): void
    {
        $config = VehicleMaintenanceConfiguration::forVehicle($vehicle->make, $vehicle->model);

        if (!$config) {
            // The configuration was switched off. Monitoring stops with it, so
            // the rows go too - otherwise they keep alerting for a make+model
            // nobody is tracking any more.
            $this->forgetItems($vehicle->id, VehicleMaintenanceConfiguration::itemNames());

            return;
        }

        $currentKm = $currentKm ?? $this->currentMileage($vehicle->id);

        $existing = VehicleMaintenanceSchedule::where('vehicle_id', $vehicle->id)
            ->get()
            ->keyBy('maintenance_item');

        $unconfigured = [];

        foreach (VehicleMaintenanceConfiguration::itemNames() as $item) {
            $interval = $config->intervalFor($item);

            if ($interval === null || $interval <= 0) {
                // No reading means this item is not serviced on this make and
                // model at all. Skipping was not enough: a row created while
                // the reading existed stayed behind and went on alerting.
                //
                // Only worth a delete when there is actually a row to delete -
                // this runs for every monitored vehicle each time the alert
                // list is opened.
                if ($existing->has($item)) {
                    $unconfigured[] = $item;
                }

                continue;
            }

            $schedule = $existing->get($item);

            if ($schedule) {
                // Keep the baseline; refresh interval + due if the config changed.
                if ((int) $schedule->service_interval_km !== $interval && $schedule->last_service_km !== null) {
                    $schedule->update([
                        'service_interval_km' => $interval,
                        'alert_before_km'     => VehicleMaintenanceConfiguration::UPCOMING_ALERT_KM,
                        'next_due_km'         => $this->nextDueFrom((int) $schedule->last_service_km, $interval),
                    ]);
                }
                continue;
            }

            [$baseline, $lastDate] = $this->baselineFor($vehicle->id, $item, $currentKm);

            if ($baseline === null) {
                continue; // no basis to start monitoring yet
            }

            VehicleMaintenanceSchedule::create([
                'vehicle_id'          => $vehicle->id,
                'maintenance_item'    => $item,
                'service_interval_km' => $interval,
                'alert_before_km'     => VehicleMaintenanceConfiguration::UPCOMING_ALERT_KM,
                'last_service_km'     => $baseline,
                'last_service_date'   => $lastDate,
                'next_due_km'         => $this->nextDueFrom($baseline, $interval),
                'last_alerted_at'     => null,
            ]);
        }

        $this->forgetItems($vehicle->id, $unconfigured);
    }

    /**
     * Drop the schedule rows for items this vehicle is no longer serviced on.
     *
     * @param  array<int, string>  $items
     */
    private function forgetItems(int $vehicleId, array $items): void
    {
        if ($items === []) {
            return;
        }

        VehicleMaintenanceSchedule::where('vehicle_id', $vehicleId)
            ->whereIn('maintenance_item', $items)
            ->delete();
    }

    /**
     * Seed schedules for every active vehicle whose make + model matches the
     * given configuration. Called when a configuration is created or updated.
     */
    public function syncConfiguration(VehicleMaintenanceConfiguration $config): void
    {
        Vehicle::where('is_active', 1)
            ->where('make', $config->make)
            ->where('model', $config->model)
            ->get()
            ->each(fn (Vehicle $vehicle) => $this->syncVehicle($vehicle));
    }

    /**
     * Advance the predictive schedule after a maintenance record is saved.
     *
     * Only predefined items that are configured for the vehicle's make + model
     * are tracked; custom "one-time" work-done items are ignored (no alerts).
     *
     * @return array<int, array{item: string, interval: int, next_due: int}>
     *         Summary used to build the "next maintenance due at X KM" message.
     */
    public function recordMaintenance(VehicleMaintenance $maintenance): array
    {
        if ($maintenance->maintenance_type !== 'predictive') {
            return [];
        }

        $config = VehicleMaintenanceConfiguration::forVehicle($maintenance->vehicle_make, $maintenance->model);

        if (!$config) {
            return [];
        }

        $maintenance->loadMissing('workDones');

        $baseline = (int) $maintenance->odometer_reading;
        $summary = [];

        foreach ($maintenance->workDoneNames() as $name) {
            $item = trim((string) $name);

            if (!VehicleMaintenanceConfiguration::isPredictiveItem($item)) {
                continue; // one-time maintenance — no schedule, no alert
            }

            $interval = $config->intervalFor($item);

            if ($interval === null || $interval <= 0) {
                continue; // item not configured for this make/model
            }

            $nextDue = $this->nextDueFrom($baseline, $interval);

            VehicleMaintenanceSchedule::updateOrCreate(
                [
                    'vehicle_id'       => $maintenance->vehicle_id,
                    'maintenance_item' => $item,
                ],
                [
                    'service_interval_km' => $interval,
                    'alert_before_km'     => VehicleMaintenanceConfiguration::UPCOMING_ALERT_KM,
                    'last_service_km'     => $baseline,
                    'last_service_date'   => $maintenance->service_date,
                    'next_due_km'         => $nextDue,
                    'last_alerted_at'     => null,
                ]
            );

            $summary[] = [
                'item'     => $item,
                'interval' => $interval,
                'next_due' => $nextDue,
            ];
        }

        return $summary;
    }

    /**
     * Compute the live Upcoming / Due predictive maintenance alerts.
     *
     * @param  array{vehicle_id?: int|string, title?: string}  $filters
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function dashboardAlerts(array $filters = []): Collection
    {
        // Make sure baselines exist for every monitored vehicle so a vehicle
        // starts being monitored the moment its configuration is in place.
        $this->syncMonitoredVehicles();

        $schedules = VehicleMaintenanceSchedule::with(['vehicle:id,vehicle_no,is_active,make,model'])
            ->whereHas('vehicle', fn ($q) => $q->where('is_active', 1))
            ->get();

        $mileageMap = [];
        $configMap = [];
        $alerts = collect();

        foreach ($schedules as $schedule) {
            $vehicle = $schedule->vehicle;

            if (!$vehicle || $schedule->next_due_km === null) {
                continue;
            }

            // The configuration has the final say, every time.
            //
            // A schedule row outlives the setting that created it: clear an
            // item's reading in Vehicle Maintenance Configuration, or switch
            // the whole configuration off, and the row stays behind and keeps
            // alerting. That is where the King Pin Greasing and Fuel Filter
            // (Large) alerts on the HYUNDAI PORTERs came from - the client is
            // right that an item with no reading should not be alerted on at
            // all. Checking here means a reading cleared in the morning stops
            // alerting the same moment, without waiting for a cleanup.
            if (!$this->isItemConfigured($vehicle, $schedule->maintenance_item, $configMap)) {
                continue;
            }

            if (!array_key_exists($schedule->vehicle_id, $mileageMap)) {
                $mileageMap[$schedule->vehicle_id] = $this->currentMileage($schedule->vehicle_id);
            }

            $current = $mileageMap[$schedule->vehicle_id];

            if ($current === null) {
                continue;
            }

            $due = (int) $schedule->next_due_km;
            $alertBefore = (int) ($schedule->alert_before_km ?: VehicleMaintenanceConfiguration::UPCOMING_ALERT_KM);
            $upcoming = $due - $alertBefore;

            if ($current >= $due) {
                $stage = 'due';
                $title = 'Maintenance Due';
                $remaining = 0;
                $message = "Vehicle {$vehicle->vehicle_no} has reached its {$schedule->maintenance_item} maintenance interval "
                    . "(due at " . number_format($due) . " KM, current " . number_format($current) . " KM). "
                    . "Please perform maintenance immediately.";
            } elseif ($current >= $upcoming) {
                $stage = 'upcoming';
                $title = 'Upcoming Maintenance';
                $remaining = $due - $current;
                $message = "Vehicle {$vehicle->vehicle_no} is approaching its {$schedule->maintenance_item} service. "
                    . "Maintenance is due in " . number_format($remaining) . " KM (at " . number_format($due) . " KM).";
            } else {
                continue;
            }

            $alerts->push([
                'id'           => $schedule->vehicle_id . '-' . $schedule->id,
                'vehicle_id'   => (int) $schedule->vehicle_id,
                'vehicle_no'   => $vehicle->vehicle_no,
                'item'         => $schedule->maintenance_item,
                'stage'        => $stage,
                'title'        => $title,
                'message'      => $message,
                'current_km'   => $current,
                'due_km'       => $due,
                'remaining_km' => $remaining,
            ]);
        }

        if (!empty($filters['vehicle_id'])) {
            $alerts = $alerts->where('vehicle_id', (int) $filters['vehicle_id']);
        }

        if (!empty($filters['title'])) {
            $alerts = $alerts->where('title', $filters['title']);
        }

        // Due alerts first, then Upcoming; within each group, most urgent first.
        return $alerts
            ->sortBy(fn ($alert) => ($alert['stage'] === 'due' ? 0 : 10_000_000) + $alert['remaining_km'])
            ->values();
    }

    /**
     * Does the vehicle's make + model still carry a reading for this item?
     *
     * Configurations are looked up once per make + model and remembered for
     * the rest of the call, so checking every schedule row costs nothing.
     *
     * @param  array<string, ?VehicleMaintenanceConfiguration>  $configMap
     */
    private function isItemConfigured(Vehicle $vehicle, string $item, array &$configMap): bool
    {
        $key = $vehicle->make . '|' . $vehicle->model;

        if (!array_key_exists($key, $configMap)) {
            $configMap[$key] = VehicleMaintenanceConfiguration::forVehicle($vehicle->make, $vehicle->model);
        }

        $interval = $configMap[$key]?->intervalFor($item);

        return $interval !== null && $interval > 0;
    }

    /**
     * Titles used to populate the dashboard "Alert" filter dropdown.
     *
     * @return array<int, string>
     */
    public function alertFilterTitles(): array
    {
        return ['Upcoming Maintenance', 'Maintenance Due'];
    }

    /**
     * Resolve the starting baseline (last service KM + date) for a fresh
     * schedule row: latest predictive maintenance if any, else current mileage.
     *
     * @return array{0: int|null, 1: \Illuminate\Support\Carbon|string|null}
     */
    private function baselineFor(int $vehicleId, string $item, ?int $currentKm): array
    {
        $lastMaint = $this->latestPredictiveMaintenanceFor($vehicleId, $item);

        if ($lastMaint) {
            return [(int) $lastMaint->odometer_reading, $lastMaint->service_date];
        }

        if ($currentKm !== null) {
            return [$currentKm, null];
        }

        return [null, null];
    }

    /**
     * The latest completed predictive maintenance for a vehicle + item.
     */
    private function latestPredictiveMaintenanceFor(int $vehicleId, string $item): ?VehicleMaintenance
    {
        return VehicleMaintenance::where('vehicle_id', $vehicleId)
            ->where('is_active', 1)
            ->where('maintenance_type', 'predictive')
            ->whereHas('workDones', fn ($q) => $q->where('name', $item))
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Ensure baselines exist for every vehicle whose make + model is configured.
     */
    private function syncMonitoredVehicles(): void
    {
        VehicleMaintenanceConfiguration::where('is_active', 1)
            ->get()
            ->each(fn (VehicleMaintenanceConfiguration $config) => $this->syncConfiguration($config));
    }
}
