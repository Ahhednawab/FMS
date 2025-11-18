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

    public $filterReason = '';
    public $reasonList = [];

    /**
     * Build query for expired + expiring drivers
     */
    public function loadExpiredDriversQuery()
    {
        $today = Carbon::today();
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

        /** FILTER REASON */
        if (!empty($this->filterReason)) {
            $query->where(function ($q) {
                if ($this->filterReason === "CNIC Expiry") {
                    $q->where('cnic_expiry_date', '<=', Carbon::now()->addMonth()->endOfMonth());
                }

                if ($this->filterReason === "License Expiry") {
                    $q->where('license_expiry_date', '<=', Carbon::now()->addMonth()->endOfMonth());
                }
            });
        }

        return $query;
    }

    public function refresh()
    {
        $this->resetPage();
    }

    public function filterDrivers()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->filterReason = '';
        $this->resetPage();
    }

    /**
     * Render table view
     */
    public function render()
    {
        $today = Carbon::today();
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        /** Load all drivers (for reasonList dropdown) */
        $allDrivers = $this->loadExpiredDriversQuery()->get();

        $dynamicReasons = [];

        foreach ($allDrivers as $driver) {

            // CNIC
            if ($driver->cnic_expiry_date) {
                $d = Carbon::parse($driver->cnic_expiry_date);

                if ($d->isPast()) {
                    $dynamicReasons[] = "CNIC Expiry";
                } elseif ($d->between($today, $nextMonthEnd)) {
                    $dynamicReasons[] = "CNIC Expiry";
                }
            }

            // LICENSE
            if ($driver->license_expiry_date) {
                $d = Carbon::parse($driver->license_expiry_date);

                if ($d->isPast()) {
                    $dynamicReasons[] = "License Expiry";
                } elseif ($d->between($today, $nextMonthEnd)) {
                    $dynamicReasons[] = "License Expiry";
                }
            }
        }

        $this->reasonList = array_unique($dynamicReasons);

        /** Pagination Result */
        $drivers = $this->loadExpiredDriversQuery()->paginate(10);

        /** Add reasons to each driver */
        $expiredDrivers = $drivers->through(function ($driver) {

            $reasons = [];

            $today = Carbon::today();
            $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

            // ----- CNIC -----
            if ($driver->cnic_expiry_date) {
                $cnic = Carbon::parse($driver->cnic_expiry_date);
                $formatted = $cnic->format('d-M-Y');

                if ($cnic->isPast()) {
                    $reasons[] = "CNIC Expired ({$formatted})";
                } elseif ($cnic->between($today, $nextMonthEnd)) {
                    $reasons[] = "CNIC Expiring Soon ({$formatted})";
                }
            }

            // ----- LICENSE -----
            if ($driver->license_expiry_date) {
                $lic = Carbon::parse($driver->license_expiry_date);
                $formatted = $lic->format('d-M-Y');

                if ($lic->isPast()) {
                    $reasons[] = "License Expired ({$formatted})";
                } elseif ($lic->between($today, $nextMonthEnd)) {
                    $reasons[] = "License Expiring Soon ({$formatted})";
                }
            }

            return [
                'id' => $driver->id,
                'serial_no' => $driver->serial_no,
                'name' => $driver->full_name,
                'status' => $driver->driverStatus ? $driver->driverStatus->name : 'N/A',
                'reason' => implode(', ', $reasons),
            ];
        });

        return view('livewire.expired-drivers-table', [
            'expiredDrivers' => $expiredDrivers,
            'reasonList'     => $this->reasonList,
        ]);
    }
}
