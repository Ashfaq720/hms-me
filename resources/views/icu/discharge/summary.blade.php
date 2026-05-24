@extends('backend.layouts.master')

@section('title', 'Discharge Summary — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <div>
                <h1 class="app-page-title">ICU Discharge Summary</h1>
                <div class="text-muted">
                    {{ $admission->icu_case_id }} —
                    {{ $admission->patient?->patient_name }}
                    <span class="badge bg-secondary ms-2">{{ $admission->status }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">Print</button>
                <a href="{{ route('icu.admissions.show', $admission->id) }}"
                    class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif

        @php $s = $admission->dischargeSummary; @endphp

        @if (! $s)
            <div class="alert alert-warning mt-2">No discharge summary recorded yet.</div>
        @else
            <div class="card mt-2">
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width:200px;">Patient</th><td>{{ $admission->patient?->patient_name }} ({{ $admission->patient?->mrn ?? '-' }})</td></tr>
                        <tr><th>Case</th><td>{{ $admission->icu_case_id }} ({{ $admission->icu_type }})</td></tr>
                        <tr><th>Admission Time</th><td>{{ $admission->admission_time?->format('Y-m-d H:i') }}</td></tr>
                        <tr><th>Discharge Time</th><td>{{ $admission->discharge_time?->format('Y-m-d H:i') ?? '-' }}</td></tr>
                        <tr><th>Outcome</th><td>{{ $admission->outcome ?? '-' }}</td></tr>
                        <tr><th>Condition at Discharge</th><td>{{ $s->condition_at_discharge ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>

            @php
                $sections = [
                    'Admission Diagnosis' => $s->admission_diagnosis,
                    'Final Diagnosis'     => $s->final_diagnosis,
                    'ICU Course'          => $s->icu_course_summary,
                    'Procedures'          => $s->procedures_summary,
                    'Ventilator'          => $s->ventilator_summary,
                    'Investigations'      => $s->investigation_summary,
                    'Medications'         => $s->medication_summary,
                    'Follow-up Advice'    => $s->followup_advice,
                ];
            @endphp

            @foreach ($sections as $label => $content)
                <div class="card mt-2"><div class="card-body">
                    <h6 class="card-title">{{ $label }}</h6>
                    <div class="text-pre">{{ $content ?? '-' }}</div>
                </div></div>
            @endforeach
        @endif

        @if ($admission->mortalityAudit)
            <div class="alert alert-dark mt-2">
                Mortality audit recorded — see
                <a href="{{ route('icu.admissions.mortality.show', $admission->id) }}" class="alert-link">Mortality Audit</a>.
            </div>
        @endif

        @if ($admission->transfers->isNotEmpty())
            <div class="card mt-2">
                <div class="card-body p-2">
                    <h6 class="card-title px-2 pt-2">Transfer History</h6>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-2">Time</th><th>Type</th><th>From → To</th><th>Reason</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($admission->transfers as $t)
                                <tr>
                                    <td class="ps-2"><small>{{ $t->transfer_time?->format('Y-m-d H:i') }}</small></td>
                                    <td>{{ $t->transfer_type }}</td>
                                    <td><small>{{ $t->from_unit }} #{{ $t->from_bed_id ?? '-' }} → {{ $t->to_unit }} #{{ $t->to_bed_id ?? '-' }}</small></td>
                                    <td><small>{{ $t->transfer_reason }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <style>.text-pre{white-space:pre-line}</style>
@endsection
