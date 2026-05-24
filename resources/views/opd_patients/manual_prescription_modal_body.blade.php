@php
    $p = $patient->patient;
    $doctor = $patient->doctor;

    $ageText = '';
    if (!empty($p->dob)) {
        try {
            $dob = \Carbon\Carbon::parse($p->dob);
            $ageText = $dob->diff(\Carbon\Carbon::now())->format('%y Year, %m Month, %d Day');
        } catch (\Exception $e) {
            $ageText = $p->age ?? '';
        }
    } else {
        $ageText = $p->age ?? '';
    }
@endphp

<style>
    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        background: #fff;
        font-family: Arial, Helvetica, sans-serif;
    }

    .prescription-print {
        width: 100%;
        min-height: 277mm;
        /* A4 height minus page margin area */
        display: flex;
        flex-direction: column;
        color: #000;
        background: #fff;
        padding: 0;
        margin: 0;
        overflow: hidden;
    }

    .prescription-print * {
        box-sizing: border-box;
    }

    .prescription-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 6px;
    }

    .prescription-header-left {
        width: 60%;
    }

    .prescription-header-right {
        width: 40%;
        text-align: right;
        font-size: 11px;
        line-height: 1.35;
        font-weight: 600;
    }

    .hospital-mini-logo {
        display: inline-block;
        background: #d9d9d9;
        border: 1px solid #8a8a8a;
        color: #333;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        margin-bottom: 4px;
    }

    .hospital-name {
        margin: 0;
        font-size: 20px;
        line-height: 1.1;
        font-weight: 700;
    }

    .prescription-title {
        background: #000;
        color: #fff;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        padding: 3px 8px;
        margin: 8px 0 8px;
    }

    .top-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 11px;
    }

    .top-meta-left {
        width: 70%;
    }

    .top-meta-right {
        width: 30%;
        text-align: right;
        font-weight: 600;
    }

    .line-separator {
        border-top: 1px solid #aaa;
        margin: 6px 0 8px;
    }

    .patient-grid {
        font-size: 11px;
        line-height: 1.5;
    }

    .patient-row {
        display: flex;
        width: 100%;
        margin-bottom: 2px;
    }

    .patient-cell {
        padding-right: 8px;
    }

    .w-33 {
        width: 33.33%;
    }

    .w-50 {
        width: 50%;
    }

    .w-100 {
        width: 100%;
    }

    .field-label {
        display: inline-block;
        min-width: 88px;
    }

    .field-colon {
        display: inline-block;
        width: 10px;
        text-align: center;
    }

    .field-value {
        display: inline;
    }

    .writing-space {
        flex: 1;
        border-top: 1px solid #aaa;
        margin-top: 8px;
        min-height: 0;
    }

    .print-footer {
        margin-top: auto;
        padding-top: 2px;
        font-size: 10px;
    }

    .modal-content,
    .modal-body {
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
        background: #fff !important;
    }

    @media print {

        html,
        body {
            width: 210mm;
            height: 297mm;
            overflow: hidden;
        }

        .modal-header,
        .modal-footer,
        .btn,
        .no-print {
            display: none !important;
        }

        .prescription-print {
            min-height: 277mm;
        }
    }
</style>

<div class="prescription-print">
    <div class="prescription-header">
        <div class="prescription-header-left">
            <div class="hospital-mini-logo">SMART HOSPITAL</div>
            <h2 class="hospital-name">Smart Hospital &amp; Research Center</h2>
        </div>
        <div class="prescription-header-right">
            <div>Address: 25 Kings Street, CA</div>
            <div>Phone No.: 89562423934</div>
            <div>Email: smarthospitalrc@gmail.com</div>
            <div>Website: www.smart-hospital.in</div>
        </div>
    </div>

    <div class="prescription-title">OPD Prescription</div>

    <div class="top-meta">
        <div class="top-meta-left">
            <div>OPD No<span style="font-weight:700;">{{ $patient->opd_no ?? 'OPDN' . $patient->id }}</span></div>
            <div>OPD Checkup ID<span
                    style="font-weight:700;">{{ $patient->checkup_id ?? 'UDCKHID' . $patient->id }}</span></div>
        </div>
        <div class="top-meta-right">
            Date : {{ now()->format('m/d/Y') }}
        </div>
    </div>

    <div class="line-separator"></div>

    <div class="patient-grid">
        <div class="patient-row">
            <div class="patient-cell w-33">
                <span class="field-label">Patient Name</span><span class="field-colon">:</span>
                <span
                    class="field-value">{{ $p->patient_name ?? 'N/A' }}{{ !empty($p->id) ? ' (' . $p->id . ')' : '' }}</span>
            </div>
            <div class="patient-cell w-33">
                <span class="field-label">Age</span><span class="field-colon">:</span>
                <span class="field-value">{{ $ageText ?: 'N/A' }}</span>
            </div>
            <div class="patient-cell w-33">
                <span class="field-label">Gender</span><span class="field-colon">:</span>
                <span class="field-value">{{ $p->gender ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="patient-row">
            <div class="patient-cell w-33">
                <span class="field-label">Consultant Doctor</span><span class="field-colon">:</span>
                <span class="field-value">
                    {{ trim(($doctor->name ?? '') . ' ' . ($doctor->surname ?? '')) }}
                    {{ $doctor ? '(' . $doctor->id . ')' : '' }}
                </span>
            </div>
            <div class="patient-cell w-33">
                <span class="field-label">Address</span><span class="field-colon">:</span>
                <span class="field-value">{{ $p->address ?? '' }}</span>
            </div>
            <div class="patient-cell w-33">
                <span class="field-label">Blood Group</span><span class="field-colon">:</span>
                <span class="field-value">{{ $p->blood_group ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="patient-row">
            <div class="patient-cell w-100">
                <span class="field-label">Known Allergies</span><span class="field-colon">:</span>
                <span class="field-value">{{ $patient->known_allergies ?? '' }}</span>
            </div>
        </div>
    </div>

    <div class="writing-space"></div>

    <div class="print-footer">
        This invoice is printed electronically, so no signature is required
    </div>
</div>
