@extends('backend.layouts.master')

@section('title', 'ICU Discharge — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <div>
                <h1 class="app-page-title">ICU Discharge</h1>
                <div class="text-muted">
                    {{ $admission->icu_case_id }} — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        @if (session('error')) <div class="alert alert-danger mt-2">{{ session('error') }}</div> @endif

        @if (! empty($blockers))
            <div class="alert alert-warning mt-2">
                <strong>Discharge blockers:</strong>
                <ul class="mb-0">
                    @foreach ($blockers as $b) <li>{{ $b }}</li> @endforeach
                </ul>
                <small>Tick <em>Force override</em> below to proceed anyway.</small>
            </div>
        @endif

        <form method="POST" action="{{ route('icu.admissions.discharge.store', $admission->id) }}" class="mt-2">
            @csrf

            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Outcome & Time</h6>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Outcome <span class="text-danger">*</span></label>
                            <select name="outcome" class="form-select" required>
                                @foreach (['Recovered', 'Referred', 'LAMA'] as $o)
                                    <option value="{{ $o }}">{{ $o }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">For death use Mortality, for transfer use Transfer.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discharge Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="discharge_time"
                                value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Condition at Discharge <span class="text-danger">*</span></label>
                            <input type="text" name="condition_at_discharge"
                                value="{{ old('condition_at_discharge', $defaults['condition_at_discharge']) }}"
                                class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-2">
                <div class="card-body">
                    <h6 class="card-title">Clinical Summary</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Admission Diagnosis</label>
                            <textarea class="form-control" rows="2" disabled>{{ $defaults['admission_diagnosis'] }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Final Diagnosis <span class="text-danger">*</span></label>
                            <textarea name="final_diagnosis" class="form-control" rows="2" required>{{ old('final_diagnosis', $defaults['final_diagnosis']) }}</textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">ICU Course Summary</label>
                            <textarea name="icu_course_summary" class="form-control" rows="3">{{ old('icu_course_summary', $defaults['icu_course_summary']) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Procedures (auto-pulled from completed orders)</label>
                            <textarea name="procedures_summary" class="form-control" rows="3">{{ old('procedures_summary', $defaults['procedures_summary']) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ventilator Summary (auto-pulled from equipment usage)</label>
                            <textarea name="ventilator_summary" class="form-control" rows="3">{{ old('ventilator_summary', $defaults['ventilator_summary']) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Investigations</label>
                            <textarea name="investigation_summary" class="form-control" rows="3">{{ old('investigation_summary', $defaults['investigation_summary']) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Medications (auto-pulled from antibiotic log)</label>
                            <textarea name="medication_summary" class="form-control" rows="3">{{ old('medication_summary', $defaults['medication_summary']) }}</textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Follow-up Advice</label>
                            <textarea name="followup_advice" class="form-control" rows="2">{{ old('followup_advice', $defaults['followup_advice']) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            @if (! empty($blockers))
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="hidden" name="force" value="0">
                            <input class="form-check-input" type="checkbox" id="force" name="force" value="1">
                            <label class="form-check-label text-danger" for="force">
                                Force override discharge blockers
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            <div class="text-end mt-2">
                <button class="btn btn-primary">Finalize Discharge</button>
            </div>
        </form>
    </div>
@endsection
