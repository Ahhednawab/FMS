<?php

namespace App\Http\Controllers;

use App\Models\DailyMileageReport;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TrackingController extends Controller
{
    private const TRACKING_ENDPOINT = 'http://125.209.111.151/keapi/TrackingServices.asmx';

    private const TRACKING_SOAP_ACTION = 'http://tempuri.org/MIS_AMS';

    private const TRACKING_API_KEY = 'wo6Iqo1206nPfcZ1bSML6GVXTyuCVu';

    public function index(Request $request)
    {
        $reportDate = $this->resolveReportDate($request->query('report_date'));
        $selectedVehicles = collect((array) $request->query('vehicle_no', []))
            ->map(fn ($vehicleNo) => $this->normalizeVehicleNo((string) $vehicleNo))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $vehicles = Vehicle::query()
            ->where('is_active', 1)
            ->orderBy('vehicle_no')
            ->get(['vehicle_no']);

        $trackingData = [];
        $apiError = null;

        try {
            $trackingData = $this->buildTrackingReport($this->fetchTrackingData($reportDate), $reportDate);

            if (! empty($selectedVehicles)) {
                $trackingData = collect($trackingData)
                    ->filter(fn (array $row) => in_array($row['vehicle_filter_key'], $selectedVehicles, true))
                    ->values()
                    ->all();
            }
        } catch (Throwable $exception) {
            $apiError = 'Unable to load tracking data right now.';
            Log::error('Tracking data API request failed.', [
                'report_date' => $reportDate,
                'message' => $exception->getMessage(),
            ]);
        }

        return view('admin.trackingData.index', [
            'reportDate' => $reportDate,
            'trackingData' => $trackingData,
            'vehicles' => $vehicles,
            'selectedVehicles' => $selectedVehicles,
            'apiError' => $apiError,
        ]);
    }

    private function resolveReportDate(?string $reportDate): string
    {
        try {
            return Carbon::parse($reportDate ?: now()->toDateString())->format('Y-m-d');
        } catch (Throwable) {
            return now()->toDateString();
        }
    }

    private function fetchTrackingData(string $reportDate): array
    {

        $response = $this->sendSoapRequest($reportDate);

        if ($response === '') {
            return [];
        }

        Log::info('Tracking API raw response received.', [
            'report_date' => $reportDate,
            'body_preview' => mb_substr($response, 0, 500),
        ]);

        $leadingJson = $this->extractLeadingJson($response);
        if ($leadingJson !== null) {
            if (($leadingJson['ResponseStatus'] ?? null) === 'No data found.') {
                return [];
            }

            if ($this->isListOfTrackingRows($leadingJson)) {
                return $this->normalizeTrackingRows($leadingJson);
            }
        }

        if ($this->looksLikeJson($response)) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (($decoded['ResponseStatus'] ?? null) === 'No data found.') {
                    return [];
                }

                return $this->normalizeTrackingRows(is_array($decoded) ? $decoded : []);
            }
        }

        return $this->normalizeTrackingRows($this->extractJsonFromSoap($response));
    }

    private function sendSoapRequest(string $reportDate): string
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::TRACKING_ENDPOINT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "'.self::TRACKING_SOAP_ACTION.'"',
            ],
            CURLOPT_POSTFIELDS => $this->buildSoapEnvelope($reportDate),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            throw new \RuntimeException('cURL error: '.$curlError);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException('Tracking API returned HTTP '.$httpCode);
        }

        return trim((string) $response);
    }

    private function buildSoapEnvelope(string $reportDate): string
    {
        $dateTime = Carbon::parse($reportDate)->format('Y-m-d\T00:00:00');
        $apiKey = self::TRACKING_API_KEY;

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <MIS_AMS xmlns="http://tempuri.org/">
      <_date>{$dateTime}</_date>
      <apikey>{$apiKey}</apikey>
    </MIS_AMS>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private function extractJsonFromSoap(string $response): array
    {
        $xmlResponse = $this->extractXmlPayload($response);
        if ($xmlResponse === '') {
            return [];
        }

        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($xmlResponse);
        if (! $xml) {
            throw new \RuntimeException('Invalid XML response received from tracking API.');
        }

        $namespaces = $xml->getNamespaces(true);
        $soapNamespace = $namespaces['soap'] ?? $namespaces['SOAP-ENV'] ?? 'http://schemas.xmlsoap.org/soap/envelope/';
        $xml->registerXPathNamespace('soap', $soapNamespace);
        $xml->registerXPathNamespace('t', 'http://tempuri.org/');

        $resultNodes = $xml->xpath('//soap:Body/t:MIS_AMSResponse/t:MIS_AMSResult');
        $jsonPayload = trim((string) ($resultNodes[0] ?? ''));

        if ($jsonPayload === '') {
            return [];
        }

        $decoded = json_decode($jsonPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON found inside SOAP response.');
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function extractLeadingJson(string $response): ?array
    {
        $trimmed = ltrim($response);
        if ($trimmed === '' || ($trimmed[0] !== '{' && $trimmed[0] !== '[')) {
            return null;
        }

        $xmlStart = strpos($trimmed, '<?xml');
        $jsonPart = $xmlStart === false ? $trimmed : substr($trimmed, 0, $xmlStart);
        $jsonPart = trim($jsonPart);

        if ($jsonPart === '') {
            return null;
        }

        $decoded = json_decode($jsonPart, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
    }

    private function extractXmlPayload(string $response): string
    {
        $xmlStart = strpos($response, '<?xml');

        return $xmlStart === false ? '' : trim(substr($response, $xmlStart));
    }

    private function looksLikeJson(string $response): bool
    {
        $trimmed = ltrim($response);

        return $trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[');
    }

    private function isListOfTrackingRows(array $decoded): bool
    {
        return array_is_list($decoded);
    }

    private function normalizeTrackingRows(array $rows): array
    {
        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) {
                return [
                    'RegNo' => (string) ($row['RegNo'] ?? ''),
                    'PeakKMs' => $this->normalizeApiValue($row['PeakKMs'] ?? 0),
                    'OffPeakKMs' => $this->normalizeApiValue($row['OffPeakKMs'] ?? 0),
                    'AMSKMs' => $this->normalizeApiValue($row['AMSKMs'] ?? 0),
                ];
            })
            ->filter(fn (array $row) => $row['RegNo'] !== '')
            ->values()
            ->all();
    }

    private function buildTrackingReport(array $apiRows, string $reportDate): array
    {
        $apiCollection = collect($apiRows);
        $vehicleNumbers = $apiCollection
            ->pluck('RegNo')
            ->map(fn ($vehicleNo) => $this->normalizeVehicleNo((string) $vehicleNo))
            ->filter()
            ->unique()
            ->values();

        $vehicles = Vehicle::query()
            ->with(['shiftTiming', 'shiftHours'])
            ->whereIn('vehicle_no', $vehicleNumbers->all())
            ->get()
            ->keyBy(fn (Vehicle $vehicle) => $this->normalizeVehicleNo((string) $vehicle->vehicle_no));

        $dailyMileageReports = DailyMileageReport::query()
            ->whereDate('report_date', $reportDate)
            ->whereIn('vehicle_id', $vehicles->pluck('id')->filter()->all())
            ->get()
            ->keyBy('vehicle_id');

        return $apiCollection
            ->map(function (array $row) use ($reportDate, $vehicles, $dailyMileageReports) {
                $vehicleNo = $this->normalizeVehicleNo((string) ($row['RegNo'] ?? ''));
                $vehicle = $vehicles->get($vehicleNo);
                $dailyMileage = $vehicle ? $dailyMileageReports->get($vehicle->id) : null;

                $offPeak = $this->toFloat($row['OffPeakKMs'] ?? 0);
                $ams = $this->toFloat($row['AMSKMs'] ?? 0);
                $totalKms = $this->toFloat($row['PeakKMs'] ?? 0);
                $odoKms = $this->toFloat(optional($dailyMileage)->mileage);
                $parking = $this->toFloat(optional($vehicle)->parking_km);

                return [
                    'date' => $reportDate,
                    'vehicle' => (string) ($row['RegNo'] ?? ''),
                    'vehicle_filter_key' => $vehicleNo,
                    'akpl' => $vehicle?->akpl ?: 'N/A',
                    'shift' => $this->resolveShiftHoursLabel($vehicle),
                    'off_peak' => $offPeak,
                    'mis_peak_hrs' => $ams,
                    'ams' => $ams,
                    'parking' => $parking,
                    'total_kms' => $totalKms,
                    'odo_kms' => $odoKms,
                    'diff' => $totalKms - $odoKms,
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeVehicleNo(string $vehicleNo): string
    {
        return strtoupper(trim($vehicleNo));
    }

    private function resolveShiftHoursLabel(?Vehicle $vehicle): string
    {
        if (! $vehicle) {
            return 'N/A';
        }

        $hours = $vehicle->shiftHours?->hours;
        if ($hours !== null && $hours !== '') {
            return rtrim(rtrim((string) $hours, '0'), '.').' Hr';
        }

        return $vehicle->shiftHours?->name
            ?: $vehicle->shiftTiming?->name
            ?: 'N/A';
    }

    private function toFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }

    private function normalizeApiValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return (string) $value;
    }
}
