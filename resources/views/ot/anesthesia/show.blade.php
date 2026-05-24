@extends('backend.layouts.master')
@section('title','Anesthesia — ' . $schedule->schedule_no)
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between">
        <h1 class="app-page-title">Anesthesia — {{ $schedule->schedule_no }}</h1>
        <a href="{{ route('ot.schedules.show', $schedule->id) }}" class="btn btn-outline-secondary">Back</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <form action="{{ $record->exists ? route('ot.anesthesia.update', $schedule->id) : route('ot.anesthesia.store', $schedule->id) }}" method="POST">@csrf
        @if($record->exists) @method('PUT') @endif
        <div class="card"><div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Anesthesia Type</label>
                    <select name="anesthesia_type_id" class="form-select">
                        <option value="">—</option>
                        @foreach($types as $t)<option value="{{ $t->id }}" @selected($record->anesthesia_type_id == $t->id)>{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Anesthetist ID</label><input name="anesthetist_id" class="form-control" value="{{ $record->anesthetist_id }}"></div>
                <div class="col-md-4"><label class="form-label">ASA Grade</label><input name="asa_grade" class="form-control" value="{{ $record->asa_grade }}"></div>
                <div class="col-md-4"><label class="form-label">Induction Time</label>
                    <input type="datetime-local" name="induction_time" class="form-control" value="{{ optional($record->induction_time)->format('Y-m-d\TH:i') }}"></div>
                <div class="col-md-4"><label class="form-label">Recovery Time</label>
                    <input type="datetime-local" name="recovery_time" class="form-control" value="{{ optional($record->recovery_time)->format('Y-m-d\TH:i') }}"></div>
                <div class="col-12"><label class="form-label">Pre-Anesthesia Assessment</label><textarea name="pre_anesthesia_assessment" class="form-control" rows="2">{{ $record->pre_anesthesia_assessment }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Drugs Used</label><textarea name="drugs_used" class="form-control" rows="3">{{ $record->drugs_used }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Airway Management</label><textarea name="airway_management" class="form-control" rows="3">{{ $record->airway_management }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Complications</label><textarea name="complications" class="form-control" rows="2">{{ $record->complications }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Post-Anesthesia Notes</label><textarea name="post_anesthesia_notes" class="form-control" rows="2">{{ $record->post_anesthesia_notes }}</textarea></div>
            </div>
        </div><div class="card-footer text-end"><button class="btn btn-primary">Save</button></div></div>
    </form>
</div>
@endsection
