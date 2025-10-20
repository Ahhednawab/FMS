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
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();
        $this->expiredVehicles = Vehicle::where('is_active', 1)
            ->where(function($query) use ($nextMonthEnd) {
                $query->where('next_inspection_date', '<=', $nextMonthEnd)
                      ->orWhere('next_fitness_date', '<=', $nextMonthEnd)
                      ->orWhere('insurance_expiry_date', '<=', $nextMonthEnd)
                      ->orWhere('route_permit_expiry_date', '<=', $nextMonthEnd)
                      ->orWhere('next_tax_date', '<=', $nextMonthEnd);
            })
            ->with(['vehicleType', 'station'])
            ->get()
            ->map(function($vehicle) use ($nextMonthEnd) {
                $reasons = [];
                // if ($vehicle->next_inspection_date && Carbon::parse($vehicle->next_inspection_date)->isPast()) {
                if ($vehicle->next_inspection_date && ($vehicle->next_inspection_date <= $nextMonthEnd)) {
                    $formatted = Carbon::parse($vehicle->next_inspection_date)->format('d-M-Y');
                    $reasons[] = "Next Inspection Date ({$formatted})";
                }
                // if ($vehicle->next_fitness_date && Carbon::parse($vehicle->next_fitness_date)->isPast()) {
                if ($vehicle->next_fitness_date && ($vehicle->next_fitness_date <= $nextMonthEnd)) {
                    $formatted = Carbon::parse($vehicle->next_fitness_date)->format('d-M-Y');
                    $reasons[] = "Next Fitness Date ({$formatted})";
                }
                // if ($vehicle->insurance_expiry_date && Carbon::parse($vehicle->insurance_expiry_date)->isPast()) {
                if ($vehicle->insurance_expiry_date && ($vehicle->insurance_expiry_date <= $nextMonthEnd)) {
                    $formatted = Carbon::parse($vehicle->insurance_expiry_date)->format('d-M-Y');
                    $reasons[] = "Insurance Expiry Date ({$formatted})";
                }
                // if ($vehicle->route_permit_expiry_date && Carbon::parse($vehicle->route_permit_expiry_date)->isPast()) {
                if ($vehicle->route_permit_expiry_date && ($vehicle->route_permit_expiry_date <= $nextMonthEnd)) {
                    $formatted = Carbon::parse($vehicle->route_permit_expiry_date)->format('d-M-Y');
                    $reasons[] = "Route Permit Expiry Date ({$formatted})";
                }
                // if ($vehicle->next_tax_date && Carbon::parse($vehicle->next_tax_date)->isPast()) {
                if ($vehicle->next_tax_date && ($vehicle->next_tax_date <= $nextMonthEnd)) {
                    $formatted = Carbon::parse($vehicle->next_tax_date)->format('d-M-Y');
                    $reasons[] = "Next Tax Date ({$formatted})";
                }
                
                return [
                    'id' => $vehicle->id,
                    'serial_no' => str_pad($vehicle->id, 9, '0', STR_PAD_LEFT),
                    'vehicle_no' => $vehicle->vehicle_no,
                    'model' => $vehicle->model,
                    'type' => $vehicle->vehicleType ? $vehicle->vehicleType->name : 'N/A',
                    'station' => $vehicle->station ? $vehicle->station->area : 'N/A',
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
