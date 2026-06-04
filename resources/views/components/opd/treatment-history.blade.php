@props(['opdPatient'])

@php
    $prescriptions = $opdPatient->prescriptions ?? collect();
@endphp

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-journal-medical me-2 text-primary"></i>Treatment History
            <span class="badge bg-primary-subtle text-primary ms-2">{{ $prescriptions->count() }}</span>
        </h6>
        <div class="text-muted small">
            <i class="bi bi-person-vcard me-1"></i>{{ $opdPatient->patient->patient_name ?? '-' }}
        </div>
    </div>

    <div class="card-body">
        @if ($prescriptions->isEmpty())
            <div class="alert alert-light border text-center mb-0">
                <i class="bi bi-info-circle me-1"></i> No treatment history recorded for this patient yet.
            </div>
        @else
            <div class="treatment-timeline">
                @foreach ($prescriptions as $rx)
                    <div class="treatment-card">
                        <div class="treatment-card-header">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-primary">{{ $rx->prescription_no ?? 'RX' }}</span>
                                <span class="fw-semibold">
                                    {{ $rx->date ? $rx->date->format('d M Y, h:i A') : '-' }}
                                </span>
                                @if ($rx->type)
                                    <span class="badge bg-secondary-subtle text-secondary text-capitalize">
                                        {{ $rx->type }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-person-badge me-1"></i>
                                {{ $rx->doctor->name ?? 'Unknown Doctor' }}
                            </div>
                        </div>

                        <div class="treatment-card-body">
                            <div class="row g-3">
                                {{-- Symptoms --}}
                                <div class="col-md-6">
                                    <div class="treatment-section">
                                        <div class="treatment-section-title">
                                            <i class="bi bi-clipboard2-pulse text-danger me-1"></i>Symptoms111
                                        </div>
                                        @if ($rx->symptoms && $rx->symptoms->count())
                                            <ul class="mb-0 ps-3 small">
                                                @foreach ($rx->symptoms as $sym)
                                                    <li>{{ $sym->symptom?->name .",". $sym->symptom?->notes ?? '-' }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="text-muted small">—</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Findings --}}
                                <div class="col-md-6">
                                    <div class="treatment-section">
                                        <div class="treatment-section-title">
                                            <i class="bi bi-search text-info me-1"></i>Findings
                                        </div>
                                        <div class="small">{{ $rx->findings ?: '—' }}</div>
                                    </div>
                                </div>

                                {{-- Medicines --}}
                                <div class="col-md-12">
                                    <div class="treatment-section">
                                        <div class="treatment-section-title">
                                            <i class="bi bi-capsule-pill text-success me-1"></i>Medicines
                                        </div>
                                        @if ($rx->medicines && $rx->medicines->count())
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0 small">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Medicine</th>
                                                            <th>Dosage</th>
                                                            <th>Frequency</th>
                                                            <th>Duration</th>
                                                            <th>Instruction</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($rx->medicines as $med)
                                                            <tr>
                                                                <td>{{ $med->medicine_name ?? optional($med->medicine)->medicine_name ?? '-' }}</td>
                                                                <td>{{ $med->dosage ?? '-' }}</td>
                                                                <td>{{ $med->frequency ?? '-' }}</td>
                                                                <td>{{ $med->duration ?? '-' }}</td>
                                                                <td>{{ $med->instruction ?? $med->note ?? '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-muted small">—</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Lab Investigations --}}
                                <div class="col-md-6">
                                    <div class="treatment-section">
                                        <div class="treatment-section-title">
                                            <i class="bi bi-clipboard2-data text-warning me-1"></i>Lab Investigations
                                        </div>
                                        @if ($rx->labInvestigations && $rx->labInvestigations->count())
                                            <ul class="mb-0 ps-3 small">
                                                @foreach ($rx->labInvestigations as $lab)
                                                    <li>{{ $lab->title ?? $lab->name ?? '-' }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="text-muted small">—</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Advice & Next Visit --}}
                                <div class="col-md-6">
                                    <div class="treatment-section">
                                        <div class="treatment-section-title">
                                            <i class="bi bi-chat-left-text text-primary me-1"></i>Advice
                                        </div>
                                        <div class="small">{{ $rx->advice ?: '—' }}</div>
                                        @if ($rx->next_visit)
                                            <div class="mt-2 small">
                                                <i class="bi bi-calendar-event me-1 text-primary"></i>
                                                <strong>Next Visit:</strong>
                                                {{ \Illuminate\Support\Carbon::parse($rx->next_visit)->format('d M Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@once
    @push('styles')
        <style>
            .treatment-timeline {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .treatment-card {
                border: 1px solid #e9ecef;
                border-left: 4px solid #0d6efd;
                border-radius: 10px;
                background: #fff;
                overflow: hidden;
            }

            .treatment-card-header {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
                padding: 10px 14px;
                background: #f8f9fb;
                border-bottom: 1px solid #eef0f3;
            }

            .treatment-card-body {
                padding: 14px;
            }

            .treatment-section {
                background: #fafbfc;
                border: 1px solid #eef0f3;
                border-radius: 8px;
                padding: 10px 12px;
                height: 100%;
            }

            .treatment-section-title {
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                color: #475467;
                margin-bottom: 6px;
                letter-spacing: 0.3px;
            }
        </style>
    @endpush
@endonce
