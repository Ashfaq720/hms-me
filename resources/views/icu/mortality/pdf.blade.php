@php
    $a = $admission->mortalityAudit;
    $patient = $admission->patient;
    $company = company_info();
    $companyName = $company['name'] ?? setting('company_name') ?? config('app.name');
    $companyAddress = $company['address'] ?? setting('company_address');
    $companyPhone = $company['phone'] ?? setting('company_phone');
    $companyEmail = $company['email'] ?? setting('company_email');
    $printedAt = now()->format('Y-m-d H:i');

    $value = fn ($text) => filled($text) ? $text : '-';
    $dateTime = fn ($date) => $date ? $date->format('Y-m-d H:i') : '-';
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mortality Audit - {{ $admission->icu_case_id }}</title>
    <style>
        body {
            color: #172033;
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.45;
        }

        .header {
            border-bottom: 2px solid #1f6f78;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .hospital-name {
            color: #123b46;
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
            text-align: center;
        }

        .hospital-meta {
            color: #5c6675;
            font-size: 9pt;
            margin-top: 3px;
            text-align: center;
        }

        .title-band {
            background: #123b46;
            color: #ffffff;
            margin: 10px 0 12px;
            padding: 9px 12px;
        }

        .title {
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: .4px;
            margin: 0;
            text-transform: uppercase;
        }

        .subtitle {
            color: #d7eef2;
            font-size: 9pt;
            margin-top: 2px;
        }

        .badge {
            background: #eef7f8;
            border: 1px solid #9bc8cf;
            color: #123b46;
            font-size: 9pt;
            font-weight: bold;
            padding: 4px 8px;
        }

        .section {
            border: 1px solid #d7dee8;
            margin-bottom: 10px;
        }

        .section-title {
            background: #eef3f6;
            border-bottom: 1px solid #d7dee8;
            color: #123b46;
            font-size: 10pt;
            font-weight: bold;
            padding: 7px 9px;
            text-transform: uppercase;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .info td {
            border-bottom: 1px solid #edf0f4;
            padding: 7px 9px;
            vertical-align: top;
            width: 25%;
        }

        .label {
            color: #687384;
            display: block;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-value {
            color: #172033;
            display: block;
            font-size: 10pt;
            margin-top: 2px;
        }

        .narrative {
            padding: 8px 9px;
            white-space: pre-line;
        }

        .review-table th,
        .review-table td {
            border: 1px solid #dfe5ec;
            padding: 7px 9px;
            text-align: left;
            vertical-align: top;
        }

        .review-table th {
            background: #f5f8fa;
            color: #4e5968;
            font-size: 8.5pt;
            width: 30%;
        }

        .signature-table {
            margin-top: 24px;
        }

        .signature-table td {
            padding-top: 24px;
            text-align: center;
            width: 33.33%;
        }

        .signature-line {
            border-top: 1px solid #4e5968;
            display: inline-block;
            padding-top: 5px;
            width: 150px;
        }

        .footer-note {
            color: #687384;
            font-size: 8pt;
            margin-top: 8px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="hospital-name">{{ $companyName }}</h1>
        <div class="hospital-meta">
            {{ $value($companyAddress) }}
            @if ($companyPhone) | {{ $companyPhone }} @endif
            @if ($companyEmail) | {{ $companyEmail }} @endif
        </div>
    </div>

    <div class="title-band">
        <table>
            <tr>
                <td>
                    <div class="title">Mortality Audit</div>
                    <div class="subtitle">ICU clinical mortality review and audit record</div>
                </td>
                <td style="text-align:right; width: 35%;">
                    <span class="badge">{{ $value($a->audit_status) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Patient and Case Details</div>
        <table class="info">
            <tr>
                <td>
                    <span class="label">Patient Name</span>
                    <span class="text-value">{{ $value($patient?->patient_name) }}</span>
                </td>
                <td>
                    <span class="label">MRN</span>
                    <span class="text-value">{{ $value($patient?->mrn) }}</span>
                </td>
                <td>
                    <span class="label">ICU Case ID</span>
                    <span class="text-value">{{ $value($admission->icu_case_id) }}</span>
                </td>
                <td>
                    <span class="label">ICU Type</span>
                    <span class="text-value">{{ $value($admission->icu_type) }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Admission Time</span>
                    <span class="text-value">{{ $dateTime($admission->admission_time) }}</span>
                </td>
                <td>
                    <span class="label">Death Time</span>
                    <span class="text-value">{{ $dateTime($a->death_time) }}</span>
                </td>
                <td>
                    <span class="label">Outcome</span>
                    <span class="text-value">{{ $value($admission->outcome) }}</span>
                </td>
                <td>
                    <span class="label">Printed At</span>
                    <span class="text-value">{{ $printedAt }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Clinical Mortality Details</div>
        <table class="review-table">
            <tr>
                <th>Declared By</th>
                <td>{{ $value($a->declaredByDoctor?->name) }}</td>
                <th>Code Blue Event</th>
                <td>{{ $a->codeBlueEvent ? $a->codeBlueEvent->event_no : '-' }}</td>
            </tr>
            <tr>
                <th>Final Diagnosis</th>
                <td colspan="3">{{ $value($a->final_diagnosis) }}</td>
            </tr>
            <tr>
                <th>Cause of Death</th>
                <td colspan="3">{{ $value($a->cause_of_death) }}</td>
            </tr>
            <tr>
                <th>Body Handover To</th>
                <td colspan="3">{{ $value($a->body_handover_to) }}</td>
            </tr>
        </table>
        <div class="narrative">
            <span class="label">Resuscitation Details</span>
            {{ $value($a->resuscitation_details) }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Audit Committee Review</div>
        <table class="review-table">
            <tr>
                <th>Death Reviewed By</th>
                <td>{{ $value($a->deathReviewedByDoctor?->name) }}</td>
                <th>Review Date</th>
                <td>{{ $dateTime($a->review_date) }}</td>
            </tr>
            <tr>
                <th>Primary Cause</th>
                <td colspan="3">{{ $value($a->primary_cause) }}</td>
            </tr>
            <tr>
                <th>Preventability</th>
                <td>{{ $value($a->preventability) }}</td>
                <th>Review Completed At</th>
                <td>{{ $dateTime($a->reviewed_at) }}</td>
            </tr>
            <tr>
                <th>Contributing Factors</th>
                <td colspan="3">{{ $value($a->contributing_factors) }}</td>
            </tr>
            <tr>
                <th>Clinical Remarks</th>
                <td colspan="3">{{ $value($a->clinical_remarks) }}</td>
            </tr>
            <tr>
                <th>Committee Remarks</th>
                <td colspan="3">{{ $value($a->committee_remarks) }}</td>
            </tr>
        </table>
    </div>

    <table class="signature-table">
        <tr>
            <td><span class="signature-line">Prepared By</span></td>
            <td><span class="signature-line">Reviewed By</span></td>
            <td><span class="signature-line">Authorized Signature</span></td>
        </tr>
    </table>

    <div class="footer-note">Generated from HMS ICU module</div>
</body>
</html>
