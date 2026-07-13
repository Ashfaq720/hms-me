@extends('backend.layouts.master')

@section('title', 'ICU Order Management')

@section('content')
    @php
        $patient   = $order->patient ?? $order->admission?->patient;
        $admission = $order->admission;

        $patientName  = $patient?->patient_name ?? '—';
        $patientPid   = $patient?->mrn ?? ($patient ? 'PID' . str_pad($patient->id, 6, '0', STR_PAD_LEFT) : '—');
        $patientAge   = $patient?->dob ? \Carbon\Carbon::parse($patient->dob)->age : null;
        $genderShort  = match (strtolower((string) $patient?->gender)) {
            'male'   => 'Male',
            'female' => 'Female',
            'other'  => 'Other',
            default  => '—',
        };
        $ageGender = $patientAge !== null
            ? $patientAge . ' Y / ' . $genderShort
            : ($patient?->gender ?? '—');

        $admissionDate = $admission?->admission_time?->format('d M Y, h:i A') ?? '—';
        $diagnosis     = $admission?->admission_diagnosis ?? '—';

        $orderCode   = 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
        $orderType   = $order->order_type;
        $typeLabel   = $orderType === 'Lab' ? 'Pathology' : $orderType;
        $typeClass = match ($orderType) {
            'Medication' => 'om-type--medication',
            'Lab'        => 'om-type--lab',
            'Radiology'  => 'om-type--radiology',
            'Procedure'  => 'om-type--procedure',
            default      => 'om-type--default',
        };

        $prioClass = match ($order->priority) {
            'STAT'   => 'om-priority--stat',
            'Urgent' => 'om-priority--urgent',
            default  => 'om-priority--routine',
        };

        $statusKey   = strtolower($order->status);
        $statusLabel = match ($order->status) {
            'InProgress' => 'In Progress',
            'OnHold'     => 'On Hold',
            default      => $order->status,
        };

        $assignedTeam = match ($orderType) {
            'Procedure'  => 'ICU Procedure Team',
            'Medication' => 'ICU Pharmacy Team',
            'Lab'        => 'Pathology Lab',
            'Radiology'  => 'Radiology Team',
            'NursingCare'=> 'ICU Nursing Team',
            'DietFluid'  => 'Dietary Team',
            'Monitoring' => 'ICU Monitoring Team',
            default      => 'ICU Care Team',
        };

        $orderTimeStr = $order->created_at?->format('d M Y, h:i A') ?? '—';
        $startTimeStr = $order->start_time?->format('d M Y, h:i A') ?? '—';

        // Build timeline checkpoints from execution logs (with audit fallback).
        $logs = $order->executionLogs;
        $createdLog = (object) [
            'label'      => 'Order Created',
            'at'         => $order->created_at,
            'actor'      => $order->doctor?->name ?? 'Doctor',
            'state'      => 'done',
        ];

        $ackLog = $logs->firstWhere('status', 'Acknowledged');
        $progLog = $logs->firstWhere('status', 'InProgress');
        $compLog = $logs->firstWhere('status', 'Completed');

        $statusOrder = ['Ordered' => 0, 'Acknowledged' => 1, 'InProgress' => 2, 'Completed' => 3, 'Cancelled' => 4, 'OnHold' => 1];
        $curStep = $statusOrder[$order->status] ?? 0;

        $timeline = [
            [
                'label' => 'Order Created',
                'at'    => $order->created_at,
                'actor' => $order->doctor?->name ?? 'Doctor',
                'done'  => true,
            ],
            [
                'label' => 'Acknowledged',
                'at'    => $ackLog?->created_at,
                'actor' => $ackLog?->executor?->name ?? ($curStep >= 1 ? 'Nurse' : null),
                'done'  => $curStep >= 1 || $ackLog,
            ],
            [
                'label' => 'In Progress',
                'at'    => $progLog?->execution_start_time ?? $progLog?->created_at,
                'actor' => $progLog?->executor?->name ?? $assignedTeam,
                'done'  => $curStep >= 2 || (bool) $progLog,
                'active'=> $order->status === 'InProgress',
            ],
            [
                'label' => 'Completed',
                'at'    => $compLog?->execution_end_time ?? $compLog?->created_at,
                'actor' => $compLog?->executor?->name,
                'done'  => $order->status === 'Completed',
                'pending'=> $order->status !== 'Completed',
            ],
        ];
    @endphp

    <style>
        .od-page { padding: 0 4px; }
        .od-back {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: .85rem; font-weight: 600;
            padding: 7px 14px;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 14px;
            transition: all .15s;
        }
        .od-back:hover {
            border-color: #2563eb;
            color: #1d4ed8;
            background: #f8fafc;
        }
        .od-back i { font-size: 1rem; }

        .od-title { font-size: 1.55rem; font-weight: 700; color: #0f172a; margin: 0 0 18px; }

        .od-icu-pill {
            display: flex; align-items: center; justify-content: space-between;
            background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
            padding: 12px 18px; margin-bottom: 18px;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
        }
        .od-icu-pill__left { display: inline-flex; align-items: center; gap: 10px; color: #0f172a; font-weight: 600; }
        .od-icu-pill__left i { color: #2563eb; }
        .od-icu-pill__caret { color: #94a3b8; }

        .od-grid {
            display: grid;
            grid-template-columns: 1.05fr 1.25fr 1fr;
            gap: 18px;
            align-items: start;
        }
        @media (max-width: 1100px) {
            .od-grid { grid-template-columns: 1fr; }
        }

        .od-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
            padding: 18px 20px;
        }
        .od-card__head {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 14px;
        }
        .od-card__title { font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0; }
        .od-card__link {
            color: #2563eb; font-size: .85rem; font-weight: 600; text-decoration: none;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .od-card__link:hover { color: #1d4ed8; }

        .od-kv { display: grid; grid-template-columns: auto 1fr; gap: 14px 14px; }
        .od-kv__k { color: #64748b; font-size: .85rem; }
        .od-kv__v { color: #0f172a; font-size: .9rem; font-weight: 600; text-align: right; }

        .od-summary-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; margin-top: 16px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 12px 14px; font-size: .9rem; font-weight: 600; color: #334155;
            cursor: pointer; transition: all .15s;
            text-decoration: none;
        }
        .od-summary-btn:hover { border-color: #2563eb; color: #1d4ed8; background: #f8fafc; }

        /* Order Details card */
        .od-status-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; border-radius: 999px;
            font-size: .72rem; font-weight: 700;
        }
        .od-status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
        .od-status-pill--ordered      { background: #fef3c7; color: #92400e; }
        .od-status-pill--ordered::before      { background: #d97706; }
        .od-status-pill--acknowledged { background: #dbeafe; color: #1e40af; }
        .od-status-pill--acknowledged::before { background: #2563eb; }
        .od-status-pill--inprogress   { background: #ede9fe; color: #5b21b6; }
        .od-status-pill--inprogress::before   { background: #7c3aed; }
        .od-status-pill--completed    { background: #dcfce7; color: #166534; }
        .od-status-pill--completed::before    { background: #16a34a; }
        .od-status-pill--cancelled    { background: #fee2e2; color: #b91c1c; }
        .od-status-pill--cancelled::before    { background: #dc2626; }
        .od-status-pill--onhold       { background: #e2e8f0; color: #475569; }
        .od-status-pill--onhold::before       { background: #64748b; }
        .od-status-pill--modified     { background: #cffafe; color: #0e7490; }
        .od-status-pill--modified::before     { background: #0891b2; }

        .od-order-id { color: #64748b; font-family: 'JetBrains Mono','Courier New',monospace; font-size: .8rem; margin-bottom: 4px; }
        .od-order-title { font-size: 1.18rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; }

        .od-type {
            display: inline-flex; align-items: center;
            padding: 3px 12px; border-radius: 999px;
            font-size: .75rem; font-weight: 600;
        }
        .od-type--medication { background: #dbeafe; color: #1d4ed8; }
        .od-type--lab        { background: #ede9fe; color: #6d28d9; }
        .od-type--radiology  { background: #fef3c7; color: #b45309; }
        .od-type--procedure  { background: #dcfce7; color: #166534; }
        .od-type--default    { background: #f1f5f9; color: #475569; }

        .od-priority { font-weight: 700; font-size: .82rem; letter-spacing: .04em; text-transform: uppercase; }
        .om-priority--stat    { color: #dc2626; }
        .om-priority--urgent  { color: #d97706; }
        .om-priority--routine { color: #2563eb; }

        .od-section-label {
            font-size: .68rem; font-weight: 700; letter-spacing: .14em;
            text-transform: uppercase; color: #94a3b8; margin: 18px 0 8px;
        }

        .od-status-select {
            width: 100%;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 9px 36px 9px 14px;
            font-size: .88rem; color: #0f172a; font-weight: 600;
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M8 11.5 3.5 7l1-1L8 9.5 11.5 6l1 1z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 14px;
            cursor: pointer;
        }
        .od-status-select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }

        .od-remarks {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 12px 14px;
            color: #78350f;
            font-size: .88rem;
            line-height: 1.5;
        }

        /* Timeline */
        .od-timeline { position: relative; padding-left: 30px; }
        .od-timeline::before {
            content: ''; position: absolute;
            left: 11px; top: 6px; bottom: 30px;
            width: 2px; background: #e2e8f0;
        }
        .od-tl-item { position: relative; padding-bottom: 22px; }
        .od-tl-item:last-of-type { padding-bottom: 4px; }
        .od-tl-dot {
            position: absolute; left: -30px; top: 2px;
            width: 24px; height: 24px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: #fff; border: 2px solid #cbd5e1;
            font-size: .7rem;
        }
        .od-tl-dot--done    { background: #16a34a; border-color: #16a34a; color: #fff; }
        .od-tl-dot--active  { background: #fff; border-color: #2563eb; }
        .od-tl-dot--active::after {
            content: ''; width: 10px; height: 10px; background: #2563eb; border-radius: 50%;
        }
        .od-tl-dot--pending { background: #fff; border-color: #cbd5e1; }
        .od-tl-head {
            display: flex; justify-content: space-between; align-items: baseline; gap: 10px;
            margin-bottom: 2px;
        }
        .od-tl-title { font-size: .92rem; font-weight: 700; color: #0f172a; }
        .od-tl-time  { font-size: .72rem; color: #64748b; white-space: nowrap; }
        .od-tl-sub   { font-size: .82rem; color: #64748b; }
        .od-tl-sub--pending { color: #94a3b8; font-style: italic; }

        .od-tl-foot-btn {
            display: block; width: 100%;
            margin-top: 14px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            text-align: center;
            font-weight: 600; font-size: .9rem; color: #334155;
            text-decoration: none;
            cursor: pointer;
        }
        .od-tl-foot-btn:hover { border-color: #2563eb; color: #1d4ed8; background: #f8fafc; }
    </style>

    <div class="od-page container-fluid">
        <a href="{{ route('icu.orders.manage') }}" class="od-back">
            <i class="bi bi-arrow-left"></i> Back to Order Management
        </a>

        <h1 class="od-title">Hospital Management System</h1>

        <div class="od-icu-pill">
            <span class="od-icu-pill__left">
                <i class="bi bi-card-list"></i>
                {{ $admission?->icu_case_id ?: 'ICU' }} ({{ ($admission?->icu_type ?: 'ICU') . ($admission?->icu_type === 'CCU' ? '' : ' - General') }})
            </span>
            <i class="bi bi-chevron-down od-icu-pill__caret"></i>
        </div>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

        <div class="od-grid">
            {{-- Patient Summary --}}
            <div class="od-card">
                <div class="od-card__head">
                    <h6 class="od-card__title">Patient Summary</h6>
                    @if ($patient)
                        <a href="{{ url('/patients/' . $patient->id) }}" class="od-card__link">
                            View Profile <i class="bi bi-arrow-right"></i>
                        </a>
                    @endif
                </div>

                <div class="od-kv">
                    <div class="od-kv__k">Name</div>
                    <div class="od-kv__v">{{ $patientName }}</div>

                    <div class="od-kv__k">Age / Gender</div>
                    <div class="od-kv__v">{{ $ageGender }}</div>

                    <div class="od-kv__k">PID</div>
                    <div class="od-kv__v">{{ $patientPid }}</div>

                    <div class="od-kv__k">Admission</div>
                    <div class="od-kv__v">{{ $admissionDate }}</div>

                    <div class="od-kv__k">Diagnosis</div>
                    <div class="od-kv__v">{{ $diagnosis }}</div>
                </div>

                @if ($admission)
                    <a href="{{ route('icu.admissions.show', $admission->id) }}" class="od-summary-btn">
                        <i class="bi bi-file-medical"></i> View Clinical Summary
                    </a>
                @endif
            </div>

            {{-- Order Details --}}
            <div class="od-card">
                <div class="od-card__head">
                    <h6 class="od-card__title">Order Details</h6>
                    <span class="od-status-pill od-status-pill--{{ $statusKey }}">{{ $statusLabel }}</span>
                </div>

                <div class="od-order-id">{{ $orderCode }}</div>
                <div class="od-order-title">{{ $order->order_title }}</div>
                <span class="od-type {{ $typeClass }}">{{ $typeLabel }}</span>

                <div class="od-kv" style="margin-top: 18px;">
                    <div class="od-kv__k">Priority</div>
                    <div class="od-kv__v"><span class="od-priority {{ $prioClass }}">{{ strtoupper($order->priority) }}</span></div>

                    <div class="od-kv__k">Ordered By</div>
                    <div class="od-kv__v">{{ $order->doctor?->name ?? '—' }}</div>

                    <div class="od-kv__k">Order Time</div>
                    <div class="od-kv__v">{{ $orderTimeStr }}</div>

                    <div class="od-kv__k">Assigned To</div>
                    <div class="od-kv__v">{{ $assignedTeam }}</div>

                    <div class="od-kv__k">Start Time</div>
                    <div class="od-kv__v">{{ $startTimeStr }}</div>
                </div>

                <div class="od-section-label">Status</div>
                <select class="od-status-select" disabled>
                    @foreach (['Ordered', 'Acknowledged', 'InProgress', 'Completed', 'Cancelled', 'OnHold', 'Modified'] as $s)
                        <option value="{{ $s }}" @selected($order->status === $s)>
                            {{ $s === 'InProgress' ? 'In Progress' : ($s === 'OnHold' ? 'On Hold' : $s) }}
                        </option>
                    @endforeach
                </select>

                @if ($order->remarks)
                    <div class="od-section-label">Remarks</div>
                    <div class="od-remarks">{{ $order->remarks }}</div>
                @endif
            </div>

            {{-- Order Activity Timeline --}}
            <div class="od-card">
                <div class="od-card__head">
                    <h6 class="od-card__title">Order Activity Timeline</h6>
                </div>

                <div class="od-timeline">
                    @foreach ($timeline as $step)
                        @php
                            $dotClass = 'od-tl-dot--pending';
                            if (! empty($step['done']) && empty($step['active'])) $dotClass = 'od-tl-dot--done';
                            if (! empty($step['active'])) $dotClass = 'od-tl-dot--active';
                        @endphp
                        <div class="od-tl-item">
                            <span class="od-tl-dot {{ $dotClass }}">
                                @if ($dotClass === 'od-tl-dot--done')
                                    <i class="bi bi-check-lg"></i>
                                @endif
                            </span>
                            <div class="od-tl-head">
                                <span class="od-tl-title">{{ $step['label'] }}</span>
                                <span class="od-tl-time">
                                    {{ $step['at']?->format('d M Y, h:i A') ?? '' }}
                                </span>
                            </div>
                            @if (! empty($step['pending']) && empty($step['at']))
                                <div class="od-tl-sub od-tl-sub--pending">Pending</div>
                            @else
                                <div class="od-tl-sub">{{ $step['actor'] ?? '—' }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- @if ($admission)
                    <a href="{{ route('icu.admissions.orders.index', $admission->id) }}" class="od-tl-foot-btn">
                        View Full Timeline
                    </a>
                @endif --}}
            </div>
        </div>
    </div>
@endsection
