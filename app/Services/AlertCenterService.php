<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Notification;
use App\Models\Vehicle;
use App\Services\Dashboard\ExpiredDriversDashboardService;
use App\Services\Dashboard\ExpiredVehiclesDashboardService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

/**
 * Turns every kind of dashboard alert into one row shape so the Alerts page can
 * list them with filters, instead of each one living in its own dashboard panel
 * or pop-up.
 *
 * Alerts come from three different places:
 *  - live predictive maintenance schedules (calculated, not stored),
 *  - the vehicles / drivers tables (document expiry),
 *  - the notifications table (written by the daily notification command).
 */
class AlertCenterService
{
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_EXPIRED_VEHICLES = 'expired_vehicles';
    public const TYPE_EXPIRED_DRIVERS = 'expired_drivers';
    public const TYPE_MASTER_DATA = 'master_data';
    public const TYPE_DRIVER = 'driver';

    public function __construct(
        private VehicleMaintenanceScheduleService $maintenanceSchedule,
        private ExpiredDriversDashboardService $expiredDrivers,
        private ExpiredVehiclesDashboardService $expiredVehicles
    ) {
    }

    /** The alert types the page can show, in the order of the Type filter. */
    public function types(): array
    {
        return [
            self::TYPE_MAINTENANCE => 'Maintenance Alerts',
            self::TYPE_EXPIRED_VEHICLES => 'Vehicle Document Expiry',
            self::TYPE_EXPIRED_DRIVERS => 'Driver Document Expiry',
            self::TYPE_MASTER_DATA => 'Master Data Notifications',
            self::TYPE_DRIVER => 'Driver Notifications',
        ];
    }

    public function normalizeType(?string $type): string
    {
        return array_key_exists((string) $type, $this->types()) ? (string) $type : self::TYPE_MAINTENANCE;
    }

    /** Whether the third filter lists vehicles, drivers, or is not shown. */
    public function referenceFilter(string $type): ?string
    {
        return match ($type) {
            self::TYPE_MAINTENANCE, self::TYPE_MASTER_DATA => 'vehicle',
            self::TYPE_DRIVER => 'driver',
            default => null,
        };
    }

    /** What the reference column holds for this type. */
    public function referenceLabel(string $type): string
    {
        return in_array($type, [self::TYPE_DRIVER, self::TYPE_EXPIRED_DRIVERS], true) ? 'Driver' : 'Vehicle';
    }

    /** Label of the second filter: an alert name, or an expiry reason. */
    public function titleLabel(string $type): string
    {
        return in_array($type, [self::TYPE_EXPIRED_DRIVERS, self::TYPE_EXPIRED_VEHICLES], true) ? 'Reason' : 'Alert';
    }

    /** Options for that second filter. */
    public function titleOptions(string $type): array
    {
        return match ($type) {
            self::TYPE_MAINTENANCE => $this->selfKeyed($this->maintenanceSchedule->alertFilterTitles()),
            self::TYPE_EXPIRED_DRIVERS => $this->selfKeyed($this->expiredDrivers->reasonList()),
            self::TYPE_EXPIRED_VEHICLES => $this->expiredVehicles->reasonList(),
            default => $this->selfKeyed($this->notificationTitles($type)),
        };
    }

    public function paginate(string $type, array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return match ($type) {
            self::TYPE_MAINTENANCE => $this->maintenanceAlerts($filters, $perPage),
            self::TYPE_EXPIRED_DRIVERS => $this->expiredDriverAlerts($filters, $perPage),
            self::TYPE_EXPIRED_VEHICLES => $this->expiredVehicleAlerts($filters, $perPage),
            default => $this->notificationAlerts($type, $filters, $perPage),
        };
    }

    /**
     * Every row matching the filters, for the Excel export: the same list the
     * page shows, read page by page so nothing is silently cut off.
     */
    public function export(string $type, array $filters): Collection
    {
        $rows = collect();
        $page = 1;

        do {
            $paginator = $this->paginate($type, array_merge($filters, ['page' => $page]), 1000);
            $rows = $rows->concat($paginator->items());
            $page++;
        } while ($paginator->hasMorePages() && $page <= 50);

        return $rows;
    }

    /**
     * Mark every copy of one alert as done. The daily job writes the same alert
     * again on each run, so clearing a single row would leave the rest behind.
     */
    public function markDone(string $type, string $title, $referenceId): int
    {
        return Notification::where('type', $type)
            ->where('title', $title)
            ->where('ref_id', $referenceId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    // ---------------------------------------------------------------- sources

    private function maintenanceAlerts(array $filters, int $perPage): LengthAwarePaginator
    {
        $alerts = $this->maintenanceSchedule->dashboardAlerts([
            'vehicle_id' => $filters['reference_id'] ?? null,
            'title' => $filters['title'] ?? null,
        ]);

        $search = $this->search($filters);
        if ($search !== '') {
            $alerts = $alerts->filter(
                fn (array $alert) => stripos($alert['vehicle_no'] . ' ' . $alert['item'] . ' ' . $alert['message'], $search) !== false
            )->values();
        }

        $rows = $alerts->map(fn (array $alert) => [
            'title' => $alert['title'],
            'badge' => $alert['stage'] === 'due' ? 'danger' : 'warning',
            'message' => $alert['message'],
            'reference' => $alert['vehicle_no'],
            'detail' => 'Current ' . number_format($alert['current_km']) . ' KM / Due ' . number_format($alert['due_km']) . ' KM',
            'action_url' => route('vehicleMaintenances.index', ['vehicle_id' => [$alert['vehicle_id']]]),
            'action_label' => 'View Maintenance',
            'mark_done' => null,
        ]);

        return $this->paginateCollection($rows, $perPage, $filters);
    }

    private function expiredDriverAlerts(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->expiredDrivers->paginate([
            'filter_reason' => (string) ($filters['title'] ?? ''),
            'search' => $this->search($filters),
            'page' => $this->page($filters),
        ], $perPage)->through(fn (array $driver) => [
            'title' => $driver['reason'] !== '-' ? $driver['reason'] : 'Document Expiry',
            'badge' => str_contains((string) $driver['reason'], 'Expired') ? 'danger' : 'warning',
            'message' => 'Driver ' . $driver['name'] . ' (CNIC ' . ($driver['cnic_no'] ?: 'N/A') . '), status '
                . $driver['status'] . '. Expiry: ' . $driver['date'] . '.',
            'reference' => $driver['name'],
            'detail' => $driver['date'],
            'action_url' => route('drivers.edit', $driver['id']),
            'action_label' => 'View Driver',
            'mark_done' => null,
        ]);
    }

    private function expiredVehicleAlerts(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->expiredVehicles->paginate([
            'reason' => (string) ($filters['title'] ?? ''),
            'search' => $this->search($filters),
            'page' => $this->page($filters),
        ], $perPage)->through(fn ($vehicle) => [
            'title' => $vehicle->reason !== '-' ? $vehicle->reason : 'Document Expiry',
            'badge' => 'warning',
            'message' => 'Vehicle ' . $vehicle->vehicle_no . ' (' . $vehicle->vehicle_type_name . ', '
                . $vehicle->station_area . '). Expiry: ' . $vehicle->date . '.',
            'reference' => $vehicle->vehicle_no,
            'detail' => $vehicle->date,
            'action_url' => route('vehicles.edit', $vehicle->id),
            'action_label' => 'View Vehicle',
            'mark_done' => null,
        ]);
    }

    private function notificationAlerts(string $type, array $filters, int $perPage): LengthAwarePaginator
    {
        $title = (string) ($filters['title'] ?? '');
        $referenceId = (string) ($filters['reference_id'] ?? '');
        $search = $this->search($filters);

        $conditions = function ($query) use ($type, $title, $referenceId, $search) {
            $query->where('type', $type)
                ->where('is_read', false)
                ->when($title !== '', fn ($q) => $q->where('title', $title))
                ->when($referenceId !== '', fn ($q) => $q->where('ref_id', $referenceId))
                ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%' . $search . '%')
                        ->orWhere('message', 'like', '%' . $search . '%');
                }));
        };

        // One row per alert: the daily job re-writes the same alert on every run,
        // so only the newest copy of each (title + reference) is listed.
        $paginator = Notification::query()
            ->whereIn('id', function ($sub) use ($conditions) {
                $sub->from('notifications')->selectRaw('MAX(id)');
                $conditions($sub);
                $sub->groupBy('title', 'ref_id');
            })
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $this->page($filters));

        $names = $this->referenceNames($type, collect($paginator->items())->pluck('ref_id')->filter()->unique()->all());

        return $paginator->through(function (Notification $notification) use ($type, $names) {
            $reference = $names[$notification->ref_id] ?? ($notification->ref_id ? '#' . $notification->ref_id : '-');

            return [
                'title' => $notification->title,
                'badge' => 'info',
                'message' => $notification->message,
                'reference' => $reference,
                'detail' => optional($notification->created_at)->format('d-M-Y'),
                'action_url' => $this->notificationActionUrl($type, $notification->ref_id),
                'action_label' => $type === self::TYPE_DRIVER ? 'View Driver' : 'View Vehicle',
                'mark_done' => [
                    'type' => $type,
                    'title' => $notification->title,
                    'reference_id' => $notification->ref_id,
                ],
            ];
        });
    }

    // ---------------------------------------------------------------- helpers

    private function notificationActionUrl(string $type, $referenceId): ?string
    {
        if (!$referenceId) {
            return null;
        }

        return $type === self::TYPE_DRIVER
            ? route('drivers.edit', $referenceId)
            : route('vehicles.edit', $referenceId);
    }

    /** @return array<int|string, string> */
    private function referenceNames(string $type, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $type === self::TYPE_DRIVER
            ? Driver::whereIn('id', $ids)->pluck('full_name', 'id')->all()
            : Vehicle::whereIn('id', $ids)->pluck('vehicle_no', 'id')->all();
    }

    private function notificationTitles(string $type): array
    {
        return Notification::where('type', $type)
            ->where('is_read', false)
            ->distinct()
            ->orderBy('title')
            ->pluck('title')
            ->all();
    }

    private function selfKeyed(array $values): array
    {
        return empty($values) ? [] : array_combine($values, $values);
    }

    private function search(array $filters): string
    {
        return trim((string) ($filters['search'] ?? ''));
    }

    private function page(array $filters): int
    {
        return max(1, (int) ($filters['page'] ?? 1));
    }

    private function paginateCollection(Collection $rows, int $perPage, array $filters): LengthAwarePaginator
    {
        $page = $this->page($filters);

        return new Paginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }
}
