<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Driver;
use Carbon\Carbon;

class ExpiredDriversTable extends Component
{
    public $expiredDrivers = [];

    public function mount()
    {
        $this->loadExpiredDrivers();
    }

    public function loadExpiredDrivers()
    {
        $this->expiredDrivers = Driver::where('is_active', 1)
            ->where(function($query) {
                $query->where('cnic_expiry_date', '<', Carbon::now())
                      ->orWhere('license_expiry_date', '<', Carbon::now());
            })
            ->with(['driverStatus', 'vehicle'])
            ->get()
            ->map(function($driver) {
                $reasons = [];
                if ($driver->cnic_expiry_date && Carbon::parse($driver->cnic_expiry_date)->isPast()) {
                    $reasons[] = 'CNIC Expiry Date';
                }
                if ($driver->license_expiry_date && Carbon::parse($driver->license_expiry_date)->isPast()) {
                    $reasons[] = 'License Expiry Date';
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
            })->toArray();
    }

    public function refresh()
    {
        $this->loadExpiredDrivers();
    }

    public function render()
    {
        return view('livewire.expired-drivers-table');
    }
}
