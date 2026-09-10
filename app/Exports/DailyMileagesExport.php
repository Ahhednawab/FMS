<?php

namespace App\Exports;

use App\Models\DailyMileageReport;
use App\Models\Vehicle;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Server-side Excel export for the Daily Mileage list.
 *
 * Applies the same filters as the list screen and always exports the full
 * filtered dataset (not just the visible page). When a date filter is applied
 * (up to {@see MAX_FILL_DAYS} days), vehicles that have NO mileage entry on a
 * date in the range are also included with Mileage = 0 and status
 * "Not Recorded", so the sheet accounts for the whole fleet per day.
 */
class DailyMileagesExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithStrictNullComparison
{
    /** Longest date range for which missing vehicles are zero-filled per day. */
    public const MAX_FILL_DAYS = 31;

    private $fleetCache = null;

    public function __construct(private Request $request)
    {
    }

    public function collection()
    {
        $vehicleIds = array_filter((array) $this->request->input('vehicle_id', []));
        $search = trim((string) $this->request->input('search', ''));
        $fromDate = $this->request->input('from_date');
        $toDate = $this->request->input('to_date');

        // ---- Recorded entries, filtered exactly like the list screen ----
        $query = DailyMileageReport::query()
            ->where('is_active', 1)
            ->whereHas('vehicle', function ($q) {
                $q->where('is_active', 1);
            })
            ->with(['vehicle.station']);

        if (!empty($vehicleIds)) {
            $query->whereIn('vehicle_id', $vehicleIds);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('current_km', 'like', "%{$search}%")
                    ->orWhere('previous_km', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($v) use ($search) {
                        $v->where('vehicle_no', 'like', "%{$search}%");
                    });
            });
        }

        if ($fromDate) {
            $query->whereDate('report_date', '>=', Carbon::parse($fromDate));
        }
        if ($toDate) {
            $query->whereDate('report_date', '<=', Carbon::parse($toDate));
        }

        $rows = [];
        $recordedByDate = [];

        foreach ($query->get() as $record) {
            $dateKey = Carbon::parse($record->report_date)->toDateString();
            $recordedByDate[$dateKey][$record->vehicle_id] = true;

            $rows[] = [
                'sort_date' => $dateKey,
                'vehicle_no' => $record->vehicle->vehicle_no,
                'station' => $record->vehicle->station->area ?? 'N/A',
                'report_date' => Carbon::parse($record->report_date)->format('d-M-Y'),
                'previous_km' => $record->previous_km,
                'current_km' => $record->current_km,
                'mileage' => $record->mileage,
                'status' => 'Recorded',
            ];
        }

        // ---- Zero rows for vehicles with no entry on each filtered date ----
        foreach ($this->fillDates($fromDate, $toDate) as $date) {
            $dateKey = $date->toDateString();
            $dateLabel = $date->format('d-M-Y');

            foreach ($this->fleet($vehicleIds, $search) as $vehicle) {
                if (isset($recordedByDate[$dateKey][$vehicle->id])) {
                    continue;
                }

                $rows[] = [
                    'sort_date' => $dateKey,
                    'vehicle_no' => $vehicle->vehicle_no,
                    'station' => $vehicle->station->area ?? 'N/A',
                    'report_date' => $dateLabel,
                    'previous_km' => '',
                    'current_km' => '',
                    'mileage' => 0,
                    'status' => 'Not Recorded',
                ];
            }
        }

        return collect($rows)
            ->sortBy([
                ['sort_date', 'desc'],
                ['vehicle_no', 'asc'],
            ])
            ->map(function ($row) {
                unset($row['sort_date']);

                return array_values($row);
            })
            ->values();
    }

    /**
     * Dates to zero-fill: the filtered range (From→To; From→today when To is
     * empty). No date filter, an inverted range, or a range longer than
     * MAX_FILL_DAYS exports recorded entries only.
     *
     * @return \Carbon\Carbon[]
     */
    private function fillDates($fromDate, $toDate): array
    {
        if (!$fromDate) {
            return [];
        }

        $start = Carbon::parse($fromDate)->startOfDay();
        $end = $toDate ? Carbon::parse($toDate)->startOfDay() : Carbon::today();

        if ($end->lessThan($start) || $start->diffInDays($end) >= self::MAX_FILL_DAYS) {
            return [];
        }

        return iterator_to_array(CarbonPeriod::create($start, $end));
    }

    /**
     * Active vehicles the zero-fill applies to, honouring the Vehicle No
     * filter and the vehicle-number part of the search box.
     */
    private function fleet(array $vehicleIds, string $search)
    {
        if ($this->fleetCache === null) {
            $this->fleetCache = Vehicle::with('station')
                ->where('is_active', 1)
                ->when(!empty($vehicleIds), function ($q) use ($vehicleIds) {
                    $q->whereIn('id', $vehicleIds);
                })
                ->when($search !== '', function ($q) use ($search) {
                    $q->where('vehicle_no', 'like', "%{$search}%");
                })
                ->orderBy('vehicle_no')
                ->get();
        }

        return $this->fleetCache;
    }

    public function headings(): array
    {
        return [
            'Vehicle No',
            'Station',
            'Report Date',
            'Previous Kms',
            'Current Kms',
            'Mileage',
            'Status',
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
        return 'Daily Mileage';
    }
}
