<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPD Bill — {{ $opdPatient->patient->patient_name ?? '' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #fff;
            padding: 24px;
        }

        /* ── Actions bar (hidden on print) ── */
        .no-print {
            text-align: right;
            margin-bottom: 16px;
        }
        .no-print button {
            padding: 7px 20px;
            background: #2b335d;
            color: #fff;
            border: 0;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            margin-left: 6px;
        }
        .no-print button.btn-secondary {
            background: #6c757d;
        }

        /* ── Header ── */
        .bill-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #2b335d;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .hospital-name {
            font-size: 20px;
            font-weight: 700;
            color: #2b335d;
            margin-bottom: 3px;
        }
        .hospital-meta { font-size: 11px; color: #555; line-height: 1.7; }
        .bill-title-box { text-align: right; }
        .bill-title {
            font-size: 22px;
            font-weight: 800;
            color: #2b335d;
            letter-spacing: 1px;
        }
        .bill-meta { font-size: 11px; color: #666; margin-top: 4px; line-height: 1.7; }

        /* ── Section ── */
        .section { margin-top: 14px; page-break-inside: avoid; }
        .section-title {
            background: #2b335d;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            padding: 5px 10px;
            margin-bottom: 0;
        }

        /* ── Info grid ── */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td {
            padding: 5px 8px;
            border: 1px solid #e0e0e0;
            vertical-align: top;
            font-size: 11.5px;
        }
        .info-table td.lbl {
            font-weight: 700;
            color: #444;
            background: #f8f9fb;
            width: 16%;
            white-space: nowrap;
        }
        .info-table td.val { width: 17%; }

        /* ── Data table ── */
        .data-table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        .data-table th {
            background: #f1f3f8;
            border: 1px solid #d0d4e0;
            padding: 6px 8px;
            font-weight: 700;
            color: #2b335d;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #e0e3ec;
            padding: 5px 8px;
            vertical-align: top;
        }
        .data-table tbody tr:nth-child(even) td { background: #fafbfd; }
        .data-table tfoot td, .data-table tfoot th {
            border: 1px solid #d0d4e0;
            padding: 6px 8px;
            font-weight: 700;
            background: #f1f3f8;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }

        /* ── Vitals grid ── */
        .vitals-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0;
            border: 1px solid #d0d4e0;
        }
        .vital-cell {
            padding: 6px 8px;
            border-right: 1px solid #d0d4e0;
            font-size: 11px;
        }
        .vital-cell:last-child { border-right: 0; }
        .vital-cell .vlabel { color: #888; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .vital-cell .vval   { font-size: 13px; font-weight: 700; color: #2b335d; margin-top: 2px; }

        /* ── Summary box ── */
        .summary-wrap { display: flex; justify-content: flex-end; margin-top: 4px; }
        .summary-box { width: 280px; border: 1px solid #d0d4e0; }
        .summary-box table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .summary-box td { padding: 5px 10px; border-bottom: 1px solid #eee; }
        .summary-box td:last-child { text-align: right; font-weight: 600; }
        .summary-box tr:last-child td { border-bottom: 0; }
        .summary-box .total-row td {
            background: #2b335d;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            border-bottom: 0;
        }
        .balance-due { color: #c0392b; }
        .balance-paid { color: #27ae60; }

        /* ── Badge ── */
        .badge {
            display: inline-block;
            font-size: 10px;
            padding: 1px 5px;
            border-radius: 3px;
            background: #e7f1ff;
            color: #2b335d;
            margin: 1px;
        }

        /* ── Divider ── */
        .divider { border: 0; border-top: 1px solid #ddd; margin: 12px 0; }

        /* ── Footer ── */
        .bill-footer {
            margin-top: 30px;
            border-top: 2px solid #2b335d;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 11px;
            color: #666;
        }
        .sig-block { text-align: center; }
        .sig-line { border-top: 1px solid #999; width: 180px; margin: 30px auto 4px; }

        @media print {
            body { padding: 10mm; }
            .no-print { display: none; }
            .section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

@php
    $patient      = $opdPatient->patient;
    $doctor       = $opdPatient->doctor;
    $department   = $opdPatient->department;
    $latestVital  = ($opdPatient->vitalChecks ?? collect())->sortByDesc('checked_at')->first();

    // Use combined totals when controller provides them; fall back to legacy.
    $totals       = $totals ?? [];
    $bills        = $bills ?? collect();
    $encounterCharges  = $encounterCharges ?? collect();
    $packageEnrollments = $packageEnrollments ?? collect();
    $primaryBill  = $primaryBill ?? null;

    $totalCharges = $totals['combined_charge'] ?? (float) $opdPatient->charges->sum('net_amount');
    $totalPaid    = $totals['combined_paid']   ?? (float) $opdPatient->transactions->sum('net_amount');
    $balance      = $totals['bill_balance_due'] ?? ($totalCharges - $totalPaid);
    $age          = function_exists('calculateAgeFromDob') ? (calculateAgeFromDob($patient?->dob) ?? '—') : ($patient?->dob ? \Carbon\Carbon::parse($patient->dob)->age . ' yrs' : '—');
@endphp

{{-- ── Actions (screen only) ── --}}
<div class="no-print">
    <button class="btn-secondary" onclick="window.history.back()">&#8592; Back</button>
    <button onclick="window.print()">&#128438; Print Bill</button>
</div>

{{-- ── Header ── --}}
<div class="bill-header">
    <div>
        <div class="hospital-name">Hospital Management System</div>
        <div class="hospital-meta">
            123 Health Avenue, Medical District<br>
            Phone: +880-XXX-XXXXXX &nbsp;|&nbsp; Email: info@hospital.com
        </div>
    </div>
    <div class="bill-title-box">
        <div class="bill-title">OPD BILL</div>
        <div class="bill-meta">
            OPD No: <strong>#OPD-{{ str_pad($opdPatient->id, 4, '0', STR_PAD_LEFT) }}</strong><br>
            Case ID: <strong>{{ $opdPatient->case_id ?? '—' }}</strong><br>
            Printed: {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>
</div>

{{-- ── Patient Information ── --}}
<div class="section">
    <div class="section-title">Patient Information</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Patient Name</td>
            <td class="val">{{ $patient?->patient_name ?? '—' }}</td>
            <td class="lbl">MRN</td>
            <td class="val">{{ $patient?->mrn ?? '—' }}</td>
            <td class="lbl">Contact No</td>
            <td class="val">{{ $patient?->mobileno ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Age / Gender</td>
            <td class="val">{{ $age }} / {{ $patient?->gender ?? '—' }}</td>
            <td class="lbl">Date of Birth</td>
            <td class="val">{{ $patient?->dob ? \Carbon\Carbon::parse($patient->dob)->format('d M Y') : '—' }}</td>
            <td class="lbl">Blood Group</td>
            <td class="val">{{ $patient?->blood_group ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Address</td>
            <td class="val" colspan="3">{{ $patient?->address ?? '—' }}</td>
            <td class="lbl">Known Allergies</td>
            <td class="val" style="color:#c0392b;">{{ $patient?->known_allergies ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Organization</td>
            <td class="val">{{ $patient?->organization_name ?? '—' }}</td>
            <td class="lbl">Org. ID</td>
            <td class="val">{{ $patient?->organization_id ?? '—' }}</td>
            <td class="lbl">Discount Type</td>
            <td class="val">{{ $patient?->discount_type ?? '—' }}</td>
        </tr>
        @if($patient?->insurance)
        <tr>
            <td class="lbl">Insurance</td>
            <td class="val">{{ $patient->insurance }}</td>
            <td class="lbl">Insurance Validity</td>
            <td class="val" colspan="3">{{ $patient->insurance_validity ?? '—' }}</td>
        </tr>
        @endif
    </table>
</div>

{{-- ── Visit Details ── --}}
<div class="section">
    <div class="section-title">Visit Details</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Visit Date</td>
            <td class="val">{{ $opdPatient->date ? $opdPatient->date->format('d M Y, h:i A') : '—' }}</td>
            <td class="lbl">Visit Type</td>
            <td class="val">{{ ucfirst(str_replace('_', ' ', $opdPatient->visit_type ?? 'new')) }}</td>
            <td class="lbl">Status</td>
            <td class="val">{{ $opdPatient->status ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Department</td>
            <td class="val">{{ $department?->name ?? '—' }}</td>
            <td class="lbl">Consultant</td>
            <td class="val">{{ $doctor?->name ?? '—' }}</td>
            <td class="lbl">Designation</td>
            <td class="val">{{ $doctor?->designation?->name ?? '—' }}</td>
        </tr>
        @if($opdPatient->chief_complaint)
        <tr>
            <td class="lbl">Chief Complaint</td>
            <td class="val" colspan="5">{{ $opdPatient->chief_complaint }}</td>
        </tr>
        @endif
        @if($opdPatient->referral_source)
        <tr>
            <td class="lbl">Referral Source</td>
            <td class="val" colspan="5">{{ $opdPatient->referral_source }}</td>
        </tr>
        @endif
        @if($opdPatient->remarks)
        <tr>
            <td class="lbl">Remarks</td>
            <td class="val" colspan="5">{{ $opdPatient->remarks }}</td>
        </tr>
        @endif
    </table>
</div>

{{-- ── Latest Vitals ── --}}
@if($latestVital)
<div class="section">
    <div class="section-title">Latest Vitals
        @if($latestVital->checked_at)
            &mdash; {{ \Carbon\Carbon::parse($latestVital->checked_at)->format('d M Y, h:i A') }}
        @endif
    </div>
    <div class="vitals-grid">
        <div class="vital-cell">
            <div class="vlabel">Weight</div>
            <div class="vval">{{ $latestVital->weight ?? '—' }} <small style="font-size:10px;font-weight:400;">kg</small></div>
        </div>
        <div class="vital-cell">
            <div class="vlabel">Height</div>
            <div class="vval">{{ $latestVital->height ?? '—' }} <small style="font-size:10px;font-weight:400;">cm</small></div>
        </div>
        <div class="vital-cell">
            <div class="vlabel">Blood Pressure</div>
            <div class="vval">{{ $latestVital->blood_pressure ?? '—' }} <small style="font-size:10px;font-weight:400;">mmHg</small></div>
        </div>
        <div class="vital-cell">
            <div class="vlabel">Heart Rate</div>
            <div class="vval">{{ $latestVital->heart_rate ?? '—' }} <small style="font-size:10px;font-weight:400;">bpm</small></div>
        </div>
        <div class="vital-cell">
            <div class="vlabel">Temperature</div>
            <div class="vval">{{ $latestVital->temperature ?? '—' }} <small style="font-size:10px;font-weight:400;">°F</small></div>
        </div>
        <div class="vital-cell">
            <div class="vlabel">SpO2</div>
            <div class="vval">{{ $latestVital->spo2 ?? '—' }} <small style="font-size:10px;font-weight:400;">%</small></div>
        </div>
        <div class="vital-cell">
            <div class="vlabel">Resp. Rate</div>
            <div class="vval">{{ $latestVital->respiratory_rate ?? '—' }} <small style="font-size:10px;font-weight:400;">/min</small></div>
        </div>
    </div>
</div>
@endif

{{-- ── Prescriptions ── --}}
@if($opdPatient->prescriptions->isNotEmpty())
<div class="section">
    <div class="section-title">Prescriptions</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:12%;">Rx No</th>
                <th style="width:14%;">Date</th>
                <th style="width:16%;">Doctor</th>
                <th>Symptoms</th>
                <th>Medicines</th>
                <th>Lab Tests</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opdPatient->prescriptions as $rx)
            <tr>
                <td>{{ $rx->prescription_no ?? '—' }}</td>
                <td>{{ $rx->date ? $rx->date->format('d M Y') : '—' }}</td>
                <td>{{ $rx->doctor?->name ?? '—' }}</td>
                <td>
                    @forelse($rx->symptoms as $s)
                        <span class="badge">{{ $s->symptom?->name ?? '—' }}</span>
                    @empty —
                    @endforelse
                </td>
                <td>
                    @forelse($rx->medicines as $m)
                        <span class="badge">{{ $m->medicine?->medicine_name ?? '—' }}</span>
                    @empty —
                    @endforelse
                </td>
                <td>
                    @forelse($rx->labInvestigations as $l)
                        <span class="badge">{{ $l->labInvestigation?->name ?? '—' }}</span>
                    @empty —
                    @endforelse
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Medications ── --}}
@if($opdPatient->medications->isNotEmpty())
<div class="section">
    <div class="section-title">Medications</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:18%;">Date / Time</th>
                <th>Medicine</th>
                <th style="width:14%;">Dosage</th>
                <th style="width:10%;">Unit</th>
                <th style="width:16%;">Administered By</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opdPatient->medications as $m)
            <tr>
                <td>{{ $m->datetime ? $m->datetime->format('d M Y, h:i A') : '—' }}</td>
                <td>{{ $m->medicine?->medicine_name ?? '—' }}</td>
                <td>{{ $m->dosage ?? '—' }}</td>
                <td>{{ $m->medicine?->unit?->name ?? '—' }}</td>
                <td>{{ $m->medicated_by ?? '—' }}</td>
                <td>{{ $m->notes ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Auto-Posted Service Charges (Encounter Layer) ── --}}
@if ($encounterCharges->count())
<div class="section">
    <div class="section-title">Service Charges (Auto-Posted)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Service</th>
                <th style="width:11%;">Code</th>
                <th style="width:11%;" class="text-end">Unit Price</th>
                <th style="width:6%;" class="text-center">Qty</th>
                <th style="width:9%;" class="text-end">Discount</th>
                <th style="width:9%;" class="text-end">Tax</th>
                <th style="width:12%;" class="text-end">Net Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($encounterCharges as $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $p->catalog->name ?? $p->trigger_event }}</td>
                <td>{{ $p->catalog->code ?? '—' }}</td>
                <td class="text-end">{{ number_format($p->unit_price, 2) }}</td>
                <td class="text-center">{{ rtrim(rtrim(number_format($p->quantity, 2), '0'), '.') }}</td>
                <td class="text-end">{{ number_format($p->discount_amount ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($p->tax_amount ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($p->net_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-end">Auto-Posted Subtotal</td>
                <td class="text-end">{{ number_format($totals['encounter_charge_total'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- ── Bills (assembled from encounter charges) ── --}}
@if ($bills->count())
<div class="section">
    <div class="section-title">Bills</div>
    @foreach ($bills as $bill)
        <table class="data-table" style="margin-bottom:8px;">
            <thead>
                <tr>
                    <th colspan="4">
                        <strong>{{ $bill->bill_no }}</strong>
                        — Status: <span style="text-transform:uppercase">{{ $bill->status }}</span>
                        · {{ \Carbon\Carbon::parse($bill->bill_date)->format('d M Y') }}
                    </th>
                    <th class="text-end">Grand: {{ number_format($bill->grand_total, 2) }}</th>
                    <th class="text-end">Paid: {{ number_format($bill->paid_total, 2) }}</th>
                    <th class="text-end">Due: {{ number_format($bill->balance_due, 2) }}</th>
                    <th style="width:9%;" class="text-end">{{ $bill->items->count() }} items</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bill->items as $i)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td colspan="3">{{ $i->description }}
                        @if ($i->is_package_included)<span style="background:#d1fae5;color:#065f46;padding:1px 6px;border-radius:8px;font-size:10px;">PKG</span>@endif
                    </td>
                    <td class="text-end">{{ number_format($i->unit_price, 2) }}</td>
                    <td class="text-center">× {{ rtrim(rtrim(number_format($i->quantity, 2), '0'), '.') }}</td>
                    <td class="text-end">{{ number_format($i->tax_amount, 2) }}</td>
                    <td class="text-end"><strong>{{ number_format($i->line_total, 2) }}</strong></td>
                </tr>
                @endforeach
                @foreach ($bill->payments as $pay)
                <tr style="background:#f0f9ff;">
                    <td></td>
                    <td colspan="6">
                        Payment: {{ $pay->receipt_no }} · {{ ucfirst($pay->method) }}
                        · {{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}
                        @if ($pay->reference_no) · Ref {{ $pay->reference_no }}@endif
                    </td>
                    <td class="text-end"><strong>+ {{ number_format($pay->amount, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>
@endif

{{-- ── Package Enrollments ── --}}
@if ($packageEnrollments->count())
<div class="section">
    <div class="section-title">Package Enrolments</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Package</th>
                <th>Type</th>
                <th class="text-end">Agreed ৳</th>
                <th class="text-end">Paid ৳</th>
                <th class="text-end">Outstanding</th>
                <th>Status</th>
                <th class="text-center">Services</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($packageEnrollments as $e)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $e->package->name ?? '—' }}</strong> <span style="color:#888;">{{ $e->enrollment_no }}</span></td>
                <td>{{ $e->package->package_type ?? '—' }}</td>
                <td class="text-end">{{ number_format($e->agreed_price, 2) }}</td>
                <td class="text-end">{{ number_format($e->paid_amount, 2) }}</td>
                <td class="text-end">{{ number_format(max((float) $e->agreed_price - (float) $e->paid_amount, 0), 2) }}</td>
                <td>{{ ucfirst($e->status) }}</td>
                <td class="text-center">{{ $e->package->services->count() ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Charges (Legacy patient_charges) ── --}}
<div class="section">
    <div class="section-title">Manual / Legacy Charges</div>
    @if($opdPatient->charges->isNotEmpty())
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Charge Item</th>
                <th style="width:13%;">Type</th>
                <th style="width:11%;" class="text-end">Unit Price</th>
                <th style="width:6%;" class="text-center">Qty</th>
                <th style="width:9%;" class="text-end">Discount</th>
                <th style="width:9%;" class="text-end">Tax</th>
                <th style="width:12%;" class="text-end">Net Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opdPatient->charges as $c)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $c->charge_item }}</td>
                <td>{{ ucfirst($c->charge_module) }}</td>
                <td class="text-end">{{ number_format($c->unit_price, 2) }}</td>
                <td class="text-center">{{ $c->quantity ?? 1 }}</td>
                <td class="text-end">{{ number_format($c->vat ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($c->tax ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($c->net_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-end">Total Charges</td>
                <td class="text-end">{{ number_format($totalCharges, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
        <div style="padding:8px 10px; color:#888; font-style:italic;">No charges found.</div>
    @endif
</div>

{{-- ── Payments (Legacy transactions) ── --}}
<div class="section">
    <div class="section-title">Manual / Legacy Payment Transactions</div>
    @if($opdPatient->transactions->isNotEmpty())
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th style="width:16%;">Invoice No</th>
                <th style="width:16%;">Date</th>
                <th style="width:14%;">Payment Mode</th>
                <th>Notes</th>
                <th style="width:9%;" class="text-end">Amount</th>
                <th style="width:9%;" class="text-end">Tax</th>
                <th style="width:12%;" class="text-end">Net Paid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opdPatient->transactions as $t)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $t->invoice_no ?? '—' }}</td>
                <td>{{ $t->payment_date ? \Carbon\Carbon::parse($t->payment_date)->format('d M Y') : '—' }}</td>
                <td>{{ ucfirst($t->payment_via ?? '—') }}</td>
                <td>{{ $t->notes ?? '—' }}</td>
                <td class="text-end">{{ number_format($t->amount ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($t->tax ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($t->net_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-end">Total Paid</td>
                <td class="text-end">{{ number_format($totalPaid, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
        <div style="padding:8px 10px; color:#888; font-style:italic;">No payment transactions found.</div>
    @endif

    {{-- Payment Summary --}}
    <div class="summary-wrap">
        <div class="summary-box">
            <table>
                <tr>
                    <td>Total Charges</td>
                    <td>{{ number_format($totalCharges, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Paid</td>
                    <td class="balance-paid">{{ number_format($totalPaid, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Balance Due</td>
                    <td>{{ number_format(max($balance, 0), 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{-- ── Documents ── --}}
@if($opdPatient->documents->isNotEmpty())
<div class="section">
    <div class="section-title">Uploaded Documents</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:35%;">Title</th>
                <th>Remarks</th>
                <th style="width:16%;">Uploaded On</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opdPatient->documents as $doc)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $doc->title ?: '—' }}</td>
                <td>{{ $doc->remarks ?: '—' }}</td>
                <td>{{ $doc->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Footer ── --}}
<div class="bill-footer">
    <div>
        <div>This bill is computer generated.</div>
        <div style="margin-top:4px; color:#aaa;">Generated on {{ now()->format('d M Y, h:i A') }}</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div>Authorized Signature</div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
</script>
</body>
</html>
