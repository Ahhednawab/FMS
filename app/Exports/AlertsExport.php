<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel export of one alert type. The rows come from AlertCenterService, so the
 * sheet holds exactly what the Alerts page (or the dashboard panel it was
 * started from) is showing, with the same filters and without the duplicate
 * copies the daily notification job leaves behind.
 */
class AlertsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithStrictNullComparison
{
    public function __construct(
        private Collection $rows,
        private string $typeLabel,
        private string $referenceLabel = 'Vehicle'
    ) {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Alert', 'Message', $this->referenceLabel, 'Date / Reading'];
    }

    public function map($row): array
    {
        return [
            $row['title'],
            $row['message'],
            $row['reference'],
            $row['detail'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        // Messages are long sentences: give them a fixed, wrapped column.
        $sheet->getColumnDimension('B')->setAutoSize(false)->setWidth(80);
        $sheet->getStyle('B')->getAlignment()->setWrapText(true);

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        // Excel sheet names cannot be longer than 31 characters.
        return mb_substr($this->typeLabel, 0, 31);
    }
}
