@php
    $p           = $ipdPatient->patient;
    $bed         = optional($ipdPatient->bedAllocations->sortByDesc('id')->first())->bed;
    $admittedAt  = $ipdPatient->admission_date ? \Illuminate\Support\Carbon::parse($ipdPatient->admission_date) : null;
    $dischargeAt = $ipdPatient->possible_discharge_date ? \Illuminate\Support\Carbon::parse($ipdPatient->possible_discharge_date) : null;
    $companyName    =  setting('company_name') ?? '';
    $companyAddress =  setting('company_address') ?? '';
    $companyPhone   =  setting('company_phone') ?? '';
    $companyEmail   =  setting('company_email') ?? '';
    $companyWebsite =  setting('company_website') ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ipd Admission Slip - {{ $p->patient_name ?? '' }}</title>
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
        .slip-title {
            background: #0d6efd;
            color: #fff;
            text-align: center;
            padding: 6px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 10px 0 14px;
        }
        .section { margin-top: 14px; page-break-inside: avoid; }
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
        .info-item strong { min-width: 130px; color: #475467; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .sign-block {
            width: 30%;
            text-align: center;
            border-top: 1px dashed #888;
            padding-top: 6px;
            font-size: 11px;
            color: #555;
        }
        .footer-note {
            margin-top: 26px;
            font-size: 10.5px;
            color: #888;
            text-align: center;
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
        <h2>{{ $companyName }}</h2>
        <div class="meta">
            {{ $companyAddress }} &nbsp;|&nbsp; {{ $companyPhone }} &nbsp;|&nbsp; {{ $companyEmail }}
        </div>
        @if ($companyWebsite)
            <div class="meta">{{ $companyWebsite }}</div>
        @endif
    </div>
    <div class="meta text-end">
        Printed on: {{ now()->format('d M Y, h:i A') }}
    </div>
</div>

<div class="slip-title">Ipd ADMISSION SLIP</div>

<div class="section">
    <h4>Admission Details</h4>
    <div class="info-grid">
        <div class="info-item"><strong>Ipd No:</strong> {{ $ipdPatient->ipd_no ?? '-' }}</div>
        <div class="info-item"><strong>Case ID:</strong> {{ $ipdPatient->case_id ?? '-' }}</div>
        <div class="info-item"><strong>Admission Date:</strong> {{ $admittedAt ? $admittedAt->format('d M Y, h:i A') : '-' }}</div>
        <div class="info-item"><strong>Possible Discharge:</strong> {{ $dischargeAt ? $dischargeAt->format('d M Y, h:i A') : '-' }}</div>
        <div class="info-item"><strong>Admission Type:</strong> {{ ucfirst($ipdPatient->admission_type ?? '-') }}</div>
        <div class="info-item"><strong>Status:</strong> {{ $ipdPatient->status ?? '-' }}</div>
    </div>
</div>

<div class="section">
    <h4>Patient Information</h4>
    <div class="info-grid">
        <div class="info-item"><strong>MRN:</strong> {{ $p->mrn ?? '-' }}</div>
        <div class="info-item"><strong>Name:</strong> {{ $p->patient_name ?? '-' }}</div>
        <div class="info-item"><strong>Mobile:</strong> {{ $p->mobileno ?? '-' }}</div>
        <div class="info-item"><strong>Gender:</strong> {{ ucfirst($p->gender ?? '-') }}</div>
        <div class="info-item"><strong>DOB:</strong> {{ $p->dob ? \Illuminate\Support\Carbon::parse($p->dob)->format('d M Y') : '-' }}</div>
        <div class="info-item"><strong>Blood Group:</strong> {{ $p->blood_group ?? '-' }}</div>
        <div class="info-item"><strong>Address:</strong> {{ $p->address ?? '-' }}</div>
        <div class="info-item"><strong>Allergies:</strong> {{ $p->known_allergies ?? '-' }}</div>
    </div>
</div>

<div class="section">
    <h4>Treating Doctor & Department</h4>
    <div class="info-grid">
        <div class="info-item"><strong>Doctor:</strong> {{ $ipdPatient->doctor->name ?? '-' }}</div>
        <div class="info-item"><strong>Designation:</strong> {{ $ipdPatient->doctor->designation->name ?? '-' }}</div>
        <div class="info-item"><strong>Department:</strong> {{ $ipdPatient->department->name ?? '-' }}</div>
        <div class="info-item"><strong>Doctor Dept:</strong> {{ $ipdPatient->doctor->department->name ?? '-' }}</div>
    </div>
</div>

@if ($bed)
<div class="section">
    <h4>Bed Allocation</h4>
    <div class="info-grid">
        <div class="info-item"><strong>Bed:</strong> {{ $bed->name ?? '-' }}</div>
        <div class="info-item"><strong>Bed Type:</strong> {{ $bed->bedType->name ?? '-' }}</div>
        <div class="info-item"><strong>Bed Group:</strong> {{ $bed->bedGroup->name ?? '-' }}</div>
        <div class="info-item"><strong>Daily Rent:</strong> {{ number_format((float) ($bed->rent ?? 0), 2) }}</div>
    </div>
</div>
@endif

@if (!empty($ipdPatient->patient_history) || !empty($ipdPatient->remarks))
<div class="section">
    <h4>Notes</h4>
    @if (!empty($ipdPatient->patient_history))
        <div style="margin-top:6px;"><strong>Patient History:</strong> {{ $ipdPatient->patient_history }}</div>
    @endif
    @if (!empty($ipdPatient->remarks))
        <div style="margin-top:6px;"><strong>Remarks:</strong> {{ $ipdPatient->remarks }}</div>
    @endif
</div>
@endif

<div class="signatures">
    <div class="sign-block">Patient / Guardian Signature</div>
    <div class="sign-block">Receptionist</div>
    <div class="sign-block">Authorised Signatory</div>
</div>

<div class="footer-note">
    This admission slip is generated electronically and is valid without a physical signature.
</div>

<script>
    window.addEventListener('load', function () { setTimeout(() => window.print(), 300); });
</script>
</body>
</html>
