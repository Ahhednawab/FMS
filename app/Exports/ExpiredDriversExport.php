<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpiredDriversExport implements FromCollection, WithHeadings, WithMapping
{
    protected $drivers;

    public function __construct($drivers)
    {
        $this->drivers = $drivers;
    }

    public function collection()
    {
        return collect($this->drivers);
    }

    public function headings(): array
    {
        return [
            'Serial No',
            'Name',
            'CNIC No',
            'Status',
            'Reason',
            'Expiry Date',
        ];
    }

    public function map($driver): array
    {
        return [
            $driver['serial_no'],
            $driver['name'],
            $driver['cnic_no'],
            $driver['status'],
            $driver['reason'],   // Reason column
            $driver['date'],     // Date column
        ];
    }
}
