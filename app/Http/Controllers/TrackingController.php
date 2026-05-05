<?php

namespace App\Http\Controllers;

use App\Models\DailyMileageReport;
use App\Services\TrackingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;

class TrackingController extends Controller
{
    public function __construct(private readonly TrackingService $trackingService) {}

    public function index(Request $request)
    {
        $reportDate = $request->query('report_date', now()->format('Y-m-d'));

        try {
            $reportDate = Carbon::parse($reportDate)->format('Y-m-d');
        } catch (Throwable) {
            $reportDate = now()->format('Y-m-d');
        }

        $apiRows = collect($this->trackingService->fetch($reportDate))
            ->filter(fn ($row) => is_array($row) && ! empty($row['RegNo']))
            ->keyBy(fn (array $row) => $this->normalizeVehicleNo($row['RegNo']));

        $trackingData = $this->buildMergedRows($apiRows, $reportDate);

        return view('admin.trackingData.index', [
            'reportDate' => $reportDate,
            'trackingData' => $trackingData,
            'totals' => [
                'peak' => $trackingData->sum('peak_kms_api'),
                'off_peak' => $trackingData->sum('off_peak_kms_api'),
                'ams' => $trackingData->sum('ams_kms_api'),
                'parking' => $trackingData->sum('parking'),
                'total_kms' => $trackingData->sum('total_kms'),
                'odo_kms' => $trackingData->sum('odo_kms'),
                'diff' => $trackingData->sum('diff'),
            ],
        ]);
    }

    protected function buildMergedRows(Collection $apiRows, string $reportDate): Collection
    {
        return DailyMileageReport::query()
            ->with(['vehicle.shiftTiming'])
            ->whereDate('report_date', $reportDate)
            ->where('is_active', 1)
            ->whereHas('vehicle', fn ($query) => $query->where('is_active', 1))
            ->orderBy('report_date')
            ->orderBy('vehicle_id')
            ->get()
            ->map(function (DailyMileageReport $dailyMileage) use ($apiRows, $reportDate) {
                $vehicle = $dailyMileage->vehicle;
                $vehicleNo = (string) ($vehicle?->vehicle_no ?? '');
                $apiRow = $apiRows->get($this->normalizeVehicleNo($vehicleNo), []);

                $peak = $this->toFloat($apiRow['PeakKMs'] ?? 0);
                $offPeak = $this->toFloat($apiRow['OffPeakKMs'] ?? 0);
                $ams = $this->toFloat($apiRow['AMSKMs'] ?? 0);
                $parking = 0.0;
                $totalKms = $peak + $offPeak + $ams + $parking;
                $odoKms = $this->toFloat($dailyMileage->current_km);

                return [
                    'date' => Carbon::parse($dailyMileage->report_date)->format('Y-m-d'),
                    'veh_reg' => $vehicleNo,
                    'peak_kms_api' => $peak,
                    'off_peak_kms_api' => $offPeak,
                    'ams_kms_api' => $ams,
                    'akpl' => $vehicle?->akpl ?: 'N/A',
                    'shift' => $vehicle?->shiftTiming?->name ?: 'N/A',
                    'parking' => $parking,
                    'mis_peak_hes' => $peak,
                    'total_kms' => $totalKms,
                    'odo_kms' => $odoKms,
                    'diff' => $odoKms - $totalKms,
                    'matched_with_api' => ! empty($apiRow),
                ];
            })
            ->values();
    }

    protected function normalizeVehicleNo(string $vehicleNo): string
    {
        return strtoupper(trim($vehicleNo));
    }

    protected function toFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }
}
