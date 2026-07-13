@extends('backend.layouts.master')

@section('title', 'NICU - ' . $nicuAdmission->admission_no)

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="app-page-head d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0">
                <i class="bi bi-emoji-smile text-primary"></i>
                {{ $nicuAdmission->admission_no }}
                <span class="badge {{ $nicuAdmission->status_badge_class }}">{{ $nicuAdmission->status }}</span>
                @foreach($nicuAdmission->riskBadges() as $rb)
                    @php [$label, $cls] = $rb; @endphp
                    <span class="badge {{ $cls }}">{{ $label }}</span>
                @endforeach
            </h1>
            <div class="text-muted small">
                Source:
                <span class="badge bg-light text-dark border">{{ $nicuAdmission->source_type }}</span>
                @if($source && method_exists($source, 'getKey'))
                    @if($nicuAdmission->source_type === 'OT')
                        <a href="{{ route('ot.schedules.show', $source->id) }}">{{ $source->schedule_no ?? ('#'.$source->id) }}</a>
                    @elseif($nicuAdmission->source_type === 'IPD')
                        <a href="{{ route('ipd-patients.show', $source->id) }}">{{ $source->ipd_no ?? ('#'.$source->id) }}</a>
                    @else
                        #{{ $source->id }}
                    @endif
                @endif
                · Admitted {{ optional($nicuAdmission->admitted_at)->format('Y-m-d H:i') }}
                @if($nicuAdmission->admittedBy) by {{ $nicuAdmission->admittedBy->name ?? '—' }}@endif
            </div>
        </div>
        <a href="{{ route('nicu.admissions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to list
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-3">

        {{-- Baby column --}}
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header"><strong><i class="bi bi-person-heart"></i> Baby</strong></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width:170px;">Name</th><td>{{ $nicuAdmission->baby?->patient_name ?? '—' }}</td></tr>
                        <tr><th>Gender</th><td>{{ $nicuAdmission->baby?->gender ?? '—' }}</td></tr>
                        <tr><th>Birth At</th><td>{{ optional($nicuAdmission->birth_at)->format('Y-m-d H:i') ?? '—' }}</td></tr>
                        <tr><th>Birth Weight</th><td>{{ $nicuAdmission->birth_weight_grams ? rtrim(rtrim((string)$nicuAdmission->birth_weight_grams, '0'), '.') . ' g' : '—' }}</td></tr>
                        <tr><th>Birth Length</th><td>{{ $nicuAdmission->birth_length_cm ? rtrim(rtrim((string)$nicuAdmission->birth_length_cm, '0'), '.') . ' cm' : '—' }}</td></tr>
                        <tr><th>Head Circumference</th><td>{{ $nicuAdmission->head_circumference_cm ? rtrim(rtrim((string)$nicuAdmission->head_circumference_cm, '0'), '.') . ' cm' : '—' }}</td></tr>
                        <tr><th>Gestational Age</th><td>{{ $nicuAdmission->gestational_age_weeks ? $nicuAdmission->gestational_age_weeks . ' weeks' : '—' }}</td></tr>
                        <tr><th>APGAR (1 / 5 min)</th><td>{{ $nicuAdmission->apgar_1min ?? '—' }} / {{ $nicuAdmission->apgar_5min ?? '—' }}</td></tr>
                        <tr><th>Delivery</th><td>{{ $nicuAdmission->delivery_type ?? '—' }}</td></tr>
                        <tr><th>Multiple Birth</th><td>{{ $nicuAdmission->is_multiple_birth ? 'Yes' : 'No' }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Bed allocation --}}
            <div class="card mb-3">
                <div class="card-header"><strong><i class="bi bi-hospital"></i> Bed / Unit</strong></div>
                <div class="card-body">
                    @if($nicuAdmission->bed)
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info fs-6">{{ $nicuAdmission->bed->name }}</span>
                            <span class="text-muted small">{{ $nicuAdmission->bedType?->name }}</span>
                        </div>
                    @else
                        <div class="text-muted">No bed allocated yet.</div>
                    @endif

                    @if($nicuAdmission->servicePackage)
                        <hr class="my-2">
                        <div>
                            <i class="bi bi-box-seam text-primary"></i>
                            <strong>{{ $nicuAdmission->servicePackage->code }}</strong>
                            — {{ $nicuAdmission->servicePackage->name }}
                            <span class="text-primary ms-2">৳{{ number_format((float) $nicuAdmission->servicePackage->base_price, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mother + clinical --}}
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header"><strong><i class="bi bi-person"></i> Mother</strong></div>
                <div class="card-body">
                    @if($nicuAdmission->mother)
                        <table class="table table-sm mb-0">
                            <tr><th style="width:170px;">Name</th><td>{{ $nicuAdmission->mother->patient_name }}</td></tr>
                            <tr><th>MRN</th><td>{{ $nicuAdmission->mother->mrn ?? '—' }}</td></tr>
                            <tr><th>Mobile</th><td>{{ $nicuAdmission->mother->mobileno ?? '—' }}</td></tr>
                            @if($nicuAdmission->caseReference?->parentCase)
                                <tr><th>Mother's Case</th><td>#{{ $nicuAdmission->caseReference->parent_case_id }}</td></tr>
                            @endif
                        </table>
                    @else
                        <div class="text-muted">No mother record linked.</div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><strong><i class="bi bi-journal-medical"></i> Clinical Notes</strong></div>
                <div class="card-body">
                    @if($nicuAdmission->clinical_notes)
                        <pre class="mb-0" style="white-space:pre-wrap; font-family:inherit;">{{ $nicuAdmission->clinical_notes }}</pre>
                    @else
                        <div class="text-muted">No notes recorded.</div>
                    @endif
                </div>
            </div>

            {{-- Discharge panel --}}
            @if($nicuAdmission->status !== \App\Models\NicuAdmission::STATUS_DISCHARGED && auth()->user()?->can('nicu_discharge'))
                <div class="card border-success mb-3">
                    <div class="card-header bg-success-subtle"><strong><i class="bi bi-box-arrow-right"></i> Discharge</strong></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('nicu.admissions.discharge', $nicuAdmission) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Discharge Summary *</label>
                                <textarea name="discharge_summary" rows="3" class="form-control" required
                                          placeholder="Outcome, follow-up, parent counselling…"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success"
                                onclick="return confirm('Discharge this baby? Bed will be released.');">
                                <i class="bi bi-check2-circle"></i> Discharge Baby
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Already discharged --}}
            @if($nicuAdmission->status === \App\Models\NicuAdmission::STATUS_DISCHARGED)
                <div class="card border-secondary mb-3">
                    <div class="card-header bg-light"><strong><i class="bi bi-box-arrow-right"></i> Discharge Summary</strong></div>
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Discharged {{ optional($nicuAdmission->discharged_at)->format('Y-m-d H:i') }}
                            @if($nicuAdmission->dischargedBy) by {{ $nicuAdmission->dischargedBy->name ?? '—' }}@endif
                        </div>
                        <pre class="mb-0" style="white-space:pre-wrap; font-family:inherit;">{{ $nicuAdmission->discharge_summary }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
