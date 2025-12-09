<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExpiredDriversExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $drivers;

    public function __construct(array $drivers)
    {
        $this->drivers = $drivers;
    }

    public function collection()
    {
        return collect($this->drivers); // works with plain PHP array
    }

    public function headings(): array
    {
        return [
            'Serial No',
            'Driver Name',
            'CNIC No',
            'Status',
            'Expired / Expiring Document(s)',
        ];
    }
}
