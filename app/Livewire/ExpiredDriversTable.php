<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Driver;
use Carbon\Carbon;

class ExpiredDriversTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $queryString = [];

    public $fromDate;
    public $toDate;

    /**
     * Build query for expired drivers
     */
    public function loadExpiredDriversQuery()
    {
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        $query = Driver::where('is_active', 1)
            ->where(function ($query) use ($nextMonthEnd) {
                $query->where('cnic_expiry_date', '<=', $nextMonthEnd)
                    ->orWhere('license_expiry_date', '<=', $nextMonthEnd);
            })
            ->with(['driverStatus', 'vehicle'])
            ->whereHas('driverStatus', function ($query) {
                $query->where('name', '!=', 'Left');
            });


        if (!empty($this->fromDate) && !empty($this->toDate)) {
            $from = Carbon::parse($this->fromDate)->startOfDay();
            $to = Carbon::parse($this->toDate)->endOfDay();

            $query->where(function ($q) use ($from, $to) {
                $q->whereBetween('cnic_expiry_date', [$from, $to])
                    ->orWhereBetween('license_expiry_date', [$from, $to]);
            });
        }

        return $query;
    }

    /**
     * Refresh table
     */
    public function refresh()
    {
        $this->resetPage();
    }

    /**
     * Apply filters (called on change)
     */
    public function filterDrivers()
    {
        $this->resetPage();
    }

    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->fromDate = null;
        $this->toDate = null;
        $this->resetPage();
    }

    /**
     * Render table view
     */
    public function render()
    {
        $drivers = $this->loadExpiredDriversQuery()->paginate(10);

        $expiredDrivers = $drivers->through(function ($driver) {
            $reasons = [];

            if ($driver->cnic_expiry_date && Carbon::parse($driver->cnic_expiry_date)->isPast()) {
                $formattedDate = Carbon::parse($driver->cnic_expiry_date)->format('d-M-Y');
                $reasons[] = "CNIC Expiry Date ({$formattedDate})";
            }

            if ($driver->license_expiry_date && Carbon::parse($driver->license_expiry_date)->isPast()) {
                $formattedDate = Carbon::parse($driver->license_expiry_date)->format('d-M-Y');
                $reasons[] = "License Expiry Date ({$formattedDate})";
            }

            return [
                'id' => $driver->id,
                'serial_no' => $driver->serial_no,
                'name' => $driver->full_name,
                'status' => $driver->driverStatus ? $driver->driverStatus->name : 'N/A',
                'reason' => implode(', ', $reasons),
                'cnic_expiry' => $driver->cnic_expiry_date,
                'license_expiry' => $driver->license_expiry_date,
            ];
        });

        return view('livewire.expired-drivers-table', [
            'expiredDrivers' => $expiredDrivers,
        ]);
    }
}
