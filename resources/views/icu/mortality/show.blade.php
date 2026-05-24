@extends('backend.layouts.master')

@section('title', 'Mortality Audit — ' . $admission->icu_case_id)

@section('content')
    @php
        $a = $admission->mortalityAudit;
        $unit = $admission->icu_type ?? 'ICU';

        $auditStatusBadge = match ($a->audit_status) {
            'Completed' => ['label' => 'Completed', 'bg' => '#dcfce7', 'fg' => '#166534', 'border' => '#86efac'],
            'InReview'  => ['label' => 'In Review', 'bg' => '#fef3c7', 'fg' => '#92400e', 'border' => '#fde68a'],
            default     => ['label' => 'Pending Review', 'bg' => '#fef3c7', 'fg' => '#92400e', 'border' => '#fde68a'],
        };

        $preventBadge = match ($a->preventability) {
            'Preventable'    => ['label' => 'Preventable', 'bg' => '#fee2e2', 'fg' => '#991b1b', 'border' => '#fecaca'],
            'NonPreventable' => ['label' => 'Non-preventable', 'bg' => '#fee2e2', 'fg' => '#991b1b', 'border' => '#fecaca'],
            'Indeterminate'  => ['label' => 'Indeterminate', 'bg' => '#e0f2fe', 'fg' => '#075985', 'border' => '#bae6fd'],
            default          => null,
        };
    @endphp

    <div class="container">
        <div class="app-page-head d-flex justify-content-between align-items-center">
            <div>
                <h1 class="app-page-title text-dark mb-1">Mortality Audit</h1>
                <div class="text-muted small">
                    {{ $admission->icu_case_id }} — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <div class="d-flex gap-2 no-print">
                <a href="{{ route('icu.mortality.index', ['icu_type' => $unit]) }}"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <a href="{{ route('icu.admissions.mortality.print', $admission->id) }}" target="_blank"
                    class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-printer me-1"></i>Print
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-2">{{ session('error') }}</div>
        @endif

        {{-- Death Summary & Mortality Audit --}}
        <div class="card shadow-sm rounded-3 border-0 mt-3 mortality-card">
            <div class="card-header d-flex align-items-center gap-2"
                style="background:#fff1f2;border-bottom:1px solid #fecdd3;">
                <span class="mortality-icon"
                    style="background:#fecdd3;color:#be123c;width:34px;height:34px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;">
                    <i class="bi bi-heart-pulse-fill"></i>
                </span>
                <h5 class="mb-0 fw-bold" style="color:#be123c;">Death Summary &amp; Mortality Audit</h5>
            </div>

            <div class="card-body p-0">
                <div class="px-3 py-2 fw-semibold d-flex align-items-center gap-2"
                    style="background:#fff1f2;color:#be123c;border-bottom:1px solid #fecdd3;">
                    <i class="bi bi-file-plus"></i>
                    <span>Death Summary</span>
                </div>

                <div class="mortality-rows">
                    <div class="mortality-row">
                        <div class="mortality-label"><i class="bi bi-calendar-event text-danger"></i>
                            <span>Date / Time of Death</span>
                        </div>
                        <div class="mortality-value d-flex gap-4">
                            <span class="fw-semibold">{{ $a->death_time?->format('d M Y') ?? '-' }}</span>
                            <span class="fw-semibold">{{ $a->death_time?->format('h:i A') ?? '' }}</span>
                        </div>
                    </div>

                    <div class="mortality-row">
                        <div class="mortality-label"><i class="bi bi-exclamation-octagon text-danger"></i>
                            <span>Cause of Death</span>
                        </div>
                        <div class="mortality-value text-pre">{{ $a->cause_of_death ?? '-' }}</div>
                    </div>

                    <div class="mortality-row">
                        <div class="mortality-label"><i class="bi bi-file-text text-danger"></i>
                            <span>Final Diagnosis</span>
                        </div>
                        <div class="mortality-value text-pre">{{ $a->final_diagnosis ?? '-' }}</div>
                    </div>

                    <div class="mortality-row">
                        <div class="mortality-label"><i class="bi bi-file-medical text-danger"></i>
                            <span>Resuscitation Details</span>
                        </div>
                        <div class="mortality-value text-pre">{{ $a->resuscitation_details ?? '-' }}</div>
                    </div>

                    <div class="mortality-row">
                        <div class="mortality-label"><i class="bi bi-plus-square text-danger"></i>
                            <span>Code Blue Reference</span>
                        </div>
                        <div class="mortality-value">
                            @if ($a->codeBlueEvent)
                                <a class="fw-semibold"
                                    href="{{ route('icu.admissions.emergency.show', [$admission->id, $a->codeBlueEvent->id]) }}">
                                    {{ $a->codeBlueEvent->event_no }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="mortality-row">
                        <div class="mortality-label"><i class="bi bi-file-earmark-text text-danger"></i>
                            <span>Doctor Declaration</span>
                        </div>
                        <div class="mortality-value">
                            @if ($a->declaredByDoctor)
                                <div class="text-pre">
                                    I hereby declare that the above patient was unresponsive to treatment and declared
                                    dead on
                                    {{ $a->death_time?->format('d M Y') }} at {{ $a->death_time?->format('h:i A') }}.
                                </div>
                                <div class="mt-2">
                                    <div class="fw-semibold">Dr. {{ $a->declaredByDoctor->name }}</div>
                                    <div class="text-muted small">Consultant Intensivist</div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    @if ($a->body_handover_to)
                        <div class="mortality-row">
                            <div class="mortality-label"><i class="bi bi-box-arrow-right text-danger"></i>
                                <span>Body Handover Details</span>
                            </div>
                            <div class="mortality-value">
                                <div><span class="fw-semibold">Handed over to:</span> {{ $a->body_handover_to }}</div>
                                <div><span class="fw-semibold">Date / Time:</span>
                                    {{ $a->death_time?->format('d M Y h:i A') }}</div>
                                <div><span class="fw-semibold">Handed over by:</span> {{ $unit }} Staff Nurse</div>
                            </div>
                        </div>
                    @endif

                    <div class="mortality-row">
                        <div class="mortality-label"><i class="bi bi-arrow-down-circle text-danger"></i>
                            <span>Mortality Audit Status</span>
                        </div>
                        <div class="mortality-value">
                            <span class="badge"
                                style="background:{{ $auditStatusBadge['bg'] }};color:{{ $auditStatusBadge['fg'] }};border:1px solid {{ $auditStatusBadge['border'] }};font-weight:600;padding:6px 12px;">
                                {{ $auditStatusBadge['label'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mortality Audit Log --}}
        <div class="card shadow-sm rounded-3 border-0 mt-3 mortality-card">
            <div class="card-header d-flex align-items-center gap-2"
                style="background:#f5f3ff;border-bottom:1px solid #ddd6fe;">
                <span class="mortality-icon"
                    style="background:#ddd6fe;color:#6d28d9;width:34px;height:34px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;">
                    <i class="bi bi-clipboard-data"></i>
                </span>
                <h5 class="mb-0 fw-bold" style="color:#6d28d9;">Mortality Audit Log</h5>
            </div>

            <div class="card-body p-0">
                @if ($a->reviewed_at)
                    <div class="mortality-rows">
                        <div class="mortality-row">
                            <div class="mortality-label"><span>Death Reviewed By</span></div>
                            <div class="mortality-value">
                                {{ $a->deathReviewedByDoctor ? 'Dr. ' . $a->deathReviewedByDoctor->name . ' (Chairperson, Mortality Audit Committee)' : '-' }}
                            </div>
                        </div>
                        <div class="mortality-row">
                            <div class="mortality-label"><span>Review Date</span></div>
                            <div class="mortality-value">{{ $a->review_date?->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="mortality-row">
                            <div class="mortality-label"><span>Primary Cause</span></div>
                            <div class="mortality-value text-pre">{{ $a->primary_cause ?? '-' }}</div>
                        </div>
                        <div class="mortality-row">
                            <div class="mortality-label"><span>Contributing Factors</span></div>
                            <div class="mortality-value text-pre">{{ $a->contributing_factors ?? '-' }}</div>
                        </div>
                        <div class="mortality-row">
                            <div class="mortality-label"><span>Preventable / Non-preventable</span></div>
                            <div class="mortality-value">
                                @if ($preventBadge)
                                    <span class="badge"
                                        style="background:{{ $preventBadge['bg'] }};color:{{ $preventBadge['fg'] }};border:1px solid {{ $preventBadge['border'] }};font-weight:600;padding:6px 12px;">
                                        {{ $preventBadge['label'] }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <div class="mortality-row">
                            <div class="mortality-label"><span>Clinical Remarks</span></div>
                            <div class="mortality-value text-pre">{{ $a->clinical_remarks ?? '-' }}</div>
                        </div>
                        <div class="mortality-row">
                            <div class="mortality-label"><span>Audit Committee Remarks</span></div>
                            <div class="mortality-value text-pre">{{ $a->committee_remarks ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="px-3 py-3">
                        <div class="alert mb-0 d-flex align-items-center gap-2"
                            style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;">
                            <i class="bi bi-info-circle"></i>
                            <span>This case has been reviewed and recorded for quality improvement and clinical
                                learning.</span>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('icu.admissions.mortality.review', $admission->id) }}"
                        class="p-3 no-print">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Death Reviewed By</label>
                                <select name="death_reviewed_by" class="form-select">
                                    <option value="">-- Select Doctor --</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}"
                                            {{ (string) old('death_reviewed_by', $a->death_reviewed_by) === (string) $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('death_reviewed_by')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Review Date</label>
                                <input type="datetime-local" name="review_date"
                                    value="{{ old('review_date', $a->review_date?->format('Y-m-d\TH:i')) }}"
                                    class="form-control">
                                @error('review_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Primary Cause</label>
                                <input type="text" name="primary_cause" class="form-control"
                                    value="{{ old('primary_cause', $a->primary_cause) }}">
                                @error('primary_cause')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Contributing Factors</label>
                                <input type="text" name="contributing_factors" class="form-control"
                                    value="{{ old('contributing_factors', $a->contributing_factors) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Preventability <span
                                        class="text-danger">*</span></label>
                                <select name="preventability" class="form-select" required>
                                    @foreach (['Indeterminate', 'Preventable', 'NonPreventable'] as $p)
                                        <option value="{{ $p }}"
                                            {{ old('preventability', $a->preventability) === $p ? 'selected' : '' }}>
                                            {{ $p === 'NonPreventable' ? 'Non-preventable' : $p }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Clinical Remarks</label>
                                <textarea name="clinical_remarks" class="form-control" rows="2">{{ old('clinical_remarks', $a->clinical_remarks) }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Audit Committee Remarks</label>
                                <textarea name="committee_remarks" class="form-control" rows="2">{{ old('committee_remarks', $a->committee_remarks) }}</textarea>
                            </div>

                            <div class="col-md-12 text-end">
                                <button class="btn btn-primary btn-sm">
                                    <i class="bi bi-check-circle me-1"></i>Save Review
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <style>
        .text-pre {
            white-space: pre-line
        }

        .mortality-card .card-header {
            padding: .75rem 1rem;
        }

        .mortality-rows .mortality-row {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 1rem;
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
        }

        .mortality-rows .mortality-row:last-child {
            border-bottom: 0;
        }

        .mortality-rows .mortality-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 600;
            color: #be123c;
        }

        .mortality-rows .mortality-label i {
            font-size: 1.05rem;
        }

        .mortality-rows .mortality-value {
            color: #334155;
        }

        @media (max-width: 768px) {
            .mortality-rows .mortality-row {
                grid-template-columns: 1fr;
            }
        }

        @media print {

            .no-print,
            form,
            .alert {
                display: none !important;
            }

            .container {
                max-width: 100% !important;
                width: 100% !important;
            }

            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
        }
    </style>
@endsection
