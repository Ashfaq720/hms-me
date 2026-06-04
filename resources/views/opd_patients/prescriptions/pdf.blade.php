<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Prescription - {{ $prescription->prescription_no }}</title>
    <style>
        @page {
            margin: 12mm 10mm 20mm 10mm;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* ── Header ── */
        .header {
            background: #213f5c;
            color: #fff;
            padding: 16px 20px;
            border-radius: 8px;
        }

        .header table {
            width: 100%;
        }

        .hospital-name {
            font-size: 20px;
            font-weight: bold;
            color: #fff;
        }

        .hospital-sub {
            font-size: 11px;
            color: #ccc;
            margin-top: 2px;
        }

        .doctor-info {
            text-align: right;
        }

        .doctor-name {
            font-size: 14px;
            font-weight: bold;
            color: #fff;
        }

        .doctor-desg {
            font-size: 11px;
            color: #ccc;
        }

        /* ── Patient Bar ── */
        .patient-bar {
            background: #e8e8e8;
            border-radius: 8px;
            padding: 12px 20px;
            margin-top: 10px;
        }

        .patient-bar table {
            width: 100%;
        }

        .patient-bar .label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.5px;
        }

        .patient-bar .value {
            font-size: 13px;
            font-weight: 600;
            margin-top: 2px;
        }

        /* ── Prescription Badge ── */
        .rx-badge {
            display: inline-block;
            background: #f5c518;
            color: #333;
            font-weight: bold;
            font-size: 11px;
            padding: 2px 10px;
            border-radius: 4px;
        }

        /* ── Section Title ── */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 16px 0 4px;
            padding-bottom: 4px;
            border-bottom: 2px solid #f5c518;
            color: #213f5c;
        }

        /* ── Content Tables ── */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .content-table th {
            background: #f0f0f0;
            text-align: left;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            border-bottom: 1px solid #ddd;
        }

        .content-table td {
            padding: 6px 10px;
            font-size: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        /* ── Notes Box ── */
        .notes-box {
            background: #fafafa;
            border-left: 4px solid #f5c518;
            padding: 10px 16px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 12px;
        }

        .notes-box .notes-label {
            font-size: 12px;
            font-weight: bold;
            color: #213f5c;
            margin-bottom: 4px;
        }

        .notes-box .notes-content {
            font-size: 12px;
            color: #333;
        }

        /* ── Two Column Layout ── */
        .two-col {
            width: 100%;
        }

        .two-col td {
            vertical-align: top;
            padding: 0;
        }

        .col-left {
            width: 38%;
            padding-right: 12px;
        }

        .col-right {
            width: 62%;
            padding-left: 12px;
        }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            font-size: 10px;
            color: #999;
            text-align: center;
        }

        .signature-area {
            margin-top: 60px;
            text-align: right;
            padding-right: 20px;
        }

        .signature-line {
            border-top: 1px solid #333;
            display: inline-block;
            width: 200px;
            padding-top: 4px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- ══════════ HEADER ══════════ --}}
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="hospital-name">{{ setting('company_name') }}</div>
                    @if ($prescription->doctor && $prescription->doctor->department)
                        <div class="hospital-sub">{{ $prescription->doctor->department->name ?? '' }}</div>
                    @endif
                    <div class="hospital-sub">{{ setting('company_address') }} &bull; {{ setting('company_phone') }}</div>
                </td>
                <td class="doctor-info">
                    @if ($prescription->doctor)
                        <div class="doctor-name">{{ $prescription->doctor->name }}</div>
                        <div class="doctor-desg">{{ $prescription->doctor->designation->name ?? '' }}</div>
                        <div class="doctor-desg">Reg. No: {{ $prescription->doctor->registration_no ?? '-' }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- ══════════ PATIENT INFO BAR ══════════ --}}
    <div class="patient-bar">
        <table>
            <tr>
                <td>
                    <div class="label">Ipd No</div>
                    <div class="value">{{ $opdPatient->case_id ?? '-' }}</div>
                </td>
                <td>
                    <div class="label">Patient Name</div>
                    <div class="value">{{ $opdPatient->patient->patient_name ?? '-' }}</div>
                </td>
                <td>
                    <div class="label">Age / Gender</div>
                    <div class="value">
                        @if ($opdPatient->patient->dob)
                            {{ calculateAgeFromDob($opdPatient->patient->dob) ?? '' }}
                        @else
                            N/A
                        @endif
                        / {{ ucfirst($opdPatient->patient->gender ?? '-') }}
                    </div>
                </td>
                <td>
                    <div class="label">Contact</div>
                    <div class="value">{{ $opdPatient->patient->mobileno ?? '-' }}</div>
                </td>
                <td>
                    <div class="label">Date</div>
                    <div class="value">{{ $prescription->date ? $prescription->date->format('d M Y') : 'N/A' }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="label">Prescription No</div>
                    <div class="value"><span class="rx-badge">{{ $prescription->prescription_no }}</span></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ══════════ TWO COLUMN LAYOUT ══════════ --}}
    <table class="two-col">
        <tr>
            {{-- LEFT COLUMN --}}
            <td class="col-left">

                {{-- Symptoms --}}
                <div class="section-title">Symptoms</div>
                @if ($prescription->symptoms->count())
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">SN</th>
                                <th>Symptom</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prescription->symptoms as $ps)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $ps->symptom->name ?? 'N/A' }}
                                        @if ($ps->note)
                                            <br><small style="color: #666;">{{ $ps->note }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="color: #999; font-size: 11px;">No symptoms recorded.</p>
                @endif

                {{-- Lab Investigations --}}
                <div class="section-title">Lab Investigations</div>
                @if ($prescription->labInvestigations->count())
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">SN</th>
                                <th>Investigation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prescription->labInvestigations as $pl)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $pl->labInvestigation->name ?? 'N/A' }}
                                        @if ($pl->note)
                                            <br><small style="color: #666;">{{ $pl->note }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="color: #999; font-size: 11px;">No lab investigations recorded.</p>
                @endif

            </td>

            {{-- RIGHT COLUMN --}}
            <td class="col-right">

                {{-- Diagnosis / Findings --}}
                <div class="section-title">Rx Diagnosis &amp; Clinical Notes</div>
                <div class="notes-box">
                    <div class="notes-content">{{ $prescription->findings ?? 'N/A' }}</div>
                </div>

                {{-- Medicines (Tx) --}}
                <div class="section-title">Tx - Medicines</div>
                @if ($prescription->medicines->count())
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">SN</th>
                                <th>Medicine</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prescription->medicines as $pm)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $pm->medicine->medicine_name ?? $pm->medicine_name ?? '-' }}</td>
                                    <td>{{ $pm->dosage ?? '-' }}</td>
                                    <td>{{ $pm->frequency ?? '-' }}</td>
                                    <td>{{ $pm->duration ?? '-' }}</td>
                                    <td>{{ $pm->note ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="color: #999; font-size: 11px;">No medicines recorded.</p>
                @endif

                {{-- Advice --}}
                @if ($prescription->advice)
                    <div class="section-title">Advice</div>
                    <div class="notes-box">
                        <div class="notes-content">{{ $prescription->advice }}</div>
                    </div>
                @endif

                {{-- Next Visit --}}
                @if ($prescription->next_visit)
                    <div class="notes-box" style="margin-top: 20px;">
                        <div class="section-title">Next Visit</div>
                        <div class="notes-content">{{ $prescription->next_visit->format('d M Y') }}</div>
                    </div>
                @endif

            </td>
        </tr>
    </table>

    {{-- ══════════ SIGNATURE ══════════ --}}
    <div class="signature-area">
        <div class="signature-line">
            {{ $prescription->doctor->name ?? 'Doctor' }}
        </div>
    </div>

    {{-- ══════════ FOOTER ══════════ --}}
    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} &bull; {{ setting('company_name') }}
    </div>

</body>

</html>
