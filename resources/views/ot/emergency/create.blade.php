@extends('backend.layouts.master')
@section('title','New Emergency OT')
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between"><h1 class="app-page-title text-danger"><i class="bi bi-exclamation-triangle"></i> New Emergency Surgery</h1>
        <a href="{{ route('ot.emergency.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <div class="alert alert-warning small">Fast-track workflow. Conflict checks skipped. All actions audited.</div>

    <form action="{{ route('ot.emergency.store') }}" method="POST">@csrf
        <div class="card"><div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Patient *</label>
                    <select name="patient_id" class="form-select" required>
                        <option value="">— select —</option>
                        @foreach($patients as $p)<option value="{{ $p->id }}">{{ $p->patient_name }} ({{ $p->mrn }})</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Encounter Type *</label>
                    <select name="encounter_type" class="form-select" required>
                        @foreach(['ER','IPD','OPD'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Primary Surgeon</label>
                    <select name="primary_surgeon_id" class="form-select">
                        <option value="">—</option>
                        @foreach($surgeons as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Surgery Type</label>
                    <select name="surgery_type_id" class="form-select">
                        <option value="">—</option>
                        @foreach($surgeryTypes as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Emergency OT Room *</label>
                    <select name="ot_room_id" class="form-select" required>
                        <option value="">— select —</option>
                        @foreach($rooms as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Start *</label>
                    <input type="datetime-local" name="scheduled_start" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
                <div class="col-md-6"><label class="form-label">End *</label>
                    <input type="datetime-local" name="scheduled_end" class="form-control" required value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}">
                </div>
                <div class="col-12"><label class="form-label">Diagnosis</label><textarea name="diagnosis" rows="2" class="form-control"></textarea></div>
                <div class="col-12"><label class="form-label">Clinical Indication *</label><textarea name="clinical_indication" rows="2" class="form-control" required></textarea></div>
                <div class="col-12"><label class="form-label">Fast-Track Reason *</label><textarea name="reason" rows="2" class="form-control" required></textarea></div>
            </div>
        </div><div class="card-footer text-end"><button class="btn btn-danger">Create Emergency Schedule</button></div></div>
    </form>
</div>
@endsection
