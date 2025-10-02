<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Vehicle;
use Carbon\Carbon;

class ExpiredVehiclesTable extends Component
{
    public $expiredVehicles = [];

    public function mount()
    {
        $this->loadExpiredVehicles();
    }

    public function loadExpiredVehicles()
    {
        $this->expiredVehicles = Vehicle::where('is_active', 1)
            ->where(function($query) {
                $query->where('next_inspection_date', '<', Carbon::now())
                      ->orWhere('next_fitness_date', '<', Carbon::now())
                      ->orWhere('insurance_expiry_date', '<', Carbon::now())
                      ->orWhere('route_permit_expiry_date', '<', Carbon::now())
                      ->orWhere('next_tax_date', '<', Carbon::now());
            })
            ->with(['vehicleType', 'station'])
            ->get()
            ->map(function($vehicle) {
                $reasons = [];
                if ($vehicle->next_inspection_date && Carbon::parse($vehicle->next_inspection_date)->isPast()) {
                    $reasons[] = 'Next Inspection Date';
                }
                if ($vehicle->next_fitness_date && Carbon::parse($vehicle->next_fitness_date)->isPast()) {
                    $reasons[] = 'Next Fitness Date';
                }
                if ($vehicle->insurance_expiry_date && Carbon::parse($vehicle->insurance_expiry_date)->isPast()) {
                    $reasons[] = 'Insurance Expiry Date';
                }
                if ($vehicle->route_permit_expiry_date && Carbon::parse($vehicle->route_permit_expiry_date)->isPast()) {
                    $reasons[] = 'Route Permit Expiry Date';
                }
                if ($vehicle->next_tax_date && Carbon::parse($vehicle->next_tax_date)->isPast()) {
                    $reasons[] = 'Next Tax Date';
                }
                
                return [
                    'id' => $vehicle->id,
                    'serial_no' => str_pad($vehicle->id, 9, '0', STR_PAD_LEFT),
                    'vehicle_no' => $vehicle->vehicle_no,
                    'model' => $vehicle->model,
                    'type' => $vehicle->vehicleType ? $vehicle->vehicleType->name : 'N/A',
                    'station' => $vehicle->station ? $vehicle->station->name : 'N/A',
                    'reason' => implode(', ', $reasons),
                ];
            })->toArray();
    }

    public function refresh()
    {
        $this->loadExpiredVehicles();
    }

    public function render()
    {
        return view('livewire.expired-vehicles-table');
    }
}
