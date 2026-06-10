<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TrackingReportsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private $trackingReports)
    {
    }

    public function collection()
    {
        return collect($this->trackingReports);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Vehicle',
            'AKPL',
            'Shift',
            'Off Peak',
            'MIS Peak Hrs',
            'AMS',
            'Parking',
            'Total KMS',
            'ODO KMs',
            'Diff',
        ];
    }

    public function map($row): array
    {
        return [
            $row['date'],
            $row['vehicle'],
            $row['akpl'],
            $row['shift'],
            $row['off_peak'],
            $row['mis_peak_hrs'],
            $row['ams'],
            $row['parking'],
            $row['total_kms'],
            $row['odo_kms'],
            $row['diff'],
        ];
    }
}
