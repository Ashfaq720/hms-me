<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Transactions — Print</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; padding: 16px 0 10px; border-bottom: 2px solid #333; margin-bottom: 14px; }
        .header h2 { font-size: 18px; margin-bottom: 4px; }
        .header p  { font-size: 11px; color: #555; }
        .filters   { font-size: 11px; margin-bottom: 12px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ddd; padding: 5px 7px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-opd { background:#dbeafe; color:#1e40af; padding:2px 6px; border-radius:4px; font-size:10px; }
        .badge-ipd { background:#cffafe; color:#0e7490; padding:2px 6px; border-radius:4px; font-size:10px; }
        .badge-otc { background:#dcfce7; color:#166534; padding:2px 6px; border-radius:4px; font-size:10px; }
        .footer { text-align: right; font-size: 10px; color: #888; margin-top: 20px; }
        @media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>Pharmacy Transactions Report</h2>
        <p>Generated: {{ now()->format('d M Y, h:i A') }}</p>
        @if(!empty(array_filter($filters)))
            <p class="filters">
                @if(!empty($filters['transaction_type'])) Type: {{ strtoupper($filters['transaction_type']) }} &nbsp;@endif
                @if(!empty($filters['date_from'])) From: {{ $filters['date_from'] }} &nbsp;@endif
                @if(!empty($filters['date_to'])) To: {{ $filters['date_to'] }} @endif
            </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Txn No</th>
                <th>Type</th>
                <th>Patient / Customer</th>
                <th class="text-center">Drugs</th>
                <th class="text-right">Total (TK)</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Pharmacist</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($transactions as $i => $t)
            @php $grandTotal += $t->total_amount; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $t->transaction_no }}</td>
                <td><span class="badge-{{ $t->transaction_type }}">{{ strtoupper($t->transaction_type) }}</span></td>
                <td>
                    @if($t->transaction_type === 'otc')
                        {{ $t->customer_name ?? 'Walk-in' }}
                    @else
                        {{ $t->patient->patient_name ?? '—' }}
                    @endif
                </td>
                <td class="text-center">{{ $t->drug_count }}</td>
                <td class="text-right">{{ number_format($t->total_amount, 2) }}</td>
                <td>{{ $t->payment_status ? ucfirst($t->payment_status) : 'Running Bill' }}</td>
                <td>{{ ucfirst($t->status) }}</td>
                <td>{{ $t->pharmacist->name ?? '—' }}</td>
                <td>{{ $t->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Grand Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Total {{ $transactions->count() }} records &nbsp;|&nbsp; Printed by {{ auth()->user()->name ?? 'System' }}</div>

    <script>window.onload = function() { window.print(); };</script>
</body>
</html>
