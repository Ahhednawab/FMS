<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Vehicle;
use Carbon\Carbon;

class ExpiredVehiclesTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // static reasons
    public $reason = '';
    public $reasonList = [
        'next_inspection_date'     => "Next Inspection Expiry",
        'next_fitness_date'        => "Next Fitness Expiry",
        'insurance_expiry_date'    => "Insurance Expiry",
        'route_permit_expiry_date' => "Route Permit Expiry",
        'next_tax_date'            => "Next Tax Expiry",
    ];

    /**
     * Reset pagination when reason changes (safe to call even if main table has no pagination)
     */
    public function updating($field)
    {
        if ($field === 'reason') {
            $this->resetPage();
        }
    }

    /**
     * Clear filters
     */
    public function clearFilters()
    {
        $this->reset(['reason']);
        $this->resetPage();
    }

    /**
     * Shared query for expired/expiring vehicles up to next month end.
     * This returns an Eloquent\Builder which we can use get() or paginate() on.
     */
    protected function expiredBaseQuery()
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        return Vehicle::query()
            ->where('is_active', 1)
            ->where(function ($q) use ($nextMonthEnd) {
                $q->where('next_inspection_date', '<=', $nextMonthEnd)
                    ->orWhere('next_fitness_date', '<=', $nextMonthEnd)
                    ->orWhere('insurance_expiry_date', '<=', $nextMonthEnd)
                    ->orWhere('route_permit_expiry_date', '<=', $nextMonthEnd)
                    ->orWhere('next_tax_date', '<=', $nextMonthEnd);
            })
            ->with(['vehicleType', 'station']);
    }

    /**
     * Get collection for main table (NO pagination).
     * If a reason filter is selected, apply it here for the main table.
     */
    public function getMainTableVehicles()
    {
        $query = $this->expiredBaseQuery();

        // Apply static reason filter only to main table (per your request)
        if (!empty($this->reason) && array_key_exists($this->reason, $this->reasonList)) {
            $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();
            $query->where($this->reason, '<=', $nextMonthEnd);
        }

        return $query->get();
    }

    /**
     * Get paginated results for modal (WITH pagination).
     * Modal shows ALL expired vehicles (ignores the main table's reason filter).
     */
    public function getModalVehicles()
    {
        $query = $this->expiredBaseQuery();

        // If you later want modal to also respect reason, uncomment:
        // if (!empty($this->reason) && array_key_exists($this->reason, $this->reasonList)) {
        //     $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();
        //     $query->where($this->reason, '<=', $nextMonthEnd);
        // }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Helper used by Blade to show a human readable reason label(s) for a vehicle.
     * Blade calls $this->getVehicleReasonLabel($vehicle) — so keep this public.
     */
    public function getVehicleReasonLabel($v)
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();
        $labels = [];

        if (!empty($v->next_inspection_date) && Carbon::parse($v->next_inspection_date)->lte($nextMonthEnd)) {
            $labels[] = "Next Inspection Date (" . Carbon::parse($v->next_inspection_date)->format('d-M-Y') . ")";
        }
        if (!empty($v->next_fitness_date) && Carbon::parse($v->next_fitness_date)->lte($nextMonthEnd)) {
            $labels[] = "Next Fitness Date (" . Carbon::parse($v->next_fitness_date)->format('d-M-Y') . ")";
        }
        if (!empty($v->insurance_expiry_date) && Carbon::parse($v->insurance_expiry_date)->lte($nextMonthEnd)) {
            $labels[] = "Insurance Expiry Date (" . Carbon::parse($v->insurance_expiry_date)->format('d-M-Y') . ")";
        }
        if (!empty($v->route_permit_expiry_date) && Carbon::parse($v->route_permit_expiry_date)->lte($nextMonthEnd)) {
            $labels[] = "Route Permit Expiry Date (" . Carbon::parse($v->route_permit_expiry_date)->format('d-M-Y') . ")";
        }
        if (!empty($v->next_tax_date) && Carbon::parse($v->next_tax_date)->lte($nextMonthEnd)) {
            $labels[] = "Next Tax Date (" . Carbon::parse($v->next_tax_date)->format('d-M-Y') . ")";
        }

        return count($labels) ? implode(', ', $labels) : '-';
    }

    /**
     * Render
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
