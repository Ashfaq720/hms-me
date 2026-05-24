@extends('backend.layouts.master')
@section('title', 'OT Safety Checklists')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0"><i class="bi bi-clipboard2-check"></i> OT Safety Checklists</h4>
            <small class="text-muted">WHO-style 3-phase surgical safety checklist used on every OT schedule.</small>
        </div>
        <a href="{{ route('ot.setup.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to OT Setup
        </a>
    </div>

    <div class="row g-3">
        @foreach ($checklists as $phaseIdx => $cl)
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-{{ ['primary','warning','success'][$phaseIdx] ?? 'secondary' }} text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-{{ ['1-circle','2-circle','3-circle'][$phaseIdx] ?? 'check-circle' }}"></i>
                            {{ $cl['phase'] }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <ol class="list-group list-group-numbered">
                            @foreach ($cl['items'] as $item)
                                <li class="list-group-item border-0 px-0">{{ $item }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="alert alert-info mt-3">
        <i class="bi bi-info-circle"></i>
        <strong>How it's used:</strong> Each <code>ot_surgery_schedules</code> row stores the live checklist state in
        the <code>pre_op_checklist</code> JSON column. The Pre-Op / Time-Out / Sign-Out phases must be ticked
        before the schedule status can advance to <em>Patient Received</em>, <em>Anesthesia Started</em>, and <em>Surgery Completed</em>.
    </div>
</div>
@endsection
