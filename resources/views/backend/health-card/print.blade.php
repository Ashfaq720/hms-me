<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Health Card Management</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #1F2937; font-size: 10.5px; }
        .header { border-bottom: 2px solid #4361EE; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { margin: 0 0 4px 0; font-size: 20px; color: #0F172A; }
        .meta { color: #64748B; font-size: 10px; }
        .filters { background: #F8FAFC; border: 1px solid #E2E8F0; padding: 6px 10px; margin-bottom: 10px; font-size: 10px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #4361EE;
            color: #fff;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
            padding: 7px 6px;
            text-align: left;
            border: 1px solid #4361EE;
        }
        tbody td { border: 1px solid #E2E8F0; padding: 6px; vertical-align: middle; font-size: 10px; }
        tbody tr:nth-child(even) td { background: #F8FAFC; }
        .card-no { color: #4361EE; font-weight: 700; }
        .patient-name { font-weight: 600; color: #0F172A; }
        .muted { color: #64748B; font-size: 9px; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 600;
            color: #fff;
        }
        .badge-active { background: #10B981; }
        .badge-inactive { background: #64748B; }
        .badge-deceased { background: #0F172A; }
        .summary { margin-top: 10px; font-size: 10px; color: #475569; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Health Card Management</h1>
        <div class="meta">
            Generated on {{ $generated->format('d M Y, h:i A') }} &nbsp;|&nbsp;
            Total Records: <strong>{{ number_format($patients->count()) }}</strong>
        </div>
    </div>

    @if ($search || $status || $from || $to)
        <div class="filters">
            <strong>Active filters:</strong>
            @if ($search) Search: <em>{{ $search }}</em> &nbsp; @endif
            @if ($status) Status: <em>{{ ucfirst($status) }}</em> &nbsp; @endif
            @if ($from) From: <em>{{ \Carbon\Carbon::parse($from)->format('d M Y') }}</em> &nbsp; @endif
            @if ($to) To: <em>{{ \Carbon\Carbon::parse($to)->format('d M Y') }}</em> @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 14%;">Card No</th>
                <th style="width: 22%;">Patient Name</th>
                <th style="width: 13%;">MRN</th>
                <th style="width: 14%;">Mobile</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 12%;">Issue Date</th>
                <th style="width: 12%;">Expiry Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($patients as $i => $patient)
                @php
                    $issueDate  = $patient->created_at;
                    $expiryDate = $issueDate ? $issueDate->copy()->addYears(2) : null;

                    $cardNo = $patient->health_card_no
                        ?: 'HC-' . ($issueDate ? $issueDate->format('Y') : date('Y'))
                            . '-' . str_pad((string) $patient->id, 5, '0', STR_PAD_LEFT);
                    $mrnNo = $patient->mrn
                        ?: 'MRN-' . str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT);

                    if ($patient->is_dead) {
                        $statusLabel = 'Deceased';
                        $badgeClass  = 'badge-deceased';
                    } elseif ($patient->is_active) {
                        $statusLabel = 'Active';
                        $badgeClass  = 'badge-active';
                    } else {
                        $statusLabel = 'Inactive';
                        $badgeClass  = 'badge-inactive';
                    }
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="card-no">{{ $cardNo }}</td>
                    <td>
                        <div class="patient-name">{{ $patient->patient_name }}</div>
                        @if ($patient->gender || $patient->blood_group)
                            <div class="muted">
                                {{ $patient->gender }}{{ $patient->gender && $patient->blood_group ? ' · ' : '' }}{{ $patient->blood_group }}
                            </div>
                        @endif
                    </td>
                    <td>{{ $mrnNo }}</td>
                    <td>{{ $patient->mobileno ?: '—' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                    <td>{{ $issueDate ? $issueDate->format('d M Y') : '—' }}</td>
                    <td>{{ $expiryDate ? $expiryDate->format('d M Y') : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #64748B;">
                        No patient records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total <strong>{{ number_format($patients->count()) }}</strong> health card record(s) listed.
    </div>

</body>
</html>
