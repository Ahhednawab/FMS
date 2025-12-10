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
        return [
            $vehicle->vehicle_no,
            $vehicle->model,
            $vehicle->station ? $vehicle->station->name : 'N/A',
            $vehicle->vehicleType ? $vehicle->vehicleType->name : 'N/A',
            $vehicle->formatted_expiry_reason ?? '-', // Use the pre-formatted reason
        ];
    }
}
