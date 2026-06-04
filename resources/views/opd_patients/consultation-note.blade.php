@php $note = $opdPatient->consultationNote; @endphp

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard2-pulse me-2 text-primary"></i>SOAP Consultation Note</h6>
        @if($note && $note->isClosed())
            <span class="badge bg-secondary">Closed — {{ $note->closed_at?->format('d M Y H:i') }}</span>
        @elseif($note)
            <span class="badge bg-warning text-dark">Draft</span>
        @endif
    </div>

    <div class="card-body p-3 p-md-4">
        @if($note && $note->isClosed())
            {{-- Read-only closed view --}}
            <div class="row g-3">
                @foreach(['subjective' => ['S — Subjective','bi-person-fill'],'objective' => ['O — Objective','bi-eyedropper'],'assessment' => ['A — Assessment','bi-stethoscope'],'plan' => ['P — Plan','bi-list-check']] as $field => [$label, $icon])
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="fw-bold small text-muted mb-1"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</div>
                        <p class="mb-0 small" style="white-space:pre-wrap;">{{ $note->$field ?: '—' }}</p>
                    </div>
                </div>
                @endforeach

                @if($note->icd10_code)
                <div class="col-12">
                    <div class="border rounded-3 p-3 bg-light">
                        <span class="fw-bold small text-muted">ICD-10:</span>
                        <span class="ms-2 fw-semibold">{{ $note->icd10_code }}</span>
                        @if($note->icd10_description)
                            <span class="text-muted ms-1">— {{ $note->icd10_description }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        @else
            {{-- Editable form --}}
            <form action="{{ route('opd-patients.consultation-note.store', $opdPatient->id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="bi bi-person-fill me-1 text-primary"></i>S — Subjective <small class="text-muted fw-normal">(Patient's complaint)</small></label>
                        <textarea name="subjective" rows="4" class="form-control"
                            placeholder="What the patient reports…">{{ old('subjective', $note->subjective ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="bi bi-eyedropper me-1 text-success"></i>O — Objective <small class="text-muted fw-normal">(Examination findings)</small></label>
                        <textarea name="objective" rows="4" class="form-control"
                            placeholder="Vital signs, physical examination…">{{ old('objective', $note->objective ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="bi bi-stethoscope me-1 text-warning"></i>A — Assessment <small class="text-muted fw-normal">(Diagnosis)</small></label>
                        <textarea name="assessment" rows="4" class="form-control"
                            placeholder="Clinical impression / diagnosis…">{{ old('assessment', $note->assessment ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="bi bi-list-check me-1 text-danger"></i>P — Plan <small class="text-muted fw-normal">(Treatment plan)</small></label>
                        <textarea name="plan" rows="4" class="form-control"
                            placeholder="Medications, investigations, referrals…">{{ old('plan', $note->plan ?? '') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ICD-10 Code</label>
                        <input type="text" name="icd10_code" class="form-control"
                            value="{{ old('icd10_code', $note->icd10_code ?? '') }}"
                            placeholder="e.g. J06.9" maxlength="20">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">ICD-10 Description</label>
                        <input type="text" name="icd10_description" class="form-control"
                            value="{{ old('icd10_description', $note->icd10_description ?? '') }}"
                            placeholder="Diagnosis description">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i>Save Draft
                    </button>
                </div>
            </form>

            @if($note)
            <form action="{{ route('opd-patients.consultation-note.close', $opdPatient->id) }}" method="POST"
                class="mt-2" onsubmit="return confirm('Close this consultation? It cannot be edited after closing.')">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-lock me-1"></i>Close Consultation
                </button>
            </form>
            @endif
        @endif
    </div>
</div>
