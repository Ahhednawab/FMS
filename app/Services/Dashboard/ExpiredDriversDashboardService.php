<?php

namespace App\Services\Dashboard;

use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ExpiredDriversDashboardService
{
    public function reasonList(): array
    {
        return ['CNIC Expiry', 'License Expiry'];
    }

    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $query = $this->buildQuery($filters);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $paginator->appends([
            'filter_reason' => $filters['filter_reason'] ?? '',
            'search' => $filters['search'] ?? '',
        ]);

        return $paginator->through(function (Driver $driver) use ($filters) {
            $reasonData = $this->reasonLabel($driver, $filters['filter_reason'] ?? '');

            return [
                'id' => $driver->id,
                'serial_no' => $driver->serial_no,
                'name' => $driver->full_name,
                'cnic_no' => $driver->cnic_no,
                'status' => $driver->driverStatus?->name ?? 'N/A',
                'reason' => $reasonData['reason'],
                'date' => $reasonData['date'],
            ];
        });
    }

    public function exportRows(array $filters): array
    {
        return $this->buildQuery($filters)
            ->get()
            ->map(function (Driver $driver) use ($filters) {
                $reasonData = $this->reasonLabel($driver, $filters['filter_reason'] ?? '');

                return [
                    'serial_no' => $driver->serial_no,
                    'name' => $driver->full_name,
                    'cnic_no' => $driver->cnic_no,
                    'status' => $driver->driverStatus?->name ?? 'N/A',
                    'reason' => $reasonData['reason'],
                    'date' => $reasonData['date'],
                ];
            })
            ->all();
    }

    protected function buildQuery(array $filters): Builder
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth()->toDateString();

        $query = Driver::query()
            ->where('is_active', 1)
            ->where(function (Builder $query) use ($nextMonthEnd) {
                $query->where(function (Builder $subQuery) use ($nextMonthEnd) {
                    $subQuery->whereNotNull('cnic_expiry_date')
                        ->whereDate('cnic_expiry_date', '<=', $nextMonthEnd);
                })->orWhere(function (Builder $subQuery) use ($nextMonthEnd) {
                    $subQuery->whereNotNull('license_expiry_date')
                        ->whereDate('license_expiry_date', '<=', $nextMonthEnd);
                });
            })
            ->with(['driverStatus', 'vehicle'])
            ->whereHas('driverStatus', function (Builder $query) {
                $query->where('name', '!=', 'Left');
            });

        if (!empty($filters['filter_reason'])) {
            $query->where(function (Builder $query) use ($filters, $nextMonthEnd) {
                if ($filters['filter_reason'] === 'CNIC Expiry') {
                    $query->whereNotNull('cnic_expiry_date')
                        ->whereDate('cnic_expiry_date', '<=', $nextMonthEnd);
                }

                if ($filters['filter_reason'] === 'License Expiry') {
                    $query->whereNotNull('license_expiry_date')
                        ->whereDate('license_expiry_date', '<=', $nextMonthEnd);
                }
            });
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $query) use ($search) {
                $query->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('serial_no', 'like', '%' . $search . '%')
                    ->orWhere('cnic_no', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('full_name');
    }

    protected function reasonLabel(Driver $driver, string $filterReason = ''): array
    {
        $today = Carbon::today();
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();
        $reasons = [];
        $dates = [];

        $checks = [
            'CNIC Expiry' => ['date' => $driver->cnic_expiry_date, 'label' => 'CNIC'],
            'License Expiry' => ['date' => $driver->license_expiry_date, 'label' => 'License'],
        ];

        foreach ($checks as $reason => $config) {
            if ($filterReason !== '' && $filterReason !== $reason) {
                continue;
            }

            if (!$config['date']) {
                continue;
            }

            $date = Carbon::parse($config['date']);
            if ($date->isPast() || $date->betweenIncluded($today, $nextMonthEnd)) {
                $reasons[] = $config['label'] . ' ' . ($date->isPast() ? 'Expired' : 'Expiring Soon');
                $dates[] = $date->format('d-M-Y');
            }
        }

        return [
            'reason' => count($reasons) ? implode(', ', $reasons) : '-',
            'date' => count($dates) ? implode(', ', $dates) : '-',
        ];
    }
}
