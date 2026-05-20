@extends('backend.layouts.master')

@section('title', 'Mortality Audit — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <div>
                <h1 class="app-page-title text-dark">Mortality Audit</h1>
                <div class="text-muted">
                    {{ $admission->icu_case_id }} — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif

        @php $a = $admission->mortalityAudit; @endphp

        <div class="card mt-2">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th style="width:200px;">Death Time</th><td>{{ $a->death_time?->format('Y-m-d H:i') }}</td></tr>
                    <tr><th>Cause</th><td>{{ $a->cause_of_death }}</td></tr>
                    <tr><th>Declared By (Doctor)</th><td>#{{ $a->death_declared_by }}</td></tr>
                    <tr><th>Code Blue</th><td>
                        @if ($a->codeBlueEvent)
                            <a href="{{ route('icu.admissions.emergency.show', [$admission->id, $a->codeBlueEvent->id]) }}">
                                {{ $a->codeBlueEvent->event_no }}
                            </a>
                        @else - @endif
                    </td></tr>
                    <tr><th>Resuscitation Details</th><td><div class="text-pre">{{ $a->resuscitation_details ?? '-' }}</div></td></tr>
                    <tr><th>Body Handover</th><td>{{ $a->body_handover_to ?? '-' }}</td></tr>
                    <tr><th>Audit Status</th><td>
                        @php $col = match ($a->audit_status) { 'Completed' => 'success', 'InReview' => 'warning', default => 'secondary' }; @endphp
                        <span class="badge bg-{{ $col }}">{{ $a->audit_status }}</span>
                    </td></tr>
                </table>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">Audit Committee Review</h6>
                <form method="POST" action="{{ route('icu.admissions.mortality.review', $admission->id) }}"
                    class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">Preventability <span class="text-danger">*</span></label>
                        <select name="preventability" class="form-select" required>
                            @foreach (['Indeterminate', 'Preventable', 'NonPreventable'] as $p)
                                <option value="{{ $p }}" {{ $a->preventability === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Contributing Factors</label>
                        <input type="text" name="contributing_factors" class="form-control"
                            value="{{ old('contributing_factors', $a->contributing_factors) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Clinical Remarks</label>
                        <textarea name="clinical_remarks" class="form-control" rows="2">{{ old('clinical_remarks', $a->clinical_remarks) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Committee Remarks</label>
                        <textarea name="committee_remarks" class="form-control" rows="2">{{ old('committee_remarks', $a->committee_remarks) }}</textarea>
                    </div>
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary btn-sm">Save Review</button>
                    </div>
                </form>

                @if ($a->reviewed_at)
                    <div class="text-muted small mt-2">
                        Reviewed by #{{ $a->reviewed_by }} on {{ $a->reviewed_at->format('Y-m-d H:i') }}.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>.text-pre{white-space:pre-line}</style>
@endsection
