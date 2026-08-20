<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #1a365d; padding-bottom: 20px; }
        .company-name { font-size: 22px; font-weight: bold; color: #1a365d; }
        .doc-title { font-size: 28px; font-weight: bold; color: #1a365d; text-align: right; }
        .doc-number { font-size: 14px; color: #666; text-align: right; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 5px; }
        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .info-row { display: flex; margin-bottom: 20px; }
        .info-block { width: 50%; }
        .info-label { font-size: 10px; text-transform: uppercase; color: #888; font-weight: bold; letter-spacing: 0.5px; }
        .info-value { font-size: 12px; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #1a365d; color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; margin-top: 20px; }
        .totals td { padding: 6px 12px; }
        .totals tr.total-row td { border-top: 2px solid #1a365d; font-weight: bold; font-size: 14px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #888; }
        .notes { margin-top: 20px; padding: 12px; background: #f9fafb; border-radius: 4px; }
        .notes-label { font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="company-name">VentureX ERP & CRM</div>
            <div style="font-size: 11px; color: #666; margin-top: 5px;">Universal CRM + ERP Business Operating System</div>
        </div>
        <div>
            <div class="doc-title">QUOTATION</div>
            <div class="doc-number">{{ $quotation->quotation_number }}</div>
            <div><span class="status status-{{ $quotation->status }}">{{ ucfirst($quotation->status) }}</span></div>
        </div>
    </div>

    <div class="info-row">
        <div class="info-block">
            <div class="info-label">Bill To</div>
            <div class="info-value">
                <strong>{{ $quotation->customer->name ?? 'N/A' }}</strong><br>
                {{ $quotation->customer->email ?? '' }}<br>
                {{ $quotation->customer->phone ?? '' }}<br>
                {{ $quotation->customer->address ?? '' }}
            </div>
        </div>
        <div class="info-block" style="text-align: right;">
            <div class="info-label">Details</div>
            <div class="info-value">
                <strong>Date:</strong> {{ $quotation->created_at->format('d M Y') }}<br>
                @if($quotation->valid_until)
                    <strong>Valid Until:</strong> {{ $quotation->valid_until->format('d M Y') }}<br>
                @endif
                <strong>Currency:</strong> {{ $quotation->currency_code ?? 'USD' }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->description }}@if($item->product)<br><small style="color:#888">SKU: {{ $item->product->sku ?? '' }}</small>@endif</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->discount, 2) }}</td>
                <td class="text-right">{{ number_format($item->tax_rate, 1) }}%</td>
                <td class="text-right">{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ number_format($quotation->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="text-right">-{{ number_format($quotation->discount, 2) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="text-right">{{ number_format($quotation->tax, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Total</td>
            <td class="text-right">{{ number_format($quotation->total, 2) }}</td>
        </tr>
    </table>

    @if($quotation->notes)
    <div class="notes">
        <div class="notes-label">Notes</div>
        <div>{{ $quotation->notes }}</div>
    </div>
    @endif

    @if($quotation->terms)
    <div class="notes" style="margin-top: 10px;">
        <div class="notes-label">Terms & Conditions</div>
        <div>{{ $quotation->terms }}</div>
    </div>
    @endif

    <div class="footer">
        Generated by VentureX ERP & CRM &mdash; {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
