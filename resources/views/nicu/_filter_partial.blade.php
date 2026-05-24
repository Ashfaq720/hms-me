{{-- Shared filter strip: pick one active admission to scope this list. --}}
@php
    $admissionId = request('admission_id');
@endphp
<form method="GET" class="card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small mb-0">Admission</label>
                <select name="admission_id" class="form-select form-select-sm">
                    <option value="">— All active admissions —</option>
                    @foreach($activeAdmissions as $a)
                        <option value="{{ $a->id }}" @selected((string)$admissionId === (string)$a->id)>
                            {{ $a->admission_no }} — {{ $a->baby?->patient_name ?? '—' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </div>
    </div>
</form>
