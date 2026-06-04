<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ipd Issue Report</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial, sans-serif; font-size:12px; color:#222; padding:24px; }
        .header { text-align:center; border-bottom:2px solid #333; padding-bottom:12px; margin-bottom:16px; }
        .header h2 { font-size:18px; font-weight:bold; margin-bottom:4px; }
        .header p  { font-size:11px; color:#555; }
        .filters { background:#f5f5f5; border:1px solid #ddd; border-radius:4px; padding:8px 12px; margin-bottom:16px; font-size:11px; }
        .summary { display:flex; gap:12px; margin-bottom:16px; }
        .summary-box { flex:1; border:1px solid #ddd; border-radius:4px; padding:8px; text-align:center; }
        .summary-box .val { font-size:16px; font-weight:bold; color:#1a237e; }
        .summary-box .lbl { font-size:10px; color:#666; margin-top:2px; }
        table { width:100%; border-collapse:collapse; margin-bottom:16px; }
        thead th { background:#1a237e; color:#fff; padding:7px 8px; font-size:11px; text-align:left; }
        tbody tr:nth-child(even) { background:#f9f9f9; }
        tbody td { padding:6px 8px; border-bottom:1px solid #eee; font-size:11px; vertical-align:middle; }
        .badge { display:inline-block; padding:2px 7px; border-radius:20px; font-size:10px; font-weight:bold; }
        .badge-success  { background:#e8f5e9; color:#2e7d32; }
        .badge-warning  { background:#fff8e1; color:#f57c00; }
        .badge-danger   { background:#ffebee; color:#c62828; }
        .badge-secondary{ background:#eceff1; color:#546e7a; }
        .footer { text-align:center; font-size:10px; color:#888; border-top:1px solid #ddd; padding-top:10px; margin-top:8px; }
        .no-print { text-align:center; margin-top:20px; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>

    <div class="header">
        <h2>Ipd Issue Report</h2>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    @php $activeFilters = array_filter($filters); @endphp
    @if(count($activeFilters))
        <div class="filters">
            <strong>Filters:</strong>
            @if(!empty($filters['date_from']))      <span>From: {{ $filters['date_from'] }}</span> @endif
            @if(!empty($filters['patient_name']))   <span>Patient: {{ $filters['patient_name'] }}</span> @endif
            @if(!empty($filters['issue_no']))       <span>Issue No: {{ $filters['issue_no'] }}</span> @endif
            @if(!empty($filters['requisition_no'])) <span>Req No: {{ $filters['requisition_no'] }}</span> @endif
            @if(!empty($filters['request_source'])) <span>Source: {{ $filters['request_source'] }}</span> @endif
            @if(!empty($filters['status']))         <span>Status: {{ $filters['status'] }}</span> @endif
        </div>
    @endif

    <div class="summary">
        <div class="summary-box">
            <div class="val">{{ $issues->count() }}</div>
            <div class="lbl">Total Records</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ number_format($issues->sum('total_amount'), 2) }}</div>
            <div class="lbl">Total Amount (TK)</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ $issues->sum('drug_count') }}</div>
            <div class="lbl">Total Drugs</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ $issues->where('status','approved')->count() }}</div>
            <div class="lbl">Approved</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ $issues->where('status','pending')->count() }}</div>
            <div class="lbl">Pending</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Issue No</th>
                <th>Date</th>
                <th>Ipd No</th>
                <th>Patient</th>
                <th>Ward/Bed</th>
                <th>Req. No</th>
                <th>Source</th>
                <th style="text-align:center;">Drugs</th>
                <th style="text-align:right;">Amount (TK)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($issues as $i => $issue)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $issue->issue_no }}</strong></td>
                    <td>{{ $issue->created_at->format('d/m/Y') }}</td>
                    <td>{{ $issue->ipdPatient->ipd_no ?? '—' }}</td>
                    <td>{{ $issue->patient->patient_name ?? '—' }}</td>
                    <td>{{ $issue->ward_bed ?? '—' }}</td>
                    <td>{{ $issue->requisition_no ?? '—' }}</td>
                    <td>{{ $issue->request_source ?? '—' }}</td>
                    <td style="text-align:center;">{{ $issue->drug_count }}</td>
                    <td style="text-align:right;">{{ number_format($issue->total_amount, 2) }}</td>
                    <td>
                        @switch($issue->status)
                            @case('approved') <span class="badge badge-success">Approved</span> @break
                            @case('pending')  <span class="badge badge-warning">Pending</span> @break
                            @case('returned') <span class="badge badge-danger">Returned</span> @break
                            @default          <span class="badge badge-secondary">{{ ucfirst($issue->status) }}</span>
                        @endswitch
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center;padding:16px;color:#999;">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Hospital Management System &mdash; Ipd Issue Report &mdash; {{ now()->format('d M Y') }}</div>

    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 24px;background:#1a237e;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">Print</button>
        <button onclick="window.close()" style="padding:8px 24px;background:#eee;color:#333;border:none;border-radius:4px;cursor:pointer;font-size:13px;margin-left:8px;">Close</button>
    </div>

    <script>window.addEventListener('load', function() { window.print(); });</script>
</body>
</html>
