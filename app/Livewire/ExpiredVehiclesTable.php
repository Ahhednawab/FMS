<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Vehicle;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExpiredVehiclesExport;
use Illuminate\Support\Facades\Log;

class ExpiredVehiclesTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Static reasons
    public $search = '';
    public $reason = '';
    public $reasonList = [
        'next_inspection_date'     => "Next Inspection Expiry",
        'next_fitness_date'        => "Next Fitness Expiry",
        'insurance_expiry_date'    => "Insurance Expiry",
        'route_permit_expiry_date' => "Route Permit Expiry",
        'next_tax_date'            => "Next Tax Expiry",
    ];

    private function getAlertThresholdDate($type)
    {
        return match ($type) {

            'insurance' => \Carbon\Carbon::now()->addYear()->subDays(15),
            'inspection' => \Carbon\Carbon::now()->addMonths(8),
            'tax' => \Carbon\Carbon::now()->addYear()->subMonth(),
            'route_permit' => \Carbon\Carbon::now()->addYears(3)->subMonth(),
            'fitness_new' => \Carbon\Carbon::now()->addMonths(6)->subMonth(),
            'fitness_old' => \Carbon\Carbon::now()->addYear()->subMonth(),
            'uniform' => \Carbon\Carbon::now()->addYear()->subDays(15),
            'sandals' => \Carbon\Carbon::now()->addMonths(6)->subDays(15),

            default => \Carbon\Carbon::now()->addYear(),
        };
    }

    private function getMaintenanceAlerts($vehicle)
    {
        $alerts = [];
        $today = now();

        // assume current km field exists
        $currentKm = $vehicle->current_km ?? 0;

        /**
         * CHANGE: Maintenance Mileage Rules
         */

        if ($vehicle->engine_oil_km && ($currentKm >= $vehicle->engine_oil_km - 500)) {
            $alerts[] = "Engine Oil Change Due (500 KM left)";
        }

        if ($vehicle->oil_filter_km && ($currentKm >= $vehicle->oil_filter_km - 500)) {
            $alerts[] = "Oil Filter Change Due";
        }

        if ($vehicle->air_filter_km && ($currentKm >= $vehicle->air_filter_km - 500)) {
            $alerts[] = "Air Filter Change Due";
        }

        if ($vehicle->transmission_oil_km && ($currentKm >= $vehicle->transmission_oil_km - 500)) {
            $alerts[] = "Transmission Oil Due";
        }

        if ($vehicle->differential_oil_km && ($currentKm >= $vehicle->differential_oil_km - 500)) {
            $alerts[] = "Differential Oil Due";
        }

        if ($vehicle->wheel_greasing_km && ($currentKm >= $vehicle->wheel_greasing_km - 500)) {
            $alerts[] = "Wheel Bearing Greasing Due";
        }

        if ($vehicle->fuel_filter_km && ($currentKm >= $vehicle->fuel_filter_km - 500)) {
            $alerts[] = "Fuel Filter Due";
        }

        if ($vehicle->power_steering_km && ($currentKm >= $vehicle->power_steering_km - 500)) {
            $alerts[] = "Power Steering Oil Due";
        }

        if ($vehicle->brake_oil_km && ($currentKm >= $vehicle->brake_oil_km - 500)) {
            $alerts[] = "Brake Oil Due";
        }

        // KING PIN (special rule)
        if ($vehicle->king_pin_km && ($currentKm >= $vehicle->king_pin_km - 200)) {
            $alerts[] = "King Pin Greasing Due (200 KM left)";
        }

        return $alerts;
    }


    public function export()
    {
        // This re-uses EXACTLY the same query as your main table
        $vehicles = $this->getFilteredVehicles()->get();

        // Create a custom collection with formatted expiry reason and date
        $formattedVehicles = $vehicles->map(function ($vehicle) {
            $reasonData = $this->getVehicleReasonLabel($vehicle);
            $vehicle->formatted_reason = $reasonData['reason'];
            $vehicle->formatted_date = $reasonData['date'];
            return $vehicle;
        });

        return Excel::download(
            new ExpiredVehiclesExport($formattedVehicles),
            'expired-vehicles-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Reset pagination when reason changes (safe to call even if main table has no pagination)
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingReason()
    {
        $this->resetPage();
    }

    public function filterVechile()
    {
        $this->resetPage();  // Reset pagination on filter change
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->reason = '';
        $this->resetPage();
    }

    /**
     * Shared query for expired/expiring vehicles up to next month end.
     * This returns an Eloquent\Builder which we can use get() or paginate() on.
     */
    protected function expiredBaseQuery()
    {
        return Vehicle::query()
            ->where('is_active', 1)
            ->with(['vehicleType', 'station']);
    }

    public function getFilteredVehicles()
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        $query = $this->expiredBaseQuery();

        // Apply reason filter
        if (!empty($this->reason) && array_key_exists($this->reason, $this->reasonList)) {
            $query->where($this->reason, '<=', $nextMonthEnd);
        } else {
            $query->where(function ($q) use ($nextMonthEnd) {
                $q->where('next_inspection_date', '<=', $nextMonthEnd)
                    ->orWhere('next_fitness_date', '<=', $nextMonthEnd)
                    ->orWhere('insurance_expiry_date', '<=', $nextMonthEnd)
                    ->orWhere('route_permit_expiry_date', '<=', $nextMonthEnd)
                    ->orWhere('next_tax_date', '<=', $nextMonthEnd);
            });
        }

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('vehicle_no', 'like', '%' . $this->search . '%')
                    ->orWhere('model', 'like', '%' . $this->search . '%');
            });
        }

        return $query;
    }


    /**
     * Get the main table vehicles with pagination
     */
    public function getMainTableVehicles()
    {
        $query = $this->getFilteredVehicles();
        return $query->paginate(10);  // Apply pagination directly
    }

    /**
     * Get the modal vehicles with pagination
     */
    public function getModalVehicles()
    {
        $query = $this->getFilteredVehicles();
        return $query->paginate(10);  // Apply pagination directly
    }


    /**
     * Helper used by Blade to show a human readable reason label(s) for a vehicle.
     * Now returns an array with 'reason' and 'date' keys.
     */
    // public function getVehicleReasonLabel($v)
    // {
    //     $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

    //     // If a specific reason filter is active → return ONLY that reason
    //     if (!empty($this->reason) && array_key_exists($this->reason, $this->reasonList)) {
    //         $field = $this->reason;
    //         $date  = $v->$field;

    //         if (!empty($date) && Carbon::parse($date)->lte($nextMonthEnd)) {
    //             return [
    //                 'reason' => $this->reasonList[$field],
    //                 'date' => Carbon::parse($date)->format('d-M-Y')
    //             ];
    //         }

    //         return ['reason' => '-', 'date' => '-'];  // if for some reason doesn't match
    //     }

    //     // Otherwise → return ALL expired reasons
    //     $reasons = [];
    //     $dates = [];

    //     if (!empty($v->next_inspection_date) && Carbon::parse($v->next_inspection_date)->lte($nextMonthEnd)) {
    //         $reasons[] = "Next Inspection";
    //         $dates[] = Carbon::parse($v->next_inspection_date)->format('d-M-Y');
    //     }
    //     if (!empty($v->next_fitness_date) && Carbon::parse($v->next_fitness_date)->lte($nextMonthEnd)) {
    //         $reasons[] = "Next Fitness";
    //         $dates[] = Carbon::parse($v->next_fitness_date)->format('d-M-Y');
    //     }
    //     if (!empty($v->insurance_expiry_date) && Carbon::parse($v->insurance_expiry_date)->lte($nextMonthEnd)) {
    //         $reasons[] = "Insurance";
    //         $dates[] = Carbon::parse($v->insurance_expiry_date)->format('d-M-Y');
    //     }
    //     if (!empty($v->route_permit_expiry_date) && Carbon::parse($v->route_permit_expiry_date)->lte($nextMonthEnd)) {
    //         $reasons[] = "Route Permit";
    //         $dates[] = Carbon::parse($v->route_permit_expiry_date)->format('d-M-Y');
    //     }
    //     if (!empty($v->next_tax_date) && Carbon::parse($v->next_tax_date)->lte($nextMonthEnd)) {
    //         $reasons[] = "Next Tax";
    //         $dates[] = Carbon::parse($v->next_tax_date)->format('d-M-Y');
    //     }

    //     return [
    //         'reason' => count($reasons) ? implode(', ', $reasons) : '-',
    //         'date' => count($dates) ? implode(', ', $dates) : '-'
    //     ];
    // }
    public function getVehicleReasonLabel($v)
    {
        $alerts = [];
        $dates = [];
        $today = \Carbon\Carbon::today();

        // =========================
        // 🧾 MASTER DATA ALERTS
        // =========================

        if ($v->insurance_expiry_date &&
            $today->gte(\Carbon\Carbon::parse($v->insurance_expiry_date)->subDays(15))) {
            $alerts[] = "Insurance Expiring Soon";
            $dates[] = $v->insurance_expiry_date;
        }

        if ($v->next_tax_date &&
            $today->gte(\Carbon\Carbon::parse($v->next_tax_date)->subMonth())) {
            $alerts[] = "Tax Expiring Soon";
            $dates[] = $v->next_tax_date;
        }

        if ($v->route_permit_expiry_date &&
            $today->gte(\Carbon\Carbon::parse($v->route_permit_expiry_date)->subMonth())) {
            $alerts[] = "Route Permit Expiring Soon";
            $dates[] = $v->route_permit_expiry_date;
        }

        if ($v->next_fitness_date &&
            $today->gte(\Carbon\Carbon::parse($v->next_fitness_date)->subMonth())) {
            $alerts[] = "Fitness Due";
            $dates[] = $v->next_fitness_date;
        }

        if ($v->next_inspection_date &&
            $today->gte(\Carbon\Carbon::parse($v->next_inspection_date))) {
            $alerts[] = "Inspection Due";
            $dates[] = $v->next_inspection_date;
        }

        // =========================
        // 🔧 MAINTENANCE ALERTS
        // =========================
        $maintenanceAlerts = $this->getMaintenanceAlerts($v);

        foreach ($maintenanceAlerts as $alert) {
            $alerts[] = $alert;
        }

        // =========================
        // FINAL RETURN
        // =========================
        return [
            'reason' => !empty($alerts) ? implode(', ', $alerts) : '-',
            'date'   => !empty($dates) ? implode(', ', $dates) : '-'
        ];
    }

    


    /**
     * Render the view
     */
    public function render()
    {
        return view('livewire.expired-vehicles-table', [
            'mainVehicles'  => $this->getMainTableVehicles(),
            'modalVehicles' => $this->getModalVehicles(),
            'reasonList'    => $this->reasonList,
        ]);
    }
}
