<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class TrackingReportsExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison
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
            'Total Km in a Day',
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
            $this->numeric($row['off_peak'] ?? 0),
            $this->numeric($row['mis_peak_hrs'] ?? 0),
            $this->numeric($row['ams'] ?? 0),
            $this->numeric($row['parking'] ?? 0),
            $this->numeric($row['total_kms'] ?? 0),
            $this->numeric($row['odo_kms'] ?? 0),
            $this->numeric($row['diff'] ?? 0),
        ];
    }

    private function numeric(mixed $value): float
    {
        return ($value === null || $value === '') ? 0.0 : (float) $value;
    }
}
