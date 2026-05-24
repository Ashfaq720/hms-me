<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill {{ $bill->bill_no }} — PDF</title>
    <style>
        @media print { .no-print { display:none !important; } body { margin:0; } }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; padding: 24px; max-width: 800px; margin: 0 auto; }
        .actions { position: fixed; top: 12px; right: 12px; display:flex; gap:6px; }
        .btn { display:inline-block; padding:6px 12px; background:#0d6efd; color:#fff; border-radius:6px; text-decoration:none; font-size:13px; border:none; cursor:pointer; }
        .btn.secondary { background:#6c757d; }
        h1 { font-size: 22px; margin: 0 0 4px; color:#0d6efd; }
        .meta { display:flex; justify-content:space-between; border-bottom: 2px solid #0d6efd; padding-bottom: 12px; margin-bottom: 16px; }
        .meta .right { text-align:right; }
        .info-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 6px 24px; margin-bottom: 16px; padding: 10px; background:#f8f9fa; border-radius:6px; }
        .info-grid div { font-size: 12px; }
        .info-grid strong { color:#555; min-width: 110px; display:inline-block; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 8px 10px; border-bottom: 1px solid #ddd; text-align:left; font-size:12px; }
        th { background:#f1f3f5; font-weight:600; }
        td.r, th.r { text-align: right; }
        td.c, th.c { text-align: center; }
        .totals { width: 320px; margin-left: auto; }
        .totals td { border-bottom: none; padding: 4px 10px; }
        .totals .grand { font-weight:700; font-size: 15px; border-top: 2px solid #333; }
        .footer { margin-top: 32px; display: flex; justify-content: space-between; border-top: 1px solid #ddd; padding-top: 16px; font-size: 11px; color: #888; }
        .footer .sig { display: inline-block; min-width: 180px; border-top: 1px solid #333; padding-top: 4px; text-align: center; }
        .status-paid { color: #198754; font-weight:bold; }
        .status-due  { color: #dc3545; font-weight:bold; }
        .badge { display:inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; background: #e7f1ff; color: #0d6efd; }
    </style>
</head>
<body>
    <div class="actions no-print">
        <button class="btn" onclick="window.print()">🖨 Save as PDF</button>
        <a class="btn secondary" href="javascript:history.back()">← Back</a>
    </div>

    <div class="meta">
        <div>
            <h1>Hospital Management System</h1>
            <div>123 Health Avenue, Medical District</div>
            <div>Phone: +880-XXX-XXXXXX · info@hospital.com</div>
        </div>
        <div class="right">
            <div><strong>BILL #</strong> {{ $bill->bill_no }}</div>
            <div><strong>Type:</strong> <span class="badge">{{ strtoupper($bill->bill_type) }}</span></div>
            <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($bill->bill_date)->format('d M Y') }}</div>
            <div><strong>Status:</strong>
                <span class="{{ $bill->balance_due <= 0.01 ? 'status-paid' : 'status-due' }}">
                    {{ strtoupper(str_replace('_', ' ', $bill->status)) }}
                </span>
            </div>
        </div>
    </div>

    <div class="info-grid">
        <div><strong>Patient:</strong> {{ optional($bill->patient)->patient_name ?? '—' }}</div>
        <div><strong>MRN:</strong> {{ optional($bill->patient)->mrn ?? '—' }}</div>
        <div><strong>Phone:</strong> {{ optional($bill->patient)->mobileno ?? '—' }}</div>
        <div><strong>Gender / DOB:</strong> {{ optional($bill->patient)->gender ?? '—' }} · {{ optional($bill->patient)->dob ?? '—' }}</div>
        <div><strong>Doctor:</strong> {{ optional(optional($bill->encounter)->doctor)->name ?? '—' }}</div>
        <div><strong>Department:</strong> {{ optional(optional($bill->encounter)->department)->name ?? '—' }}</div>
        <div><strong>Encounter:</strong> {{ optional($bill->encounter)->encounter_no ?? '—' }}</div>
        <div><strong>Visit Type:</strong> {{ optional($bill->encounter)->encounter_type ?? '—' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="c">#</th>
                <th>Description</th>
                <th class="c">Qty</th>
                <th class="r">Unit Price ৳</th>
                <th class="r">Discount</th>
                <th class="r">Tax</th>
                <th class="r">Line Total ৳</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bill->items as $i)
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td>{{ $i->description }}
                        @if ($i->is_package_included)<span class="badge" style="background:#d1fae5;color:#065f46;">PKG</span>@endif
                    </td>
                    <td class="c">{{ rtrim(rtrim(number_format($i->quantity, 2), '0'), '.') }}</td>
                    <td class="r">{{ number_format($i->unit_price, 2) }}</td>
                    <td class="r">{{ number_format($i->discount_amount ?? 0, 2) }}</td>
                    <td class="r">{{ number_format($i->tax_amount ?? 0, 2) }}</td>
                    <td class="r"><strong>{{ number_format($i->line_total, 2) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="7" class="c">No line items</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal:</td><td class="r">{{ number_format($bill->subtotal, 2) }}</td></tr>
        <tr><td>Discount:</td><td class="r">{{ number_format($bill->discount_total ?? 0, 2) }}</td></tr>
        <tr><td>Tax:</td><td class="r">{{ number_format($bill->tax_total ?? 0, 2) }}</td></tr>
        <tr class="grand"><td>Grand Total:</td><td class="r">৳ {{ number_format($bill->grand_total, 2) }}</td></tr>
        <tr><td>Paid:</td><td class="r status-paid">৳ {{ number_format($bill->paid_total, 2) }}</td></tr>
        <tr><td>Balance Due:</td><td class="r {{ $bill->balance_due > 0.01 ? 'status-due' : 'status-paid' }}">৳ {{ number_format($bill->balance_due, 2) }}</td></tr>
    </table>

    @if ($bill->payments->count())
        <h3 style="margin-top:16px; font-size:14px;">Payment History</h3>
        <table>
            <thead><tr><th>Receipt</th><th>Date</th><th>Method</th><th>Reference</th><th class="r">Amount ৳</th></tr></thead>
            <tbody>
                @foreach ($bill->payments as $p)
                    <tr>
                        <td>{{ $p->receipt_no }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</td>
                        <td>{{ ucfirst($p->method) }}</td>
                        <td>{{ $p->reference_no ?? '—' }}</td>
                        <td class="r status-paid">{{ number_format($p->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div>
            Computer-generated bill. No signature required.<br>
            Printed: {{ now()->format('d M Y, h:i A') }}
        </div>
        <div>
            <span class="sig">Authorised Signatory</span>
        </div>
    </div>

    <script>
        // Auto-open print dialog on load for one-click "Save as PDF"
        if (location.search.includes('autoprint=1')) {
            window.addEventListener('load', () => setTimeout(() => window.print(), 300));
        }
    </script>
</body>
</html>
