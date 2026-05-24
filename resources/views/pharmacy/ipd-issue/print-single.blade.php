<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ipd Issue — {{ $issue->issue_no }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size:12px; color:#222; padding:28px; }
        .header { text-align:center; border-bottom:2px solid #333; padding-bottom:12px; margin-bottom:20px; }
        .header h2 { font-size:18px; font-weight:bold; }
        .header p  { font-size:11px; color:#555; margin-top:4px; }
        .section-title { font-size:12px; font-weight:bold; border-bottom:1px solid #ddd; padding-bottom:4px; margin:16px 0 8px; text-transform:uppercase; color:#333; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:4px 24px; margin-bottom:8px; }
        .info-row { display:flex; justify-content:space-between; padding:3px 0; font-size:11px; }
        .info-row .lbl { color:#666; }
        .info-row .val { font-weight:bold; }
        table { width:100%; border-collapse:collapse; margin-bottom:16px; }
        thead th { background:#1a237e; color:#fff; padding:7px 8px; font-size:11px; text-align:left; }
        tbody tr:nth-child(even) { background:#f9f9f9; }
        tbody td { padding:6px 8px; border-bottom:1px solid #eee; font-size:11px; }
        tfoot td { padding:7px 8px; font-weight:bold; background:#eef2ff; font-size:11px; }
        .badge { display:inline-block; padding:2px 7px; border-radius:20px; font-size:10px; font-weight:bold; }
        .badge-success { background:#e8f5e9; color:#2e7d32; }
        .badge-warning { background:#fff8e1; color:#f57c00; }
        .badge-danger  { background:#ffebee; color:#c62828; }
        .sig-line { border-top:1px solid #999; width:160px; margin-top:40px; font-size:10px; color:#666; padding-top:4px; }
        .footer { text-align:center; font-size:10px; color:#888; border-top:1px solid #ddd; padding-top:10px; margin-top:20px; }
        .no-print { text-align:center; margin-top:20px; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>

    <div class="header">
        <h2>Ipd Medicine Issue Note</h2>
        <p>{{ $issue->issue_no }} &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <div class="section-title">Issue Information</div>
    <div class="info-grid">
        <div>
            <div class="info-row"><span class="lbl">Issue No</span><span class="val">{{ $issue->issue_no }}</span></div>
            <div class="info-row"><span class="lbl">Ipd No</span><span class="val">{{ $issue->ipdPatient->ipd_no ?? '—' }}</span></div>
            <div class="info-row"><span class="lbl">Requisition No</span><span class="val">{{ $issue->requisition_no ?? '—' }}</span></div>
            <div class="info-row"><span class="lbl">Ward / Bed</span><span class="val">{{ $issue->ward_bed ?? '—' }}</span></div>
        </div>
        <div>
            <div class="info-row"><span class="lbl">Patient</span><span class="val">{{ $issue->patient->patient_name ?? '—' }}</span></div>
            <div class="info-row"><span class="lbl">MRN</span><span class="val">{{ $issue->patient->mrn ?? '—' }}</span></div>
            <div class="info-row"><span class="lbl">Request Source</span><span class="val">{{ $issue->request_source ?? '—' }}</span></div>
            <div class="info-row"><span class="lbl">Issued By</span><span class="val">{{ $issue->issuedBy->name ?? '—' }}</span></div>
        </div>
    </div>
    <div class="info-row" style="margin-bottom:4px;">
        <span class="lbl">Status</span>
        <span class="val">
            <span class="badge {{ $issue->status === 'approved' ? 'badge-success' : ($issue->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                {{ ucfirst($issue->status) }}
            </span>
        </span>
    </div>

    <div class="section-title">Issued Medicines</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Medicine Name</th>
                <th>Duration / Instructions</th>
                <th style="text-align:center;">Qty Required</th>
                <th style="text-align:center;">Available Qty</th>
                <th>Store</th>
            </tr>
        </thead>
        <tbody>
            @forelse($issue->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $item->medicine->medicine_name ?? '—' }}</strong></td>
                    <td>{{ $item->duration ?: '—' }}</td>
                    <td style="text-align:center;">{{ $item->qty_required }}</td>
                    <td style="text-align:center;">{{ $item->available_qty }}</td>
                    <td>{{ $item->store }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#999;">No items.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;">Total Drugs:</td>
                <td style="text-align:center;">{{ $issue->drug_count }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    @if($issue->note)
        <div style="font-size:11px; color:#555; margin-bottom:16px;">
            <strong>Note:</strong> {{ $issue->note }}
        </div>
    @endif

    <div style="display:flex; justify-content:space-between; margin-top:32px;">
        <div><div class="sig-line">Pharmacist Signature</div></div>
        <div><div class="sig-line">Ward Nurse / Receiver</div></div>
        <div><div class="sig-line">Authorised By</div></div>
    </div>

    <div class="footer">Hospital Management System &mdash; Ipd Issue Note &mdash; {{ now()->format('d M Y') }}</div>

    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 24px;background:#1a237e;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">Print</button>
        <button onclick="window.close()" style="padding:8px 24px;background:#eee;color:#333;border:none;border-radius:4px;cursor:pointer;font-size:13px;margin-left:8px;">Close</button>
    </div>

    <script>window.addEventListener('load', function() { window.print(); });</script>
</body>
</html>
