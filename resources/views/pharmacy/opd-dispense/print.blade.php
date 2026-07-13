<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPD Dispense Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; background: #fff; padding: 24px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 12px; margin-bottom: 16px; }
        .header h2 { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #555; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 11px; color: #444; }
        .filters { background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; padding: 8px 12px; margin-bottom: 16px; font-size: 11px; }
        .filters span { margin-right: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead th { background: #1a237e; color: #fff; padding: 7px 8px; font-size: 11px; text-align: left; }
        tbody tr:nth-child(even) { background: #f9f9f9; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; vertical-align: middle; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-warning { background: #fff8e1; color: #f57c00; }
        .badge-danger  { background: #ffebee; color: #c62828; }
        .badge-info    { background: #e3f2fd; color: #1565c0; }
        .badge-secondary { background: #eceff1; color: #546e7a; }
        .summary { display: flex; gap: 16px; margin-bottom: 16px; }
        .summary-box { flex: 1; border: 1px solid #ddd; border-radius: 4px; padding: 8px 12px; text-align: center; }
        .summary-box .val { font-size: 16px; font-weight: bold; color: #1a237e; }
        .summary-box .lbl { font-size: 10px; color: #666; margin-top: 2px; }
        .footer { text-align: center; font-size: 10px; color: #888; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 8px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>OPD Dispense Report</h2>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    {{-- Active filters summary --}}
    @php
        $activeFilters = array_filter($filters);
    @endphp
    @if(count($activeFilters))
        <div class="filters">
            <strong>Filters:</strong>
            @if(!empty($filters['date_from'])) <span>From: {{ $filters['date_from'] }}</span> @endif
            @if(!empty($filters['date_to']))   <span>To: {{ $filters['date_to'] }}</span> @endif
            @if(!empty($filters['patient_name'])) <span>Patient: {{ $filters['patient_name'] }}</span> @endif
            @if(!empty($filters['opd_no']))    <span>OPD No: {{ $filters['opd_no'] }}</span> @endif
            @if(!empty($filters['status']))    <span>Status: {{ $filters['status'] }}</span> @endif
            @if(!empty($filters['payment_status'])) <span>Payment: {{ $filters['payment_status'] }}</span> @endif
        </div>
    @endif

    {{-- Summary boxes --}}
    <div class="summary">
        <div class="summary-box">
            <div class="val">{{ $dispenses->count() }}</div>
            <div class="lbl">Total Records</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ number_format($dispenses->sum('total_amount'), 2) }}</div>
            <div class="lbl">Total Amount (TK)</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ $dispenses->sum('drug_count') }}</div>
            <div class="lbl">Total Drugs Dispensed</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ $dispenses->where('status', 'completed')->count() }}</div>
            <div class="lbl">Completed</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ $dispenses->where('payment_status', 'paid')->count() }}</div>
            <div class="lbl">Paid</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Dispense #</th>
                <th>Date</th>
                <th>OPD Case No</th>
                <th>Patient</th>
                <th>Prescription</th>
                <th>Drugs</th>
                <th>Amount (TK)</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Pharmacist</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dispenses as $i => $d)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $d->dispense_no }}</strong></td>
                    <td>{{ $d->created_at->format('d/m/Y') }}</td>
                    <td>{{ $d->opdPatient->case_id ?? '—' }}</td>
                    <td>{{ $d->patient->patient_name ?? '—' }}</td>
                    <td>{{ $d->prescription->prescription_no ?? '—' }}</td>
                    <td style="text-align:center;">{{ $d->drug_count }}</td>
                    <td style="text-align:right;">{{ number_format($d->total_amount, 2) }}</td>
                    <td>
                        @if($d->payment_status === 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($d->payment_status === 'unpaid')
                            <span class="badge badge-danger">Unpaid</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst($d->payment_status) }}</span>
                        @endif
                    </td>
                    <td>
                        @switch($d->status)
                            @case('completed')      <span class="badge badge-success">Completed</span> @break
                            @case('pending_approval') <span class="badge badge-warning">Pending</span> @break
                            @case('partial')        <span class="badge badge-info">Partial</span> @break
                            @case('cancelled')      <span class="badge badge-danger">Cancelled</span> @break
                            @default                <span class="badge badge-secondary">{{ ucfirst($d->status) }}</span>
                        @endswitch
                    </td>
                    <td>{{ $d->pharmacist->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center; padding:20px; color:#999;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Hospital Management System &mdash; OPD Dispense Report &mdash; {{ now()->format('d M Y') }}
    </div>

    <div class="no-print" style="text-align:center; margin-top:20px;">
        <button onclick="window.print()" style="padding:8px 24px; background:#1a237e; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px;">
            Print
        </button>
        <button onclick="window.close()" style="padding:8px 24px; background:#eee; color:#333; border:none; border-radius:4px; cursor:pointer; font-size:13px; margin-left:8px;">
            Close
        </button>
    </div>

    <script>
        // Auto-print when opened in new tab
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
