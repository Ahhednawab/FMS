@php
    use App\Http\Controllers\InvoiceController;
    use App\Services\AmountInWords;

    /*
    |--------------------------------------------------------------------------
    | Printed invoice (designed to go on top of the company letterhead)
    |--------------------------------------------------------------------------
    | The blocks below are the only fixed text on this page. If the company
    | details, the row label or the closing note ever change, edit them here.
    */
    $companyName = 'FORMULAONE';
    $companyLines = [
        '302-A, Sea Breeze Plaza, Shahrah-e-',
        'Faisal, Karachi.',
        '',
        'NTN 1543919-4',
        'SST  S1543919-4',
        'IBAN Number PK75BAHL1100098100519901',
    ];

    $clientName = 'K - Electric';
    $clientLines = [
        'Accounts Payable',
        'KESC House 39-B, Sunset Boulevard, Phase 4,',
        'Karachi.',
        'NTN 1543137-1',
        'SST  S1543137-1',
        'PO Number ' . ($invoice->po_no ?: '-'),
    ];

    $region = 'Karachi Region';
    $vehicleRowLabel = 'MTL Vehicles';
    $serviceDescription = [
        'Transport Services (MTL vehicles) at K-Electric',
        'Karachi as per PO & attendance attached',
    ];

    // Kept in step with the tax engine so the note can never drift from the rate
    // actually charged on the invoice.
    $salesTaxPercent = rtrim(rtrim(number_format(InvoiceController::SALES_TAX_RATE * 100, 2, '.', ''), '0'), '.');
    $withholdingPercent = rtrim(rtrim(number_format(InvoiceController::WITHHOLDING_RATE * 100, 2, '.', ''), '0'), '.');

    // Amounts print with thousand separators, and decimals only when there are any.
    $money = function ($value) {
        $value = (float) $value;
        return number_format($value, fmod($value, 1) == 0.0 ? 0 : 2);
    };
    // Days / Qty print exactly as entered, without pointless trailing zeros.
    $plain = function ($value) {
        if ($value === null || $value === '') {
            return '';
        }
        $number = (float) $value;
        return fmod($number, 1) == 0.0 ? (string) (int) $number : rtrim(rtrim((string) $number, '0'), '.');
    };

    $toArray = fn ($value) => is_array($value) ? $value : (($value === null || $value === '') ? [] : [$value]);
    $qtyList = $toArray($invoice->vehicle_qty);
    $daysList = $toArray($invoice->days);
    $rentList = $toArray($invoice->vehicle_rent);
    $monthlyList = $toArray($invoice->monthly_rent);

    $rows = [];
    foreach ($qtyList as $i => $qty) {
        $amount = (float) ($monthlyList[$i] ?? 0);
        if ((float) $qty == 0.0 && $amount == 0.0) {
            continue; // empty row on the form
        }
        $rows[] = [
            'description' => $vehicleRowLabel,
            'days' => $daysList[$i] ?? null,
            'qty' => $qty,
            'rate' => $rentList[$i] ?? null,
            'amount' => $amount,
        ];
    }

    if ((float) $invoice->sunday_gazette != 0.0) {
        $rows[] = ['description' => 'Sunday / Gazetted', 'days' => null, 'qty' => null, 'rate' => null, 'amount' => $invoice->sunday_gazette];
    }

    if ((float) $invoice->control_room_charges != 0.0) {
        $rows[] = ['description' => 'Control Room Charges', 'days' => null, 'qty' => null, 'rate' => null, 'amount' => $invoice->control_room_charges];
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        :root {
            /* Both are physical measurements, so the toolbar can adjust them */
            --letterhead-space: 40mm;
            --side-margin: 14mm;
            --printer-margin: 8mm;
        }

        body {
            margin: 0;
            padding: 0 0 24px;
            background: #e9e9e9;
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.35;
            color: #000;
        }

        /* On screen this mimics a full A4 sheet: the printer margin and the side
           margin together give exactly the printed content width. */
        .sheet {
            width: 100%;
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 0 calc(var(--printer-margin) + var(--side-margin)) 20mm;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.18);
            box-sizing: border-box;
        }

        /* Blank space left for the pre-printed letterhead */
        .letterhead-space {
            height: var(--letterhead-space);
        }

        .top-line {
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .parties {
            table-layout: fixed; /* both boxes stay exactly half the width */
        }

        .parties td {
            border: 1px solid #000;
            vertical-align: top;
            padding: 7px 10px;
            width: 50%;
            font-size: 10.5pt; /* small enough for the IBAN to stay on one line */
            line-height: 1.45;
        }

        .parties .party-name {
            font-weight: bold;
        }

        .doc-title {
            text-align: center;
            margin: 20px 0 4px;
        }

        .doc-title h1 {
            font-size: 15pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 4px;
            letter-spacing: 0.3px;
        }

        .doc-title .sub {
            font-size: 10.5pt;
            font-weight: bold;
            line-height: 1.4;
        }

        .items {
            margin-top: 14px;
            table-layout: fixed;
        }

        .items th,
        .items td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 11.5pt;
        }

        .items th {
            text-align: center;
            font-weight: bold;
            white-space: nowrap;
        }

        .items td {
            text-align: center;
        }

        .items td.description {
            font-weight: bold;
        }

        .items td.amount {
            text-align: right;
        }

        /* Summary rows start under Description, like the sample invoice */
        .items td.spacer {
            border: 0;
        }

        .items td.summary-label {
            font-weight: bold;
        }

        .footer-block {
            margin-top: 32px;
        }

        .footer-block td {
            vertical-align: top;
            line-height: 1.45;
        }

        .underline-head {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 6px;
        }

        .total-amount-box {
            border: 1px solid #000;
            padding: 5px 14px;
            text-align: center;
            font-weight: bold;
            min-width: 120px;
            display: inline-block;
        }

        .amount-words {
            font-weight: bold;
            margin-top: 26px;
            line-height: 1.5;
        }

        .sro-note {
            margin-top: 30px;
            font-size: 10.5pt;
            line-height: 1.55;
        }

        /* Screen-only controls */
        .toolbar {
            width: 100%;
            max-width: 210mm;
            margin: 16px auto 12px;
            padding: 10px 14px;
            box-sizing: border-box;
            background: #fff;
            border: 1px solid #d5d5d5;
            border-radius: 4px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #333;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px 18px;
        }

        .toolbar button,
        .toolbar a {
            font: inherit;
            padding: 6px 14px;
            border: 1px solid #bbb;
            border-radius: 3px;
            background: #fff;
            color: #333;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar button.primary {
            background: #2196f3;
            border-color: #2196f3;
            color: #fff;
        }

        .toolbar input {
            width: 64px;
            font: inherit;
            padding: 5px;
            border: 1px solid #bbb;
            border-radius: 3px;
        }

        .toolbar .hint {
            color: #777;
            flex-basis: 100%;
            margin: 0;
        }

        @media print {
            @page {
                size: A4;
                margin: var(--printer-margin);
            }

            body {
                background: #fff;
                padding: 0;
            }

            .sheet {
                width: auto;
                max-width: none;
                min-height: 0;
                margin: 0;
                padding: 0 var(--side-margin);
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

            /* Keep rows whole and repeat the header if an invoice runs long */
            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="toolbar no-print">
    <button type="button" class="primary" onclick="window.print()">Print</button>
    <label>
        Letterhead space:
        <input type="number" id="letterheadSpace" min="0" max="120" step="1" value="40"> mm
    </label>
    <label>
        Side margin:
        <input type="number" id="sideMargin" min="0" max="40" step="1" value="14"> mm
    </label>
    <a href="{{ route('invoices.show', $invoice->id) }}">Back</a>
    <p class="hint">
        Letterhead par ek test print nikal kar ye dono settings apne hisab se set karein — browser inhein yaad rakhega.
    </p>
</div>

<div class="sheet">
    <div class="letterhead-space" id="letterheadSpacer"></div>

    <div class="top-line">{{ optional($invoice->invoice_date)->format('M, d Y') }}</div>
    <div class="top-line" style="margin-bottom: 18px;">Inv. No. {{ $invoice->invoice_no }}</div>

    <table class="parties">
        <tr>
            <td>
                <div class="party-name">{{ $companyName }}</div>
                @foreach ($companyLines as $line)
                    <div>{!! $line === '' ? '&nbsp;' : e($line) !!}</div>
                @endforeach
            </td>
            <td>
                <div class="party-name">{{ $clientName }}</div>
                @foreach ($clientLines as $line)
                    <div>{!! $line === '' ? '&nbsp;' : e($line) !!}</div>
                @endforeach
            </td>
        </tr>
    </table>

    <div class="doc-title">
        <h1>SALES TAX INVOICE</h1>
        <div class="sub">For The Month Of {{ optional($invoice->invoice_month)->format('M-Y') }}</div>
        <div class="sub">{{ $region }}</div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 8%;">S. No</th>
                <th style="width: 36%;">Description</th>
                <th style="width: 10%;">Days</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 18%;">Rate / Month</th>
                <th style="width: 18%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="description">{{ $row['description'] }}</td>
                    <td>{{ $plain($row['days']) }}</td>
                    <td>{{ $plain($row['qty']) }}</td>
                    <td>{{ $row['rate'] === null ? '' : $money($row['rate']) }}</td>
                    <td class="amount">{{ $money($row['amount']) }}</td>
                </tr>
            @endforeach

            <tr>
                <td class="spacer"></td>
                <td class="summary-label">Total</td>
                <td colspan="3"></td>
                <td class="amount">{{ $money($invoice->total_claim) }}</td>
            </tr>
            <tr>
                <td class="spacer"></td>
                <td class="summary-label">Sindh Sales Tax @ {{ $salesTaxPercent }}%</td>
                <td colspan="3"></td>
                <td class="amount">{{ $money($invoice->sales_tax) }}</td>
            </tr>
            <tr>
                <td class="spacer"></td>
                <td class="summary-label">Including Sales Tax Value</td>
                <td colspan="3"></td>
                <td class="amount">{{ $money($invoice->inclusive_sales_tax) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="footer-block">
        <tr>
            <td>
                <div class="underline-head">Description</div>
                @foreach ($serviceDescription as $line)
                    <div>{{ $line }}</div>
                @endforeach
            </td>
            <td style="text-align: right; width: 32%;">
                <div class="underline-head">Total Amount</div>
                <div class="total-amount-box">{{ $money($invoice->inclusive_sales_tax) }}</div>
            </td>
        </tr>
    </table>

    <div class="amount-words">
        ({{ AmountInWords::rupees($invoice->inclusive_sales_tax) }})
    </div>

    <div class="sro-note">
        As per SROSRB-3-4/14/2014 dated 01/07/2014, kindly deduct over {{ $withholdingPercent }}%<br>
        of {{ $salesTaxPercent }}% Tax amount mentioned on the invoice.
    </div>
</div>

<script>
    // Letterhead gap and side margin are physical measurements, so they are
    // adjustable here and remembered in this browser for the next print.
    (function () {
        var settings = [
            { input: 'letterheadSpace', variable: '--letterhead-space', store: 'invoiceLetterheadSpace', fallback: 40 },
            { input: 'sideMargin', variable: '--side-margin', store: 'invoiceSideMargin', fallback: 14 }
        ];

        settings.forEach(function (setting) {
            var input = document.getElementById(setting.input);
            var saved = null;

            try {
                saved = localStorage.getItem(setting.store);
            } catch (e) {
                saved = null;
            }

            if (saved !== null && saved !== '') {
                input.value = saved;
            }

            function apply() {
                var mm = parseFloat(input.value);
                if (isNaN(mm) || mm < 0) {
                    mm = setting.fallback;
                }
                document.documentElement.style.setProperty(setting.variable, mm + 'mm');
                try {
                    localStorage.setItem(setting.store, String(mm));
                } catch (e) {
                    // storage blocked: the setting still applies to this print
                }
            }

            input.addEventListener('input', apply);
            apply();
        });
    })();
</script>

</body>
</html>
