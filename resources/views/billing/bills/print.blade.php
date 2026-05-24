<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Bill {{ $bill->bill_no }}</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 24px auto; color: #222; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        .row { display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; font-size: 13px; text-align: left; }
        td.right, th.right { text-align: right; }
        .summary td { border: 0; padding: 2px 8px; }
        .totals { width: 320px; margin-left: auto; }
        .small { font-size: 11px; color: #666; }
        @media print { .noprint { display: none; } body { margin: 0; padding: 12px; } }
    </style>
</head>
<body>
    <div class="row">
        <div>
            <h1>Tax Invoice</h1>
            <div class="small">{{ $bill->bill_no }} · {{ optional($bill->bill_date)->toDateString() }}</div>
        </div>
        <div style="text-align:right">
            <div class="small">{{ optional($bill->branch)->name }}</div>
            <div class="small">{{ strtoupper($bill->bill_type) }} bill — {{ ucwords(str_replace('_',' ',$bill->status)) }}</div>
        </div>
    </div>

    <div class="row" style="margin-top:12px">
        <div>
            <strong>{{ optional($bill->patient)->patient_name }}</strong><br>
            <span class="small">MRN: {{ optional($bill->patient)->mrn }} · {{ optional($bill->patient)->mobileno }}</span>
        </div>
        <div class="small">
            Encounter: #{{ $bill->encounter_id ?? '—' }}<br>
            @if($bill->finalized_at) Finalized: {{ $bill->finalized_at }}<br> @endif
        </div>
    </div>

    <table>
        <thead><tr><th>#</th><th>Description</th><th class="right">Qty</th><th class="right">Unit</th><th class="right">Tax</th><th class="right">Total</th></tr></thead>
        <tbody>
            @foreach ($bill->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ number_format((float)$item->quantity,4) }}</td>
                    <td class="right">{{ number_format((float)$item->unit_price,2) }}</td>
                    <td class="right">{{ number_format((float)$item->tax_amount,2) }}</td>
                    <td class="right">{{ number_format((float)$item->line_total,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals summary" style="margin-top:12px">
        <tr><td>Subtotal</td><td class="right">{{ number_format((float)$bill->subtotal,2) }}</td></tr>
        <tr><td>Discount</td><td class="right">-{{ number_format((float)$bill->discount_total,2) }}</td></tr>
        <tr><td>Tax</td><td class="right">{{ number_format((float)$bill->tax_total,2) }}</td></tr>
        <tr><td><strong>Grand total</strong></td><td class="right"><strong>{{ number_format((float)$bill->grand_total,2) }}</strong></td></tr>
        <tr><td>Paid</td><td class="right">{{ number_format((float)$bill->paid_total,2) }}</td></tr>
        <tr><td><strong>Balance due</strong></td><td class="right"><strong>{{ number_format((float)$bill->balance_due,2) }}</strong></td></tr>
    </table>

    <p style="margin-top:30px" class="small">Thank you for visiting. This is a computer-generated invoice.</p>
    <div class="noprint" style="margin-top:20px">
        <button onclick="window.print()">Print</button>
    </div>
</body>
</html>
