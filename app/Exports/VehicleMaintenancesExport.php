<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Server-side Excel export for the Vehicle Maintenance list.
 *
 * The controller hands over the list's own filtered query, so the sheet always
 * matches the filters on screen and contains every matching record — not only
 * the 25 shown on one page.
 */
class VehicleMaintenancesExport implements FromQuery, WithMapping, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithColumnFormatting, WithStrictNullComparison
{
    public function __construct(
        private Builder $query,
        private array $maintenanceTypes
    ) {
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Maintenance ID',
            'Date',
            'Vehicle No',
            'Make',
            'Model',
            'Type',
            'Work Done',
            'Warehouse',
            'Workshop',
            'Parts Used',
            'Amount (Rs.)',
            'Created By',
        ];
    }

    public function map($maintenance): array
    {
        // Warehouses are per product row, so list the distinct ones used
        // (same as the list screen).
        $warehouses = $maintenance->maintenanceParts
            ->map(fn ($part) => $part->warehouse?->name)
            ->filter()
            ->unique()
            ->values();

        $parts = $maintenance->maintenanceParts
            ->groupBy('product_id')
            ->map(function ($rows) {
                $product = $rows->first()->product;
                $unit = $product?->unit?->name;

                return ($product?->name ?? 'N/A') . ' (' . ($rows->sum('quantity') + 0) . ($unit ? ' ' . $unit : '') . ')';
            })
            ->values();

        return [
            $maintenance->maintenance_id,
            $maintenance->service_date ? ExcelDate::dateTimeToExcel($maintenance->service_date) : null,
            $maintenance->vehicle?->vehicle_no ?? 'N/A',
            $maintenance->vehicle_make ?? 'N/A',
            $maintenance->model ?? 'N/A',
            $this->maintenanceTypes[$maintenance->maintenance_type] ?? $maintenance->maintenance_type,
            implode(', ', $maintenance->workDoneNames()) ?: 'N/A',
            $warehouses->isNotEmpty() ? $warehouses->implode(', ') : ($maintenance->warehouse?->name ?? 'N/A'),
            $maintenance->workshop?->name ?? 'N/A',
            $parts->isNotEmpty() ? $parts->implode(', ') : 'N/A',
            (float) $maintenance->service_cost,
            $maintenance->createdBy?->name ?? 'N/A',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => 'dd mmm yyyy', // Date
            'K' => '#,##0.00',    // Amount
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

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
        return 'Vehicle Maintenance';
    }
}
