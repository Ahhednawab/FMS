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

    // ✅ Only From–To filters
    public $fromDate;
    public $toDate;

    /**
     * ✅ Reset pagination on filter change
     */
    public function updating($field)
    {
        if (in_array($field, ['fromDate', 'toDate'])) {
            $this->resetPage();
        }
    }

    /**
     * ✅ Clear filters
     */
    public function clearFilters()
    {
        $this->reset(['fromDate', 'toDate']);
        $this->resetPage();
    }

    /**
     * ✅ Query for expired vehicles
     */
    public function loadExpiredVehiclesQuery()
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        $query = Vehicle::query()
            ->where('is_active', 1)
            ->where(function ($query) use ($nextMonthEnd) {
                $query->where('next_inspection_date', '<=', $nextMonthEnd)
                    ->orWhere('next_fitness_date', '<=', $nextMonthEnd)
                    ->orWhere('insurance_expiry_date', '<=', $nextMonthEnd)
                    ->orWhere('route_permit_expiry_date', '<=', $nextMonthEnd)
                    ->orWhere('next_tax_date', '<=', $nextMonthEnd);
            })
            ->with(['vehicleType', 'station']);

        // ✅ Apply date filter
        if (!empty($this->fromDate) && !empty($this->toDate)) {
            $from = Carbon::parse($this->fromDate)->startOfDay();
            $to   = Carbon::parse($this->toDate)->endOfDay();

            $query->where(function ($q) use ($from, $to) {
                $q->whereBetween('next_inspection_date', [$from, $to])
                    ->orWhereBetween('next_fitness_date', [$from, $to])
                    ->orWhereBetween('insurance_expiry_date', [$from, $to])
                    ->orWhereBetween('route_permit_expiry_date', [$from, $to])
                    ->orWhereBetween('next_tax_date', [$from, $to]);
            });
        }

        return $query;
    }

    /**
     * ✅ Render
     */
    public function render()
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        $vehicles = $this->loadExpiredVehiclesQuery()->paginate(10);

        // ✅ Transform data for view
        $expiredVehicles = $vehicles->through(function ($v) use ($nextMonthEnd) {
            $reasons = [];

            if ($v->next_inspection_date && $v->next_inspection_date <= $nextMonthEnd) {
                $reasons[] = "Next Inspection Date (" . Carbon::parse($v->next_inspection_date)->format('d-M-Y') . ")";
            }
            if ($v->next_fitness_date && $v->next_fitness_date <= $nextMonthEnd) {
                $reasons[] = "Next Fitness Date (" . Carbon::parse($v->next_fitness_date)->format('d-M-Y') . ")";
            }
            if ($v->insurance_expiry_date && $v->insurance_expiry_date <= $nextMonthEnd) {
                $reasons[] = "Insurance Expiry Date (" . Carbon::parse($v->insurance_expiry_date)->format('d-M-Y') . ")";
            }
            if ($v->route_permit_expiry_date && $v->route_permit_expiry_date <= $nextMonthEnd) {
                $reasons[] = "Route Permit Expiry Date (" . Carbon::parse($v->route_permit_expiry_date)->format('d-M-Y') . ")";
            }
            if ($v->next_tax_date && $v->next_tax_date <= $nextMonthEnd) {
                $reasons[] = "Next Tax Date (" . Carbon::parse($v->next_tax_date)->format('d-M-Y') . ")";
            }

            return [
                'id'         => $v->id,
                'serial_no'  => str_pad($v->id, 9, '0', STR_PAD_LEFT),
                'vehicle_no' => $v->vehicle_no,
                'model'      => $v->model,
                'type'       => $v->vehicleType->name ?? 'N/A',
                'station'    => $v->station->area ?? 'N/A',
                'reason'     => implode(', ', $reasons),
            ];
        });

        return view('livewire.expired-vehicles-table', [
            'expiredVehicles' => $expiredVehicles,
        ]);
    }
}
