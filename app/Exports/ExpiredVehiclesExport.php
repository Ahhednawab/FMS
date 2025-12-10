<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpiredVehiclesExport implements FromCollection, WithHeadings, WithMapping
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
            'Reason',      // Separate column for reason
            'Expiry Date', // Separate column for date
        ];
    }

    public function map($vehicle): array
    {
        return [
            str_pad($vehicle->id, 9, '0', STR_PAD_LEFT),
            $vehicle->vehicle_no,
            $vehicle->model,
            $vehicle->vehicleType ? $vehicle->vehicleType->name : 'N/A',
            $vehicle->station ? $vehicle->station->area : 'N/A',
            $vehicle->formatted_reason ?? '-',  // Reason column
            $vehicle->formatted_date ?? '-',    // Date column
        ];
    }
}
