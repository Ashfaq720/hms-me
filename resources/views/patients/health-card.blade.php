<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Card — {{ $patient->patient_name }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4f8;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 30px 20px;
        }

        .print-btn {
            margin-bottom: 24px;
            display: flex;
            gap: 10px;
        }

        .print-btn button {
            padding: 10px 28px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-print { background: #2b335d; color: #fff; }
        .btn-back  { background: #e2e8f0; color: #2b335d; }

        /* ── Card wrapper – credit card proportions 85.6 × 54 mm ── */
        .card-wrap {
            width: 340px;
        }

        /* Front */
        .hc-front {
            width: 340px;
            height: 214px;
            border-radius: 16px;
            background: linear-gradient(135deg, #1e2a6e 0%, #2b3f9e 50%, #1a6fa8 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
            padding: 16px 18px;
            box-shadow: 0 8px 32px rgba(30,42,110,0.28);
            margin-bottom: 18px;
        }

        .hc-front::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }

        .hc-front::after {
            content: '';
            position: absolute;
            bottom: -40px; left: -40px;
            width: 160px; height: 160px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .hc-logo-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            position: relative; z-index: 1;
        }

        .hc-hospital-name {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .hc-chip {
            width: 32px; height: 24px;
            background: linear-gradient(135deg, #ffd700, #ffb300);
            border-radius: 4px;
            opacity: 0.85;
        }

        .hc-body {
            display: flex;
            gap: 12px;
            position: relative; z-index: 1;
        }

        .hc-photo {
            width: 62px; height: 74px;
            border-radius: 8px;
            border: 2px solid rgba(255,255,255,0.4);
            object-fit: cover;
            flex-shrink: 0;
            background: rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            overflow: hidden;
        }

        .hc-photo img {
            width: 100%; height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }

        .hc-info { flex: 1; }

        .hc-name {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hc-row {
            display: flex;
            gap: 12px;
            margin-bottom: 3px;
        }

        .hc-field { font-size: 9.5px; }
        .hc-field .lbl { opacity: 0.65; display: block; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .hc-field .val { font-weight: 600; font-size: 10.5px; }

        .hc-allergy {
            margin-top: 6px;
            background: rgba(255,80,80,0.18);
            border: 1px solid rgba(255,120,120,0.4);
            border-radius: 5px;
            padding: 3px 7px;
            font-size: 9px;
            font-weight: 600;
        }

        .hc-allergy .lbl { opacity: 0.8; margin-right: 4px; }

        .hc-footer {
            position: absolute;
            bottom: 12px; left: 18px; right: 18px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            z-index: 1;
        }

        .hc-card-no {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            opacity: 0.95;
        }

        .hc-mrn {
            font-size: 9px;
            opacity: 0.6;
            margin-top: 2px;
        }

        .hc-qr {
            width: 52px; height: 52px;
            background: #fff;
            border-radius: 6px;
            padding: 3px;
            display: flex; align-items: center; justify-content: center;
        }

        .hc-qr canvas, .hc-qr img { width: 46px !important; height: 46px !important; }

        /* Back */
        .hc-back {
            width: 340px;
            height: 214px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            padding: 0;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .hc-back-stripe {
            background: #2b335d;
            height: 38px;
            width: 100%;
        }

        .hc-back-body {
            padding: 12px 18px;
        }

        .hc-back-title {
            font-size: 10px;
            font-weight: 700;
            color: #2b335d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .hc-back-row {
            display: flex;
            gap: 16px;
            margin-bottom: 6px;
        }

        .hc-back-field { flex: 1; }
        .hc-back-field .lbl {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            display: block;
            margin-bottom: 1px;
        }
        .hc-back-field .val {
            font-size: 11px;
            font-weight: 600;
            color: #1e293b;
        }

        .hc-back-footer {
            position: absolute;
            bottom: 12px;
            left: 18px; right: 18px;
            font-size: 8.5px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        .hc-back { position: relative; }

        .badge-allergy {
            display: inline-block;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 4px;
            padding: 2px 7px;
            font-size: 9.5px;
            font-weight: 600;
            margin-right: 3px;
            margin-top: 2px;
        }

        .section-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 5px;
            margin-top: 10px;
        }

        @media print {
            body { background: #fff; padding: 0; justify-content: flex-start; padding: 10mm; }
            .print-btn { display: none; }
            .card-wrap { break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="print-btn">
    <button class="btn-print" onclick="window.print()">&#128438; Print Health Card</button>
    <button class="btn-back"  onclick="history.back()">&#8592; Back</button>
</div>

<div class="card-wrap">

    {{-- ── FRONT ── --}}
    <div class="hc-front">

        <div class="hc-logo-row">
            <div class="hc-hospital-name">
                {{-- {{ config('app.name', 'HMS Hospital') }} --}}
                HMS
            </div>
            <div class="hc-chip"></div>
        </div>

        <div class="hc-body">
            {{-- Photo --}}
            <div class="hc-photo">
                @if($patient->image)
                    <img src="{{ asset('storage/' . $patient->image) }}" alt="">
                @else
                    <span>&#128100;</span>
                @endif
            </div>

            {{-- Info --}}
            <div class="hc-info">
                <div class="hc-name">{{ $patient->patient_name }}</div>

                <div class="hc-row">
                    <div class="hc-field">
                        <span class="lbl">Gender</span>
                        <span class="val">{{ $patient->gender ?? '—' }}</span>
                    </div>
                    <div class="hc-field">
                        <span class="lbl">DOB</span>
                        <span class="val">{{ $patient->dob?->format('d M Y') ?? '—' }}</span>
                    </div>
                    <div class="hc-field">
                        <span class="lbl">Blood</span>
                        <span class="val">{{ $patient->blood_group ?? '—' }}</span>
                    </div>
                </div>

                @if($patient->known_allergies)
                <div class="hc-allergy">
                    <span class="lbl">⚠ Allergy:</span>{{ $patient->known_allergies }}
                </div>
                @endif
            </div>
        </div>

        <div class="hc-footer">
            <div>
                <div class="hc-card-no">{{ $patient->health_card_no }}</div>
                <div class="hc-mrn">{{ $patient->mrn }}</div>
            </div>
            <div class="hc-qr" id="qrcode"></div>
        </div>
    </div>

    {{-- ── BACK ── --}}
    <div class="hc-back">
        <div class="hc-back-stripe"></div>
        <div class="hc-back-body">
            <div class="hc-back-title">Patient Information</div>

            <div class="hc-back-row">
                <div class="hc-back-field">
                    <span class="lbl">Mobile</span>
                    <span class="val">{{ $patient->mobileno ?? '—' }}</span>
                </div>
                <div class="hc-back-field">
                    <span class="lbl">Guardian</span>
                    <span class="val">{{ $patient->guardian_name ?? '—' }}</span>
                </div>
            </div>

            <div class="hc-back-row">
                <div class="hc-back-field">
                    <span class="lbl">Insurance</span>
                    <span class="val">{{ $patient->insurance ?? '—' }}</span>
                </div>
                <div class="hc-back-field">
                    <span class="lbl">Valid Until</span>
                    <span class="val">{{ $patient->insurance_validity?->format('M Y') ?? '—' }}</span>
                </div>
            </div>

            @if($patient->organization_name)
            <div class="hc-back-row">
                <div class="hc-back-field">
                    <span class="lbl">Organization</span>
                    <span class="val">{{ $patient->organization_name }} ({{ $patient->organization_id ?? '—' }})</span>
                </div>
            </div>
            @endif

            @if($patient->known_allergies)
            <div class="section-label">Known Allergies</div>
            <div>
                @foreach(explode(',', $patient->known_allergies) as $a)
                    <span class="badge-allergy">{{ trim($a) }}</span>
                @endforeach
            </div>
            @endif
        </div>

        <div class="hc-back-footer">
            This card is the property of .HMS
            If found, please return to the hospital. Card No: {{ $patient->health_card_no }}
        </div>
    </div>

</div>

<script>
    new QRCode(document.getElementById('qrcode'), {
        text:           '{{ $patient->health_card_no }}',
        width:          46,
        height:         46,
        colorDark:      '#1e2a6e',
        colorLight:     '#ffffff',
        correctLevel:   QRCode.CorrectLevel.M
    });
</script>
</body>
</html>
