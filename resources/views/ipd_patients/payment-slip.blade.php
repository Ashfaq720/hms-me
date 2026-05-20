@php
    $p          = $ipdPatient->patient;
    $amount     = (float) ($transaction->amount ?? 0);
    $vat        = (float) ($transaction->vat ?? 0);
    $tax        = (float) ($transaction->tax ?? 0);
    $discount   = (float) ($transaction->discount ?? 0);
    $netAmount  = (float) ($transaction->net_amount ?? 0);
    $paymentVia = strtolower(trim($transaction->payment_via ?? ''));
    $paidOn     = $transaction->payment_date ? \Illuminate\Support\Carbon::parse($transaction->payment_date) : null;
    $company        = function_exists('company_info') ? company_info() : [];
    $companyName    = $company['name']    ?? setting('company_name');
    $companyAddress = $company['address'] ?? setting('company_address');
    $companyPhone   = $company['phone']   ?? setting('company_phone');
    $companyEmail   = $company['email']   ?? setting('company_email');
    $companyWebsite = $company['website'] ?? setting('company_website');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ipd Initial Payment Slip - {{ $p->patient_name ?? '' }}</title>
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
            border-bottom: 2px solid #198754;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header h2 { color: #198754; }
        .meta { font-size: 11px; color: #555; }
        .slip-title {
            background: #198754;
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
            border-left: 4px solid #198754;
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
        .totals {
            width: 320px;
            margin-left: auto;
            margin-top: 8px;
        }
        .totals td { border: 0; padding: 4px 8px; }
        .totals .grand {
            border-top: 2px solid #198754;
            font-weight: bold;
            font-size: 13px;
            color: #198754;
        }
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
            background: #198754;
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

<div class="slip-title">Ipd INITIAL PAYMENT RECEIPT</div>

<div class="section">
    <h4>Receipt Details</h4>
    <div class="info-grid">
        <div class="info-item"><strong>Invoice No:</strong> {{ $transaction->invoice_no ?? '-' }}</div>
        <div class="info-item"><strong>Receipt Type:</strong> {{ $transaction->type ?? 'Advance' }}</div>
        <div class="info-item"><strong>Payment Date:</strong> {{ $paidOn ? $paidOn->format('d M Y') : '-' }}</div>
        <div class="info-item"><strong>Payment Mode:</strong> {{ ucfirst($transaction->payment_via ?? '-') }}</div>
        <div class="info-item"><strong>Status:</strong> {{ $transaction->status ?? '-' }}</div>
        <div class="info-item"><strong>Received By:</strong> {{ $transaction->received_by ?? '-' }}</div>
    </div>
</div>

<div class="section">
    <h4>Patient & Case</h4>
    <div class="info-grid">
        <div class="info-item"><strong>Ipd No:</strong> {{ $ipdPatient->ipd_no ?? '-' }}</div>
        <div class="info-item"><strong>Case ID:</strong> {{ $ipdPatient->case_id ?? '-' }}</div>
        <div class="info-item"><strong>MRN:</strong> {{ $p->mrn ?? '-' }}</div>
        <div class="info-item"><strong>Name:</strong> {{ $p->patient_name ?? '-' }}</div>
        <div class="info-item"><strong>Mobile:</strong> {{ $p->mobileno ?? '-' }}</div>
        <div class="info-item"><strong>Gender:</strong> {{ ucfirst($p->gender ?? '-') }}</div>
        <div class="info-item"><strong>Doctor:</strong> {{ $ipdPatient->doctor->name ?? '-' }}</div>
        <div class="info-item"><strong>Department:</strong> {{ $ipdPatient->department->name ?? '-' }}</div>
    </div>
</div>

<div class="section">
    <h4>Payment Breakdown</h4>
    <table>
        <thead>
            <tr>
                <th width="6%">#</th>
                <th>Description</th>
                <th width="20%" class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Ipd Advance Payment - {{ $ipdPatient->ipd_no ?? '' }}</td>
                <td class="text-end">{{ number_format($amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Amount</td>
            <td class="text-end">{{ number_format($amount, 2) }}</td>
        </tr>
        <tr>
            <td>VAT ({{ rtrim(rtrim(number_format($vat, 2), '0'), '.') }}%)</td>
            <td class="text-end">{{ number_format(($amount * $vat) / 100, 2) }}</td>
        </tr>
        <tr>
            <td>Tax ({{ rtrim(rtrim(number_format($tax, 2), '0'), '.') }}%)</td>
            <td class="text-end">{{ number_format(($amount * $tax) / 100, 2) }}</td>
        </tr>
        <tr>
            <td>Discount ({{ rtrim(rtrim(number_format($discount, 2), '0'), '.') }}%)</td>
            <td class="text-end">- {{ number_format(($amount * $discount) / 100, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>Net Paid</td>
            <td class="text-end">{{ number_format($netAmount, 2) }}</td>
        </tr>
    </table>
</div>

@if ($paymentVia === 'cheque' && ($transaction->cheque_no || $transaction->cheque_name))
<div class="section">
    <h4>Cheque Details</h4>
    <div class="info-grid">
        <div class="info-item"><strong>Cheque No:</strong> {{ $transaction->cheque_no ?? '-' }}</div>
        <div class="info-item"><strong>Bank / Name:</strong> {{ $transaction->cheque_name ?? '-' }}</div>
        <div class="info-item"><strong>Cheque Date:</strong> {{ $transaction->cheque_date ? \Illuminate\Support\Carbon::parse($transaction->cheque_date)->format('d M Y') : '-' }}</div>
    </div>
</div>
@elseif ($paymentVia === 'card' && ($transaction->card_no || $transaction->card_type))
<div class="section">
    <h4>Card Details</h4>
    <div class="info-grid">
        <div class="info-item"><strong>Card No:</strong> {{ $transaction->card_no ?? '-' }}</div>
        <div class="info-item"><strong>Card Type:</strong> {{ $transaction->card_type ?? '-' }}</div>
    </div>
</div>
@elseif ($paymentVia === 'mfs' && ($transaction->mfs_no || $transaction->mfs_type))
<div class="section">
    <h4>MFS Details</h4>
    <div class="info-grid">
        <div class="info-item"><strong>MFS Type:</strong> {{ $transaction->mfs_type ?? '-' }}</div>
        <div class="info-item"><strong>MFS No:</strong> {{ $transaction->mfs_no ?? '-' }}</div>
        <div class="info-item"><strong>Transaction ID:</strong> {{ $transaction->mfs_transaction_id ?? '-' }}</div>
    </div>
</div>
@endif

@if (!empty($transaction->notes))
<div class="section">
    <h4>Notes</h4>
    <div style="margin-top:6px;">{{ $transaction->notes }}</div>
</div>
@endif

<div class="signatures">
    <div class="sign-block">Payer Signature</div>
    <div class="sign-block">Cashier</div>
    <div class="sign-block">Authorised Signatory</div>
</div>

<div class="footer-note">
    This receipt is generated electronically and is valid without a physical signature.
</div>

<script>
    window.addEventListener('load', function () { setTimeout(() => window.print(), 300); });
</script>
</body>
</html>
