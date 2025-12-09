<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;  // Fixed: No extra \Excel\
use Maatwebsite\Excel\Concerns\WithMapping;   // Fixed: No extra \Excel\
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ExpiredVehiclesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $vehicles;

    public function __construct($vehicles)
    {
        $this->vehicles = $vehicles;
    }

    public function collection()
    {
        return $this->vehicles;
    }

    public function headings(): array
    {
        return [
            'Serial No',
            'Vehicle No',
            'Model',
            'Type',
            'Station',
            'Reason',
        ];
    }

    public function map($vehicle): array
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        $reasons = [];

        if ($vehicle->next_inspection_date && Carbon::parse($vehicle->next_inspection_date)->lte($nextMonthEnd)) {
            $reasons[] = 'Next Inspection (' . Carbon::parse($vehicle->next_inspection_date)->format('d-M-Y') . ')';
        }
        if ($vehicle->next_fitness_date && Carbon::parse($vehicle->next_fitness_date)->lte($nextMonthEnd)) {
            $reasons[] = 'Next Fitness (' . Carbon::parse($vehicle->next_fitness_date)->format('d-M-Y') . ')';
        }
        if ($vehicle->insurance_expiry_date && Carbon::parse($vehicle->insurance_expiry_date)->lte($nextMonthEnd)) {
            $reasons[] = 'Insurance (' . Carbon::parse($vehicle->insurance_expiry_date)->format('d-M-Y') . ')';
        }
        if ($vehicle->route_permit_expiry_date && Carbon::parse($vehicle->route_permit_expiry_date)->lte($nextMonthEnd)) {
            $reasons[] = 'Route Permit (' . Carbon::parse($vehicle->route_permit_expiry_date)->format('d-M-Y') . ')';
        }
        if ($vehicle->next_tax_date && Carbon::parse($vehicle->next_tax_date)->lte($nextMonthEnd)) {
            $reasons[] = 'Next Tax (' . Carbon::parse($vehicle->next_tax_date)->format('d-M-Y') . ')';
        }

        return [
            str_pad($vehicle->id, 9, '0', STR_PAD_LEFT),
            $vehicle->vehicle_no,
            $vehicle->model,
            $vehicle->vehicleType?->name ?? 'N/A',
            $vehicle->station?->area ?? 'N/A',
            $reasons ? implode(' | ', $reasons) : 'All Valid',
        ];
    }
}
