<?php

namespace App\Exports;

use App\Models\AccidentDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Server-side Excel export for the Accident Details list.
 *
 * Applies the same filters as the list screen (search + payment status +
 * active records only), so the sheet always contains the full filtered
 * dataset — not just the rows visible on the current page.
 */
class AccidentDetailsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithColumnFormatting, WithStrictNullComparison
{
    public function __construct(private Request $request)
    {
    }

    public function collection()
    {
        $query = AccidentDetail::query()->where('is_active', 1);

        $status = $this->request->query('payment_status', 'all');
        if ($status && $status !== 'all') {
            $query->where('payment_status', $status);
        }

        $search = trim((string) $this->request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('accident_id', 'like', "%{$search}%")
                    ->orWhere('vehicle_no', 'like', "%{$search}%")
                    ->orWhere('workshop', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Accident ID',
            'Accident Date',
            'Vehicle No',
            'Insurance',
            'Policy No',
            'Loss No',
            'Driver Name',
            'Workshop',
            'Claim Amount',
            'Depreciation Amount',
            'Payment Status',
            'Bill to KE',
            'Remarks',
        ];
    }

    public function map($row): array
    {
        return [
            $row->accident_id,
            $row->accident_date ? \Carbon\Carbon::parse($row->accident_date)->format('d-m-Y') : '',
            $row->vehicle_no,
            $row->insurance ?? '',
            $row->policy_no ?? '',
            $row->loss_no ?? '',
            $row->driver_name ?? '',
            $row->workshop ?? '',
            (float) ($row->claim_amount ?? 0),
            (float) ($row->depreciation_amount ?? 0),
            ucfirst((string) $row->payment_status),
            $row->bill_to_ke ? 'Yes' : 'No',
            $row->remarks ?? '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Claim
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Depreciation
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Accident Details';
    }
}
