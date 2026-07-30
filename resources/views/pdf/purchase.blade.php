{{-- resources/views/filament/invoices/invoice-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase #{{ $record->lot_number }}</title>
    <style>
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            background-color: #ffffff;
            font-size: 10pt;
            line-height: 1.5;
        }

        .frame { border: 2px solid #111111; padding: 34px 40px; }

        /* Header */
        .top-header { width: 100%; border-collapse: collapse; }
        .top-header td { vertical-align: top; }

        .main-title { font-size: 30pt; font-weight: 800; letter-spacing: 3px; color: #111111; }
        .main-subtitle {
            font-size: 8.5pt; letter-spacing: 3px; text-transform: uppercase;
            color: #9c6b1f; font-weight: 600; margin-top: 2px;
        }

        .company-logo-text { text-align: right; }
        .logo-circle {
            display: inline-block; border-radius: 50%;
            width: 118px; height: 98px; text-align: center; line-height: 78px;
            font-family: Georgia, 'Times New Roman', serif; font-size: 24pt;
            font-weight: bold; font-style: italic; color: #9c6b1f;
        }
        .logo-name {
            font-size: 8pt; font-weight: 700; letter-spacing: 2px;
            text-transform: uppercase; margin-top: 0px; color: #111111;
        }
        .logo-tagline {
            font-size: 7pt; letter-spacing: 1.5px; text-transform: uppercase;
            color: #9a9a9a; margin-top: 2px;
        }

        .header-rule-gold { border-bottom: 1.5px solid #9c6b1f; margin-top: 18px; }
        .header-rule-black { border-bottom: 1px solid #111111; margin-top: 3px; margin-bottom: 36px; }

        /* Meta */
        .meta-section { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
        .meta-section td { vertical-align: top; }
        .section-label {
            font-size: 8pt; font-weight: 700; letter-spacing: 1.5px;
            text-transform: uppercase; color: #9c6b1f; margin-bottom: 8px;
        }
        .meta-text { font-size: 9.5pt; color: #4a4a4a; line-height: 1.5; }
        .meta-text strong { color: #111111; }

        .meta-info-table { float: right; border-collapse: collapse; }
        .meta-info-table td { padding: 3px 0; font-size: 9.5pt; }
        .meta-info-table td.label {
            font-size: 8pt; font-weight: 700; letter-spacing: 1.5px;
            text-transform: uppercase; padding-right: 20px; text-align: right;
            color: #111111; white-space: nowrap;
        }
        .meta-info-table td.value { text-align: right; color: #333333; font-weight: 500; white-space: nowrap; }

        /* Items */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th {
            font-size: 7.6pt; font-weight: 700; letter-spacing: 1.2px;
            text-transform: uppercase; padding: 10px 6px;
            border-top: 1.5px solid #111111; border-bottom: 1px solid #111111;
            text-align: left; color: #111111;
        }
        .items-table th:first-child, .items-table td:first-child { padding-left: 0; }
        .items-table th:last-child, .items-table td:last-child { padding-right: 0; }
        .items-table th.text-center, .items-table td.text-center { text-align: center; }
        .items-table th.text-right, .items-table td.text-right { text-align: right; }
        .items-table td { padding: 10px 6px; font-size: 9.3pt; color: #4a4a4a; }
        .items-table td.lot-no { color: #9c6b1f; font-weight: 700; }
        .items-table td.item-desc { color: #111111; font-weight: 500; }
        .items-table tbody tr:nth-child(even) { background-color: #f8f6f2; }
        .table-divider { border-bottom: 1.5px solid #111111; margin-bottom: 15px; }

        /* Totals */
        .totals-table { float: right; border-collapse: collapse; margin-bottom: 50px; min-width: 260px; }
        .totals-table td { padding: 5px 0; font-size: 9.5pt; }
        .totals-table td.label {
            font-size: 8pt; font-weight: 700; letter-spacing: 1.5px;
            text-transform: uppercase; padding-right: 25px; text-align: right; color: #111111;
        }
        .totals-table td.value { text-align: right; font-weight: 500; color: #111111; min-width: 90px; }
        .totals-table tr.grand-total-row td {
            padding-top: 10px; padding-bottom: 6px; border-top: 1.5px solid #9c6b1f;
            font-weight: 800; font-size: 12pt; color: #9c6b1f;
        }
        .totals-table tr.grand-total-row td.label { font-size: 9pt; }

        /* Bottom */
        .bottom-section { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .bottom-section td { vertical-align: bottom; }
        .payment-info { font-size: 9pt; color: #4a4a4a; line-height: 1.6; }
        .payment-info strong { color: #111111; }
        .signature-text {
            font-family: Georgia, 'Times New Roman', serif; font-style: italic;
            font-size: 24pt; color: #111111; text-align: right;
        }
        .signature-caption {
            text-align: right; font-size: 7.5pt; letter-spacing: 1.5px;
            text-transform: uppercase; color: #9a9a9a; margin-top: 2px;
        }
        .footer-note {
            text-align: center; font-size: 7.5pt; letter-spacing: 1.5px;
            text-transform: uppercase; color: #9a9a9a; margin-top: 30px;
        }

        .totals-table tr.grand-total-row td {
    padding-top: 6px; padding-bottom: 6px; border-top: 1.5px solid #9c6b1f;
    font-weight: 800; font-size: 12pt; color: #9c6b1f;
}
.totals-table td { padding: 3px 0; font-size: 9.5pt; }
    </style>
</head>
<body>
<div class="frame">

    {{-- Header --}}
    <table class="top-header">
        <tr>
            <td>
                <div class="main-title">PURCHASE</div>
                <div class="main-subtitle">{{ $record->invoice_type ?? 'Purchase Invoice' }}</div>
            </td>
            <td class="company-logo-text">
                {{-- <div class="logo-circle">{{ Str::substr($company['name'] ?? 'Haroon and Sons', 0, 1) }}.</div> --}}
                <div class="logo-circle"><img style="height: 100%; width: 150%" src="/public/img/hs.png" alt=""></div>

                <div class="logo-name">{{ strtoupper($company['name'] ?? 'Haroon and Sons') }}</div>
                <div class="logo-tagline">{{ $company['tagline'] ?? 'Wholesale Traders' }}</div>
            </td>
        </tr>
    </table>
    <div class="header-rule-gold"></div>
    <div class="header-rule-black"></div>

    {{-- Issued To + Invoice Meta --}}
    <table class="meta-section">
        <tr>
            <td style="width: 50%;">
                <div class="section-label">SUPPLIER</div>
                <div class="meta-text">
                    <strong>{{ $record->supplier->name ?? 'Customer Name' }}</strong><br>
                    @if(!empty($record->supplier->phone)) {{ $record->supplier->phone }}<br> @endif
                    @if(!empty($record->supplier->address)) {{ $record->supplier->address }} @endif
                </div>
            </td>
            <td style="width: 50%;">
                <table class="meta-info-table">
                    <tr>
                        <td class="label">LOT NO:</td>
                        <td class="value">{{ $record->lot_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">DATE:</td>
                        <td class="value">{{ \Carbon\Carbon::parse($record->purchase_date)->format('d.m.Y') }}</td>
                    </tr>
                    @if(!empty($record->due_date))
                    <tr>
                        <td class="label">DUE DATE:</td>
                        <td class="value">{{ \Carbon\Carbon::parse($record->due_date)->format('d.m.Y') }}</td>
                    </tr>
                    @endif
                    {{-- <tr>
                        <td class="label">PAYMENT MODE:</td>
                        <td class="value">{{ strtoupper($record->payment_mode ?? 'Bank Transfer') }}</td>
                    </tr> --}}
                </table>
            </td>
        </tr>
    </table>

    {{-- Items --}}
    <table class="items-table">
        <thead>
            <tr>
                {{-- <th style="width: 10%;">LOT NO.</th> --}}
                <th style="width: 28%;">ITEM DESCRIPTION</th>
                <th style="width: 16%;">BRAND</th>
                {{-- <th style="width: 14%;" class="text-center">RATE</th> --}}
                <th style="width: 10%;" class="text-center">QTY</th>
                <th style="width: 22%;" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->lotItems as $item)
                <tr>
                    {{-- <td class="lot-no" style="width: 14%;">{{ $item->lot->lot_number ?? $item->lot_id ?? '—' }}</td> --}}
                    <td class="item-desc" style="width: 20%;">{{ $item->item ?? $item->name ?? 'Item' }}</td>
                    <td>{{ $item->brand ?? '—' }}</td>
                    {{-- <td class="text-center">{{ number_format($item->cost_price, 0) }}</td> --}}
                    <td class="text-center">{{ $item->qty_purchased }}</td>
                    <td class="text-right">{{ $currency ?? 'PKR' }}{{ number_format($item->cost_price, 0) }}</td>
                </tr>
            @endforeach
           
        </tbody>
    </table>
    {{-- Totals --}}
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="width: 40%;">
            <table class="totals-table" style="width: 100%; float: none;">
                <tr>
                    <td class="label">SUBTOTAL</td>
                    <td class="value">{{ $currency ?? '$' }}{{ number_format($record->lot_price ?? $record->items->sum('total'), 0) }}</td>
                </tr>
                @if($record->discount > 0)
                <tr>
                    <td class="label">DISCOUNT</td>
                    <td class="value">-{{ $currency ?? '$' }}{{ number_format($record->discount, 0) }}</td>
                </tr>
                @endif
                @if(!empty($record->tax_rate) && $record->tax_rate > 0)
                <tr>
                    <td class="label">TAX ({{ rtrim(rtrim(number_format($record->tax_rate, 1), '0'), '.') }}%)</td>
                    <td class="value">{{ $currency ?? '$' }}{{ number_format($record->tax_amount ?? ($record->sub_total * $record->tax_rate / 100), 0) }}</td>
                </tr>
                @endif
                {{-- <tr class="grand-total-row">
                    <td class="label">TOTAL</td>
                    <td class="value">{{ $currency ?? '$' }}{{ number_format($record->grand_total ?? $record->items->sum('total'), 0) }}</td>
                </tr> --}}
                {{-- @if($record->amount_paid > 0) --}}
                <tr>
                    <td class="label">PAID</td>
                    <td class="value">{{ $currency ?? '$' }}{{ number_format($record->amount_paid, 0) }}</td>
                </tr>
                <tr>
                    <td class="label">BALANCE DUE</td>
                    <td class="value">{{ $currency ?? '$' }}{{ number_format($record->balance_amount, 0) }}</td>
                </tr>
                {{-- @endif --}}
            </table>
        </td>
    </tr>
</table>

    <div class="table-divider"></div>

    {{-- Totals --}}
    <div style="width: 100%; overflow: hidden;">
        <table class="totals-table">
            <tr>
                <td class="label">SUBTOTAL</td>
                <td class="value">{{ $currency ?? '$' }}{{ number_format($record->sub_total, 0) }}</td>
            </tr>
            @if($record->discount > 0)
            <tr>
                <td class="label">DISCOUNT</td>
                <td class="value">-{{ $currency ?? '$' }}{{ number_format($record->discount, 0) }}</td>
            </tr>
            @endif
            @if(!empty($record->tax_rate) && $record->tax_rate > 0)
            <tr>
                <td class="label">TAX ({{ rtrim(rtrim(number_format($record->tax_rate, 1), '0'), '.') }}%)</td>
                <td class="value">{{ $currency ?? '$' }}{{ number_format($record->tax_amount ?? ($record->sub_total * $record->tax_rate / 100), 0) }}</td>
            </tr>
            @endif
            <tr class="grand-total-row">
                <td class="label">TOTAL</td>
                <td class="value">{{ $currency ?? '$' }}{{ number_format($record->grand_total, 0) }}</td>
            </tr>
            @if($record->amount_paid > 0)
            <tr>
                <td class="label">PAID</td>
                <td class="value">{{ $currency ?? '$' }}{{ number_format($record->amount_paid, 0) }}</td>
            </tr>
            <tr>
                <td class="label">BALANCE DUE</td>
                <td class="value">{{ $currency ?? '$' }}{{ number_format($record->grand_total - $record->amount_paid, 0) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div style="clear: both;"></div>

    {{-- Payment Info + Signature (matches PDF exactly) --}}
    <table class="bottom-section">
        <tr>
            <td style="width: 60%;">
                <div class="section-label">PAYMENT INFO</div>
                <div class="payment-info">
                    <strong>Account Name:</strong> {{ $company['account_name'] ?? $company['name'] ?? 'Haroon and Sons' }}<br>
                    <strong>Bank:</strong> {{ $company['bank'] ?? 'Borcele Bank' }}<br>
                    <strong>Account No.:</strong> {{ $company['account_no'] ?? '0123 4567 8901' }}
                </div>
            </td>
            <td style="width: 40%;">
                <div class="signature-text">{{ $company['signature'] ?? 'Zeeshan' }}</div>
                <div class="signature-caption">Authorized Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">Thank you for your business</div>

</div>
</body>
</html>