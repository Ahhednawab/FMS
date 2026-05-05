<?php

namespace App\Services;

class TrackingService
{
    public function fetch(string $date = '2024-01-01'): array
    {
        $endpoint = 'http://125.209.111.151/keapi/TrackingServices.asmx';

        $apiKey = 'wo6Iqo1206nPfcZ1bSML6GVXTyuCVu';

        $soapDate = $date . 'T00:00:00';

        $xml = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <MIS_AMS xmlns="http://tempuri.org/">
      <_date>' . $soapDate . '</_date>
      <apikey>' . $apiKey . '</apikey>
    </MIS_AMS>
  </soap:Body>
</soap:Envelope>';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://tempuri.org/MIS_AMS"',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return [];
        }

        return $this->extractJsonFromSoap($response);
    }

    private function extractJsonFromSoap(string $response): array
    {
        // SOAP XML parse
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($response);

        if (!$xml) {
            return [];
        }

        $json = $xml
            ->children('soap', true)
            ->Body
            ->children()
            ->MIS_AMSResponse
            ->MIS_AMSResult ?? null;

        if (!$json) {
            return [];
        }

        $data = json_decode((string) $json, true);

        return $data ?? [];
    }
}