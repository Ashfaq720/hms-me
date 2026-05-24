<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OT Bill — {{ $schedule->schedule_no }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #222; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        h2 { font-size: 14px; margin: 16px 0 6px 0; border-bottom: 1px solid #999; padding-bottom: 2px; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 12px; }
        .header .meta { text-align: right; font-size: 11px; }
        .meta-row { margin-bottom: 1px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #bbb; padding: 4px 6px; text-align: left; }
        th { background: #f1f3f5; }
        td.right, th.right { text-align: right; }
        tfoot td { font-weight: bold; background: #f8f9fa; }
        .info-grid { display: table; width: 100%; }
        .info-grid > div { display: table-row; }
        .info-grid .lbl { display: table-cell; padding: 2px 8px 2px 0; color: #666; width: 35%; }
        .info-grid .val { display: table-cell; padding: 2px 0; }
        .signs { margin-top: 40px; display: flex; justify-content: space-between; }
        .sign-box { width: 200px; border-top: 1px solid #333; padding-top: 4px; text-align: center; font-size: 11px; }
        .actions { margin-bottom: 12px; }
        @media print { .actions { display: none; } }
        .btn-print { background: #0d6efd; color: #fff; padding: 6px 14px; border: 0; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; }
        .btn-back  { background: #6c757d; color: #fff; padding: 6px 14px; border: 0; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; margin-left: 8px; }
    </style>
</head>
<body>
    @php
        $req = $schedule->surgeryRequest;
        $patient = $req->patient ?? null;
        $type = $req->surgeryType ?? null;
        $total = $estimatedCharges['ot_room']
               + $estimatedCharges['surgeon']
               + $estimatedCharges['anesthesia']
               + $estimatedCharges['recovery']
               + $estimatedCharges['consumables_total']
               + $estimatedCharges['emergency_surcharge'];
    @endphp

    @if(empty($isPdf))
        <div class="actions">
            <a href="#" onclick="window.print(); return false;" class="btn-print">🖨 Print / Save as PDF</a>
            <a href="{{ url()->previous() }}" class="btn-back">← Back</a>
        </div>
    @endif

    <div class="header">
        <div>
            <h1>{{ config('app.name', 'Hospital') }}</h1>
            <div style="font-size:11px;color:#666;">Operation Theatre — Provisional Bill</div>
        </div>
        <div class="meta">
            <div class="meta-row"><strong>Schedule:</strong> {{ $schedule->schedule_no }}</div>
            <div class="meta-row"><strong>Request:</strong> {{ $req?->request_no ?? '—' }}</div>
            <div class="meta-row"><strong>Date:</strong> {{ now()->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    <div class="info-grid">
        <div>
            <div class="lbl">Patient</div>
            <div class="val">{{ $patient?->patient_name ?? '—' }}
                @if($patient?->patient_unique_id) <span style="color:#666;">({{ $patient->patient_unique_id }})</span>@endif
            </div>
        </div>
        <div><div class="lbl">Age / Sex</div><div class="val">{{ $patient?->age ?? '—' }} / {{ $patient?->gender ?? '—' }}</div></div>
        <div><div class="lbl">Surgery / Procedure</div><div class="val">{{ $type?->name ?? '—' }}</div></div>
        <div><div class="lbl">Primary Surgeon</div><div class="val">{{ optional($req?->primarySurgeon)->name ?? '—' }}</div></div>
        <div><div class="lbl">OT Room</div><div class="val">{{ optional($schedule->room)->name ?? optional($schedule->room)->code ?? '—' }}</div></div>
        <div><div class="lbl">Surgery Start</div><div class="val">{{ optional($schedule->actual_start ?? $schedule->scheduled_start)->format('Y-m-d H:i') ?? '—' }}</div></div>
        <div><div class="lbl">Surgery End</div><div class="val">{{ optional($schedule->actual_end ?? $schedule->scheduled_end)->format('Y-m-d H:i') ?? '—' }}</div></div>
    </div>

    <h2>Service Charges</h2>
    <table>
        <thead><tr><th>#</th><th>Description</th><th class="right">Amount</th></tr></thead>
        <tbody>
            @php $n = 1; @endphp
            <tr><td>{{ $n++ }}</td><td>OT Room Charge</td><td class="right">{{ number_format($estimatedCharges['ot_room'], 2) }}</td></tr>
            <tr><td>{{ $n++ }}</td><td>Surgeon Fee</td><td class="right">{{ number_format($estimatedCharges['surgeon'], 2) }}</td></tr>
            <tr><td>{{ $n++ }}</td><td>Anesthesia Fee</td><td class="right">{{ number_format($estimatedCharges['anesthesia'], 2) }}</td></tr>
            <tr><td>{{ $n++ }}</td><td>Recovery Room Charge</td><td class="right">{{ number_format($estimatedCharges['recovery'], 2) }}</td></tr>
            @if($schedule->emergency_fast_track && $estimatedCharges['emergency_surcharge'] > 0)
                <tr><td>{{ $n++ }}</td><td>Emergency Surcharge (15%)</td><td class="right">{{ number_format($estimatedCharges['emergency_surcharge'], 2) }}</td></tr>
            @endif
        </tbody>
    </table>

    <h2>Consumables / Implants / Instruments</h2>
    <table>
        <thead><tr><th>#</th><th>Item</th><th>Type</th><th class="right">Qty</th><th class="right">Rate</th><th class="right">Amount</th></tr></thead>
        <tbody>
            @forelse($schedule->consumableUsages as $i => $u)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $u->item_name }}@if($u->item_code) <small style="color:#666">({{ $u->item_code }})</small>@endif</td>
                    <td>{{ ucfirst($u->type) }}</td>
                    <td class="right">{{ $u->quantity }} {{ $u->unit }}</td>
                    <td class="right">{{ number_format($u->rate, 2) }}</td>
                    <td class="right">{{ number_format($u->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#666;">No consumables billed.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="5" class="right">Sub-total (Consumables)</td><td class="right">{{ number_format($estimatedCharges['consumables_total'], 2) }}</td></tr>
        </tfoot>
    </table>

    <table>
        <tfoot>
            <tr>
                <td style="width:70%" class="right">GRAND TOTAL</td>
                <td class="right" style="font-size:14px;">{{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="signs">
        <div class="sign-box">Patient / Attendant</div>
        <div class="sign-box">Cashier / Billing</div>
        <div class="sign-box">OT Manager</div>
    </div>

    @if(! empty($autoPrint))
        <script>window.addEventListener('load', () => setTimeout(() => window.print(), 200));</script>
    @endif
</body>
</html>
