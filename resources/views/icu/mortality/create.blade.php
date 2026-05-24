@extends('backend.layouts.master')

@section('title', 'Record Mortality — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <div>
                <h1 class="app-page-title text-danger">Record Mortality</h1>
                <div class="text-muted">
                    {{ $admission->icu_case_id }} — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        @if (session('error')) <div class="alert alert-danger mt-2">{{ session('error') }}</div> @endif

        @if ($admission->mortalityAudit)
            <div class="alert alert-warning mt-2">
                Mortality already recorded — view the
                <a href="{{ route('icu.admissions.mortality.show', $admission->id) }}" class="alert-link">audit page</a>.
            </div>
        @endif

        <form method="POST" action="{{ route('icu.admissions.mortality.store', $admission->id) }}" class="mt-2">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Death Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="death_time"
                                value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Declared By (Doctor) <span class="text-danger">*</span></label>
                            <select name="death_declared_by" class="form-select" required>
                                <option value="">-- Select Doctor --</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Code Blue Event (optional)</label>
                            <select name="code_blue_event_id" class="form-select">
                                <option value="">--</option>
                                @foreach ($codeBlueEvents as $e)
                                    <option value="{{ $e->id }}">
                                        {{ $e->event_no }} — {{ $e->event_type }} ({{ $e->status }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Final Diagnosis <span class="text-danger">*</span></label>
                            <textarea name="final_diagnosis" class="form-control" rows="2" required>{{ old('final_diagnosis') }}</textarea>
                            @error('final_diagnosis')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Cause of Death <span class="text-danger">*</span></label>
                            <textarea name="cause_of_death" class="form-control" rows="3" required>{{ old('cause_of_death', $admission->mortalityAudit?->cause_of_death) }}</textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Resuscitation Details</label>
                            <textarea name="resuscitation_details" class="form-control" rows="3">{{ old('resuscitation_details', $admission->mortalityAudit?->resuscitation_details) }}</textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Body Handover To</label>
                            <input type="text" name="body_handover_to" class="form-control"
                                placeholder="Family member / mortuary / etc.">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-2">
                <button class="btn btn-danger">Record Mortality</button>
            </div>
        </form>
    </div>
@endsection
