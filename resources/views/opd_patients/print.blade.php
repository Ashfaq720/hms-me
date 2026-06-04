<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OPD Patient - {{ $opdPatient->patient->patient_name ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 24px;
        }
        h1, h2, h3, h4 { margin: 0 0 6px; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header h2 { color: #0d6efd; }
        .meta { font-size: 11px; color: #555; }
        .section { margin-top: 16px; page-break-inside: avoid; }
        .section h4 {
            background: #f1f3f5;
            padding: 6px 10px;
            border-left: 4px solid #0d6efd;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th { background: #f8f9fa; font-weight: 600; }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 24px;
            margin-top: 6px;
        }
        .info-item { display: flex; gap: 6px; }
        .info-item strong { min-width: 110px; color: #475467; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #888; }
        .badge {
            display: inline-block;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            background: #e7f1ff;
            color: #0d6efd;
            margin: 1px;
        }
        @media print {
            body { margin: 12mm; }
            .no-print { display: none; }
        }
        .actions { text-align: right; margin-bottom: 10px; }
        .actions button {
            padding: 6px 14px;
            background: #0d6efd;
            color: #fff;
            border: 0;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="actions no-print">
    <button onclick="window.print()">Print</button>
</div>

<div class="header">
    <div>
        <h2>OPD Patient Record</h2>
        <div class="meta">
            Case ID: <strong>{{ $opdPatient->case_id ?? '-' }}</strong> &nbsp;|&nbsp;
            Visit Date: <strong>{{ $opdPatient->date ? $opdPatient->date->format('d M Y, h:i A') : '-' }}</strong> &nbsp;|&nbsp;
            Visit Type: <strong>{{ ucfirst($opdPatient->visit_type ?? 'new') }}</strong>
        </div>
    </div>
    <div class="meta text-end">
        Printed on: {{ now()->format('d M Y, h:i A') }}
    </div>
</div>

{{-- Patient Info --}}
<div class="section">
    <h4>Patient Information</h4>
    <div class="info-grid">
        <div class="info-item"><strong>Name:</strong> {{ $opdPatient->patient->patient_name ?? '-' }}</div>
        <div class="info-item"><strong>Mobile:</strong> {{ $opdPatient->patient->mobileno ?? '-' }}</div>
        <div class="info-item"><strong>Gender:</strong> {{ ucfirst($opdPatient->patient->gender ?? '-') }}</div>
        <div class="info-item"><strong>Blood Group:</strong> {{ $opdPatient->patient->blood_group ?? '-' }}</div>
        <div class="info-item"><strong>DOB:</strong> {{ $opdPatient->patient->dob ?? '-' }}</div>
        <div class="info-item"><strong>Allergies:</strong> {{ $opdPatient->patient->known_allergies ?? '-' }}</div>
    </div>
</div>

{{-- Visit Info --}}
<div class="section">
    <h4>Visit Details</h4>
    <div class="info-grid">
        <div class="info-item"><strong>Doctor:</strong> {{ $opdPatient->doctor->name ?? '-' }}</div>
        <div class="info-item"><strong>Department:</strong> {{ $opdPatient->department->name ?? '-' }}</div>
        <div class="info-item"><strong>Status:</strong> {{ $opdPatient->status ?? '-' }}</div>
        <div class="info-item"><strong>Remarks:</strong> {{ $opdPatient->remarks ?? '-' }}</div>
    </div>
</div>

{{-- Vitals --}}
@if ($opdPatient->vitalChecks && $opdPatient->vitalChecks->count())
    <div class="section">
        <h4>Vital Checks</h4>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Height</th>
                    <th>Weight</th>
                    <th>BP</th>
                    <th>Pulse</th>
                    <th>Temp</th>
                    <th>SpO2</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($opdPatient->vitalChecks as $vc)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($vc->created_at)->format('d M Y') }}</td>
                        <td>{{ $vc->height ?? '-' }}</td>
                        <td>{{ $vc->weight ?? '-' }}</td>
                        <td>{{ $vc->blood_pressure ?? '-' }}</td>
                        <td>{{ $vc->pulse ?? '-' }}</td>
                        <td>{{ $vc->temperature ?? '-' }}</td>
                        <td>{{ $vc->spo2 ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Prescriptions --}}
@if ($opdPatient->prescriptions && $opdPatient->prescriptions->count())
    <div class="section">
        <h4>Prescriptions</h4>
        <table>
            <thead>
                <tr>
                    <th>Rx No</th>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Symptoms</th>
                    <th>Medicines</th>
                    <th>Lab Tests</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($opdPatient->prescriptions as $rx)
                    <tr>
                        <td>{{ $rx->prescription_no }}</td>
                        <td>{{ $rx->date ? $rx->date->format('d M Y') : '-' }}</td>
                        <td>{{ $rx->doctor->name ?? '-' }}</td>
                        <td>
                            @foreach ($rx->symptoms as $s)
                                <span class="badge">{{ $s->symptom->name ?? '-' }}</span>
                            @endforeach
                        </td>
                        <td>
                            @foreach ($rx->medicines as $m)
                                <span class="badge">{{ $m->medicine->medicine_name ?? '-' }}</span>
                            @endforeach
                        </td>
                        <td>
                            @foreach ($rx->labInvestigations as $l)
                                <span class="badge">{{ $l->labInvestigation->name ?? '-' }}</span>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Medications --}}
@if ($opdPatient->medications && $opdPatient->medications->count())
    <div class="section">
        <h4>Medications</h4>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Medicine</th>
                    <th>Dosage</th>
                    <th>Medicated By</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($opdPatient->medications as $m)
                    <tr>
                        <td>{{ $m->datetime ? $m->datetime->format('d M Y, h:i A') : '-' }}</td>
                        <td>{{ $m->medicine->medicine_name ?? '-' }}</td>
                        <td>{{ $m->dosage ?? '-' }}</td>
                        <td>{{ $m->medicated_by ?? '-' }}</td>
                        <td>{{ $m->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Charges --}}
@if ($opdPatient->charges && $opdPatient->charges->count())
    <div class="section">
        <h4>Charges</h4>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Charge Item</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Tax</th>
                    <th class="text-end">Net Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($opdPatient->charges as $c)
                    <tr>
                        <td>{{ $c->date ? \Illuminate\Support\Carbon::parse($c->date)->format('d M Y') : '-' }}</td>
                        <td>{{ $c->charge_item }}</td>
                        <td class="text-end">{{ number_format($c->unit_price, 2) }}</td>
                        <td class="text-center">{{ $c->quantity }}</td>
                        <td class="text-end">{{ number_format($c->tax, 2) }}</td>
                        <td class="text-end">{{ number_format($c->net_amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="5" class="text-end">Total</th>
                    <th class="text-end">{{ number_format($opdPatient->charges->sum('net_amount'), 2) }}</th>
                </tr>
            </tbody>
        </table>
    </div>
@endif

{{-- Payments --}}
@if ($opdPatient->transactions && $opdPatient->transactions->count())
    <div class="section">
        <h4>Payments</h4>
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Mode</th>
                    <th>Notes</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($opdPatient->transactions as $t)
                    <tr>
                        <td>{{ $t->invoice_no ?? '-' }}</td>
                        <td>{{ $t->payment_date ? \Illuminate\Support\Carbon::parse($t->payment_date)->format('d M Y') : '-' }}</td>
                        <td>{{ ucfirst($t->payment_via ?? '-') }}</td>
                        <td>{{ $t->notes ?? '-' }}</td>
                        <td class="text-end">{{ number_format($t->net_amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="4" class="text-end">Total Paid</th>
                    <th class="text-end">{{ number_format($opdPatient->transactions->sum('net_amount'), 2) }}</th>
                </tr>
            </tbody>
        </table>
    </div>
@endif

<script>
    window.addEventListener('load', function () { setTimeout(() => window.print(), 300); });
</script>
</body>
</html>
