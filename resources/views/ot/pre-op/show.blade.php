@extends('backend.layouts.master')

@section('title', 'Pre-Op — ' . $schedule->schedule_no)

@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="app-page-title mb-0">Pre-Op Checklist</h1>
            <div class="text-muted">
                {{ $schedule->schedule_no }} — {{ optional($schedule->surgeryRequest?->patient)->patient_name }}
                · {{ optional($schedule->room)->name }} · {{ $schedule->scheduled_start?->format('Y-m-d H:i') }}
            </div>
        </div>
        <a href="{{ route('ot.schedules.show', $schedule->id) }}" class="btn btn-outline-secondary">Back to Schedule</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    @if($checklist->exists)
        <div class="alert alert-info">
            Checklist progress: <strong>{{ $checklist->completionPercent() }}%</strong>
            @if($checklist->isReady())<span class="badge bg-success">Ready</span>@endif
            @if($checklist->emergency_override)<span class="badge bg-danger">Emergency Override</span>@endif
        </div>
    @endif

    <form action="{{ route('ot.pre-op.update', $schedule->id) }}" method="POST" class="card">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                @php
                    $items = [
                        'consent_obtained' => 'Consent Obtained',
                        'lab_completed' => 'Lab Tests Completed',
                        'radiology_completed' => 'Radiology Completed',
                        'fasting_confirmed' => 'NPO / Fasting Confirmed',
                        'blood_arranged' => 'Blood Arranged',
                        'allergy_reviewed' => 'Allergies Reviewed',
                        'vitals_recorded' => 'Vitals Recorded',
                        'anesthesia_clearance' => 'Anesthesia Clearance',
                        'doctor_clearance' => 'Doctor Clearance',
                        'nurse_confirmation' => 'Nurse Confirmation',
                        'site_marked' => 'Surgical Site Marked',
                        'id_band_verified' => 'Patient ID Band Verified',
                    ];
                @endphp
                @foreach($items as $key => $label)
                    <div class="col-md-6 col-lg-4">
                        <div class="form-check form-switch">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1"
                                   id="cb_{{ $key }}" @checked($checklist->{$key} ?? false)>
                            <label class="form-check-label" for="cb_{{ $key }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ $checklist->notes }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <div>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#overrideModal">
                    <i class="bi bi-exclamation-triangle"></i> Emergency Override
                </button>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-secondary">Save Progress</button>
            </div>
        </div>
    </form>

    {{-- Separate POST form so the PUT _method on the update form doesn't leak --}}
    <form action="{{ route('ot.pre-op.complete', $schedule->id) }}" method="POST" class="mt-2 text-end">
        @csrf
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check2-circle"></i> Mark Complete (Ready for OT)
        </button>
    </form>
</div>

<div class="modal fade" id="overrideModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('ot.pre-op.override', $schedule->id) }}" method="POST">@csrf
            <div class="modal-header"><h5 class="modal-title">Emergency Override</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-danger small">This will bypass mandatory pre-op checks. The action is fully audited.</div>
                <label class="form-label">Reason *</label>
                <textarea name="reason" class="form-control" required rows="3"></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-danger">Apply Override</button></div>
        </form>
    </div>
</div>
@endsection
