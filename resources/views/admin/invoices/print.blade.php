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

    // Remarks wahi chhapte hain jo invoice mein likhe hon; na likhe hon to
    // jagah khali rehti hai (koi default text nahi).
    $remarkLines = array_values(array_filter(
        array_map('trim', explode("
", (string) $invoice->description)),
        fn ($line) => $line !== ''
    ));

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
    $descriptionList = $toArray($invoice->vehicle_description);

    $rows = [];
    foreach ($qtyList as $i => $qty) {
        $amount = (float) ($monthlyList[$i] ?? 0);
        if ((float) $qty == 0.0 && $amount == 0.0) {
            continue; // empty row on the form
        }
        $rows[] = [
            'description' => trim((string) ($descriptionList[$i] ?? '')),
            'days' => $daysList[$i] ?? null,
            'qty' => $qty,
            'rate' => $rentList[$i] ?? null,
            'amount' => $amount,
        ];
    }

    // Ye teen rows hamesha chhapti hain, chahe amount 0 ho.
    foreach (['Sunday' => $invoice->sunday, 'Gazetted 12 hr' => $invoice->gazetted_12hr, 'Gazetted 24 hr' => $invoice->gazetted_24hr] as $label => $amount) {
        $rows[] = ['description' => $label, 'days' => null, 'qty' => null, 'rate' => null, 'amount' => (float) $amount];
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
            --letterhead-space: 45mm;
            --footer-space: 20mm;
            --side-margin: 14mm;
            --printer-margin: 8mm;
        }

        body {
            margin: 0;
            padding: 0 0 24px;
            background: #e9e9e9;
            font-family: "Times New Roman", Times, serif;
            font-size: 10.5pt;
            line-height: 1.25;
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

        /* Jo hissa ek page par fit karna hai (auto-fit yahan zoom lagata hai) */
        .fit-area {
            transform-origin: top left;
        }

        /* Blank space left for the pre-printed letterhead */
        .letterhead-space {
            height: var(--letterhead-space);
        }

        /* Letterhead ke neeche wale hisse ke liye jagah (screen preview) */
        .footer-space {
            height: var(--footer-space);
        }

        .top-line {
            font-weight: bold;
            margin-bottom: 6px;
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
            padding: 4px 7px;
            width: 50%;
            font-size: 9pt; /* small enough for the IBAN to stay on one line */
            line-height: 1.3;
        }

        .parties .party-name {
            font-weight: bold;
        }

        .doc-title {
            text-align: center;
            margin: 12px 0 2px;
        }

        .doc-title h1 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 4px;
            letter-spacing: 0.3px;
        }

        .doc-title .sub {
            font-size: 9pt;
            font-weight: bold;
            line-height: 1.3;
        }

        .items {
            margin-top: 8px;
            table-layout: fixed;
        }

        .items th,
        .items td {
            border: 1px solid #000;
            padding: 2px 5px;
            font-size: 9.5pt;
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
            margin-top: 16px;
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
            padding: 3px 10px;
            text-align: center;
            font-weight: bold;
            min-width: 120px;
            display: inline-block;
        }

        .amount-words {
            font-weight: bold;
            margin-top: 14px;
            line-height: 1.4;
        }

        .sro-note {
            margin-top: 16px;
            font-size: 9pt;
            line-height: 1.4;
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

        .toolbar .fit-info {
            color: #2e7d32;
            font-weight: bold;
        }

        .toolbar .hint {
            color: #777;
            flex-basis: 100%;
            margin: 0;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            /* Letterhead ki jagah ab @page margin se aati hai (printPageRules),
               taake har page par chhute — sirf pehle page par nahi. */
            .letterhead-space,
            .footer-space {
                display: none;
            }

            .sheet {
                width: auto;
                max-width: none;
                min-height: 0;
                margin: 0;
                padding: 0;
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
    <!-- @page ke andar CSS variables chalte nahi, is liye ye rule JS se likha jata hai -->
    <style id="printPageRules"></style>
</head>
<body>

<div class="toolbar no-print">
    <button type="button" class="primary" onclick="window.print()">Print</button>
    <label>
        Letterhead space:
        <input type="number" id="letterheadSpace" min="0" max="120" step="1" value="40"> mm
    </label>
    <label>
        Footer space:
        <input type="number" id="footerSpace" min="0" max="80" step="1" value="20"> mm
    </label>
    <label>
        Side margin:
        <input type="number" id="sideMargin" min="0" max="40" step="1" value="14"> mm
    </label>
    <span id="fitInfo" class="fit-info"></span>
    <a href="{{ route('invoices.show', $invoice->id) }}">Back</a>
    <p class="hint">
        Letterhead par ek test print nikal kar ye settings apne hisab se set karein — browser inhein yaad rakhega.
        Upar wali jagah letterhead ke header ke liye hai aur "Footer space" neeche wale hisse ke liye.
    </p>
</div>

<div class="sheet">
    <div class="letterhead-space" id="letterheadSpacer"></div>

    <div class="fit-area" id="fitArea">

    <div class="top-line">{{ optional($invoice->invoice_date)->format('M, d Y') }}</div>
    <div class="top-line" style="margin-bottom: 10px;">Inv. No. {{ $invoice->invoice_no }}</div>

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
                <div class="underline-head">Remarks</div>
                @foreach ($remarkLines as $line)
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
    </div><!-- /fit-area -->

    <div class="footer-space" id="footerSpacer"></div>
</div>

<script>
    // Letterhead gap and side margin are physical measurements, so they are
    // adjustable here and remembered in this browser for the next print.
    (function () {
        var settings = [
            { input: 'letterheadSpace', variable: '--letterhead-space', store: 'invoiceLetterheadSpace', fallback: 45 },
            { input: 'footerSpace', variable: '--footer-space', store: 'invoiceFooterSpace', fallback: 20 },
            { input: 'sideMargin', variable: '--side-margin', store: 'invoiceSideMargin', fallback: 14 }
        ];

        var pageRules = document.getElementById('printPageRules');

        function valueOf(setting) {
            var mm = parseFloat(document.getElementById(setting.input).value);
            return (isNaN(mm) || mm < 0) ? setting.fallback : mm;
        }

        // Har page ki margins: upar letterhead ki jagah, dono taraf side margin.
        // Ye @page rule hai, is liye doosre aur teesre page par bhi lagti hai.
        function applyPageRules() {
            var top = valueOf(settings[0]);
            var bottom = valueOf(settings[1]);
            var side = valueOf(settings[2]);

            pageRules.textContent = '@page { size: A4; margin: ' + top + 'mm ' + side + 'mm '
                + bottom + 'mm ' + side + 'mm; }';
        }

        // A4 ki lambai minus upar/neeche ki margins = ek page par bachi hui jagah.
        // Content us se bara ho to use utna chhota kar dete hain ke ek page par aa jaye.
        var MM_TO_PX = 96 / 25.4;
        var A4_HEIGHT_MM = 297;
        var MIN_SCALE = 0.6;

        function applyOnePageFit() {
            var area = document.getElementById('fitArea');
            var info = document.getElementById('fitInfo');
            if (!area) {
                return;
            }

            area.style.zoom = 1;

            var availableMm = A4_HEIGHT_MM - valueOf(settings[0]) - valueOf(settings[1]);
            var availablePx = availableMm * MM_TO_PX;
            var contentPx = area.scrollHeight;
            var scale = contentPx > availablePx ? Math.floor((availablePx / contentPx) * 100) / 100 : 1;

            if (scale < MIN_SCALE) {
                scale = MIN_SCALE;
            }

            area.style.zoom = scale;

            if (info) {
                info.textContent = scale < 1
                    ? 'Ek page par fit: ' + Math.round(scale * 100) + '%'
                    : 'Ek page par aa raha hai';
            }
        }

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
                var mm = valueOf(setting);
                input.value = mm;
                document.documentElement.style.setProperty(setting.variable, mm + 'mm');
                applyPageRules();
                applyOnePageFit();
                try {
                    localStorage.setItem(setting.store, String(mm));
                } catch (e) {
                    // storage blocked: the setting still applies to this print
                }
            }

            input.addEventListener('input', apply);
            apply();
        });

        applyOnePageFit();
        window.addEventListener('beforeprint', applyOnePageFit);
    })();
</script>

</body>
</html>
