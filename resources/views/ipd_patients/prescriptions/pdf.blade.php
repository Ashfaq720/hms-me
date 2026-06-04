<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Prescription - {{ $prescription->prescription_no }}</title>

    <style>
        @page {
            margin: 5mm 10mm 5mm 10mm;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .header {
            background: #fff;
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
            color: #000;
        }

        .hospital-sub {
            font-size: 11px;
            color: #000;
            margin-top: 2px;
        }

        .doctor-info {
            text-align: left;
        }

        .doctor-name {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        .doctor-desg {
            font-size: 11px;
            color: #000;
        }

        .patient-bar {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .patient-bar table {
            width: 100%;
            border-collapse: collapse;
        }

        .patient-bar td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 12px;
            vertical-align: top;
        }

        .patient-bar .label {
            font-weight: bold;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            padding-bottom: 4px;
            border-bottom: 2px solid #f5c518;
            color: #213f5c;
        }

        .left-split {
            width: 100%;
            height: 700px;
            border-collapse: collapse;
        }

        .left-split .left-half {
            height: 350px;
            vertical-align: top;
            padding: 0;
        }

        .left-split .left-half-2 {
            height: 350px;
            vertical-align: bottom;
            padding: 0;
        }

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

        .notes-box {
            background: #fafafa;
            padding: 10px 16px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 12px;
        }

        .notes-box .notes-content {
            font-size: 12px;
            color: #333;
        }

        .two-col {
            width: 100%;
        }

        .two-col td {
            vertical-align: top;
            padding: 0;
        }

        .col-left {
            width: 35%;
            padding-right: 12px;
        }

        .col-middle {
            width: 5%;
            padding: 0 12px;
        }

        .col-right {
            width: 60%;
            padding-left: 12px;
            padding-bottom: 250px;
        }

        .col-right .section-title {
            margin-top: 20px;
            margin-bottom: 12px;
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .col-right .section-title:first-child {
            margin-top: 0;
        }

        .medicine-box {
            max-height: 330px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .bottom-right-section {
            position: fixed;
            bottom: 145px;
            right: 20px;
            width: 58%;
        }

        .bottom-right-section .section-title {
            font-size: 14px;
            font-weight: bold;
            padding-bottom: 4px;
            border-bottom: 2px solid #f5c518;
            color: #213f5c;
            margin-top: 8px;
            margin-bottom: 6px;
        }

        .bottom-right-section .notes-box {
            margin-bottom: 8px;
            padding: 8px 14px;
        }

        .signature-area {
            position: fixed;
            bottom: 85px;
            right: 20px;
            text-align: right;
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

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px dashed #000;
            padding-top: 2px;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">
        <table>
            <tr>
                <td width="40%" class="doctor-info">
                    @if ($prescription->doctor)
                        <div class="doctor-name">{{ $prescription->doctor?->name }}</div>
                        <div class="doctor-desg">{{ $prescription->doctor?->designation?->name ?? '' }}</div>
                        <div class="doctor-desg">{{ $prescription->doctor?->department?->name ?? '' }}</div>
                        <div class="doctor-desg">{{ $prescription->doctor?->qualification ?? '' }}</div>
                    @endif
                </td>

                <td width="30%">
                    @php
                        $logoPath = public_path(setting('company_logo'));
                    @endphp

                    @if (setting('company_logo') && file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="{{ setting('company_name') }} Logo"
                            style="max-height: 60px;">
                    @endif
                </td>

                <td width="40%">
                    <div class="doctor-desg">{{ $prescription->doctor?->work_history ?? '' }}</div>
                    <div class="doctor-desg"><b>Reg. No : </b> {{ $prescription->doctor?->registration_no ?? '' }}</div>
                    <div class="doctor-desg"><b>License No :</b> {{ $prescription->doctor?->license_no ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="patient-bar">
        <table>
            <tr>
                <td><span class="label">Ipd NO:</span> {{ $ipdPatient->ipd_no ?? '-' }}</td>
                <td><span class="label">Patient Name:</span> {{ $ipdPatient->patient->patient_name ?? '-' }}</td>
                <td>
                    <span class="label">Age:</span>
                    @if ($ipdPatient->patient->dob)
                        {{ calculateAgeFromDob($ipdPatient->patient->dob) ?? '' }}
                    @else
                        N/A
                    @endif
                </td>
                <td><span class="label">Gender:</span> {{ ucfirst($ipdPatient->patient->gender ?? '-') }}</td>
                <td><span class="label">Contact:</span> {{ $ipdPatient->patient->mobileno ?? '-' }}</td>
            </tr>

            <tr>
                <td>
                    <span class="label">Date:</span>
                    {{ $prescription->date ? $prescription->date->format('d M Y') : 'N/A' }}
                </td>
                <td colspan="4">
                    <span class="label">Prescription NO:</span> {{ $prescription->prescription_no }}
                </td>
            </tr>
        </table>
    </div>

    <table class="two-col">
        <tr>
            <td class="col-left">
                <table class="left-split">
                    <tr>
                        <td class="left-half">
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
                                                        <br>
                                                        <small style="color: #666;">{{ $ps->note }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p style="color: #999; font-size: 11px;">No symptoms recorded.</p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="left-half-2">
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
                                                        <br>
                                                        <small style="color: #666;">{{ $pl->note }}</small>
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
                    </tr>
                </table>
            </td>

            <td class="col-middle"></td>

            <td class="col-right">

                <div class="section-title">Tx - Diagnosis &amp; Clinical Notes</div>
                <div class="notes-box">
                    <div class="notes-content">{{ $prescription->findings ?? 'N/A' }}</div>
                </div>

                <div style="height: 160px; line-height: 160px;">&nbsp;</div>

                <div class="section-title">Rx - Medicines</div>

                <div class="medicine-box">
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
                                        <td>{{ $pm->medicine->medicine_name ?? ($pm->medicine_name ?? '-') }}</td>
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
                </div>

            </td>
        </tr>
    </table>

    <div class="bottom-right-section">
        @if ($prescription->advice)
            <div class="section-title">Advice</div>
            <div class="notes-box">
                <div class="notes-content">{{ $prescription->advice }}</div>
            </div>
        @endif

        @if ($prescription->next_visit)
            <div class="section-title">Next Visit</div>
            <div class="notes-box">
                <div class="notes-content">{{ $prescription->next_visit->format('d M Y') }}</div>
            </div>
        @endif
    </div>

    <div class="signature-area">
        <div class="signature-line">
            {{ $prescription->doctor->name ?? 'Doctor' }}
        </div>
    </div>

    <div class="footer">
        <div class="hospital-name">{{ setting('company_name') }}</div>
        <div class="hospital-sub">{{ setting('company_address') }}</div>

        @if (setting('company_phone'))
            <div class="hospital-sub">&#9742; {{ setting('company_phone') }}</div>
        @endif

        Generated on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>
