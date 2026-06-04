<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>OPD Token</title>
    <style>
        @page {
            margin: 2mm;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .token-wrapper {
            width: 100%;
            border: 1px dashed #000;
            padding: 8px 6px;
            box-sizing: border-box;
        }

        .hospital-name {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .hospital-address {
            text-align: center;
            font-size: 8px;
            margin-bottom: 6px;
        }

        .hospital-sub {
            text-align: center;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .token-no-box {
            text-align: center;
            border: 1px solid #000;
            padding: 6px 4px;
            margin-bottom: 8px;
        }

        .token-label {
            font-size: 8px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .token-no {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            line-height: 1.1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        td {
            vertical-align: top;
            padding: 3px 0;
        }

        td:first-child {
            width: 34%;
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .notice {
            text-align: center;
            font-size: 9px;
            line-height: 1.4;
            margin-top: 6px;
        }

        .footer {
            text-align: center;
            font-size: 8px;
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <div class="token-wrapper">

        <div class="hospital-name">{{ setting('company_name') }}</div>
        <div class="hospital-address">{{ setting('company_address') }}</div>
        <div class="hospital-sub">Walk In Token</div>

        <div class="token-no-box">
            <div class="token-label">Token Number</div>
            <div class="token-no">{{ $opd->token_no }}</div>
        </div>

        <table>
            <tr>
                <td>Patient Name: </td>
                <td>: {{ $opd->patient->patient_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Mobile No. : </td>
                <td>: {{ $opd->patient->mobileno ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Doctor Name: </td>
                <td>: {{ $opd->doctor->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Department: </td>
                <td>: {{ $opd->department->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Date: </td>
                <td>: {{ \Carbon\Carbon::parse($opd->date)->format('d M Y') }}</td>
            </tr>
            <tr>
                <td>Print Time: </td>
                <td>: {{ now()->format('h:i A') }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <div class="notice">
            Please wait for your token number.<br>
            Show this token during doctor consultation.
        </div>

        <div class="footer">
            Thank You
        </div>
    </div>
</body>

</html>
