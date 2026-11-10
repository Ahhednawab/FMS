<?php

namespace App\Services\Dashboard;

use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExpiredVehiclesDashboardService
{
    public function reasonList(): array
    {
        return [
            'next_inspection_date' => 'Next Inspection Expiry',
            'next_fitness_date' => 'Next Fitness Expiry',
            'insurance_expiry_date' => 'Insurance Expiry',
            'route_permit_expiry_date' => 'Route Permit Expiry',
            'next_tax_date' => 'Next Tax Expiry',
        ];
    }

    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $query = $this->buildQuery($filters);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $paginator->appends([
            'reason' => $filters['reason'] ?? '',
            'search' => $filters['search'] ?? '',
        ]);

        return $paginator->through(function (Vehicle $vehicle) use ($filters) {
            $reasonData = $this->reasonLabel($vehicle, $filters['reason'] ?? '');

            return (object) [
                'id' => $vehicle->id,
                'vehicle_no' => $vehicle->vehicle_no,
                'model' => $vehicle->model,
                'vehicle_type_name' => $vehicle->vehicleType?->name ?? 'N/A',
                'station_area' => $vehicle->station?->area ?? 'N/A',
                'reason' => $reasonData['reason'],
                'date' => $reasonData['date'],
            ];
        });
    }

    public function exportRows(array $filters): Collection
    {
        return $this->buildQuery($filters)
            ->get()
            ->map(function (Vehicle $vehicle) use ($filters) {
                $reasonData = $this->reasonLabel($vehicle, $filters['reason'] ?? '');
                $vehicle->formatted_reason = $reasonData['reason'];
                $vehicle->formatted_date = $reasonData['date'];

                return $vehicle;
            });
    }

    protected function buildQuery(array $filters): Builder
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth()->toDateString();
        $reasonList = $this->reasonList();

        $query = Vehicle::query()
            ->where('is_active', 1)
            ->with(['vehicleType', 'station']);

        // No reason picked means every expiry column counts; one or more
        // picked narrows it to those columns.
        $selected = array_filter(
            $this->selectedReasons($filters['reason'] ?? ''),
            fn (string $reason) => array_key_exists($reason, $reasonList)
        );
        $fields = $selected !== [] ? array_values($selected) : array_keys($reasonList);

        $query->where(function (Builder $query) use ($fields, $nextMonthEnd) {
            foreach ($fields as $index => $field) {
                $method = $index === 0 ? 'where' : 'orWhere';

                $query->{$method}(function (Builder $subQuery) use ($field, $nextMonthEnd) {
                    $subQuery->whereNotNull($field)
                        ->whereDate($field, '<=', $nextMonthEnd);
                });
            }
        });

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $query) use ($search) {
                $query->where('vehicle_no', 'like', '%' . $search . '%')
                    ->orWhere('model', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('vehicle_no');
    }

    /**
     * Normalise the reason filter: one reason (dashboard panel) or several
     * (Alerts page multi-select) both arrive here.
     *
     * @return array<int, string>
     */
    protected function selectedReasons(array|string|null $reason): array
    {
        $reasons = is_array($reason) ? $reason : [$reason];
        $reasons = array_map(fn ($value) => trim((string) $value), $reasons);

        return array_values(array_filter($reasons, fn (string $value) => $value !== ''));
    }

    protected function reasonLabel(Vehicle $vehicle, array|string $selectedReason = ''): array
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();
        $selected = $this->selectedReasons($selectedReason);

        $labels = [
            'next_inspection_date' => 'Next Inspection Expiry',
            'next_fitness_date' => 'Next Fitness Expiry',
            'insurance_expiry_date' => 'Insurance Expiry',
            'route_permit_expiry_date' => 'Route Permit Expiry',
            'next_tax_date' => 'Next Tax Expiry',
        ];

        $reasons = [];
        $dates = [];

        foreach ($labels as $field => $label) {
            if ($selected !== [] && !in_array($field, $selected, true)) {
                continue;
            }

            if (!$vehicle->{$field}) {
                continue;
            }

            $date = Carbon::parse($vehicle->{$field});
            if ($date->lte($nextMonthEnd)) {
                $reasons[] = $label;
                $dates[] = $date->format('d-M-Y');
            }
        }

        return [
            'reason' => count($reasons) ? implode(', ', $reasons) : '-',
            'date' => count($dates) ? implode(', ', $dates) : '-',
        ];
    }
}
