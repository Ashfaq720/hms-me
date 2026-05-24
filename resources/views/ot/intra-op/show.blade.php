@extends('backend.layouts.master')
@section('title','Intra-Op — ' . $schedule->schedule_no)
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between">
        <h1 class="app-page-title">Operative Record — {{ $schedule->schedule_no }}</h1>
        <a href="{{ route('ot.schedules.show', $schedule->id) }}" class="btn btn-outline-secondary">Back</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form action="{{ route('ot.intra-op.update', $schedule->id) }}" method="POST">@csrf @method('PUT')
        <div class="card"><div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Incision Time</label>
                    <input type="datetime-local" name="incision_time" class="form-control" value="{{ optional($record->incision_time)->format('Y-m-d\TH:i') }}">
                </div>
                <div class="col-md-3"><label class="form-label">Closure Time</label>
                    <input type="datetime-local" name="closure_time" class="form-control" value="{{ optional($record->closure_time)->format('Y-m-d\TH:i') }}">
                </div>
                <div class="col-md-3"><label class="form-label">Blood Loss (ml)</label>
                    <input type="number" step="0.1" name="blood_loss_ml" class="form-control" value="{{ $record->blood_loss_ml }}">
                </div>
                <div class="col-md-3"><label class="form-label">Blood Transfused (ml)</label>
                    <input type="number" step="0.1" name="blood_transfused_ml" class="form-control" value="{{ $record->blood_transfused_ml }}">
                </div>
                <div class="col-12"><label class="form-label">Operative Findings</label><textarea name="operative_findings" rows="2" class="form-control">{{ $record->operative_findings }}</textarea></div>
                <div class="col-12"><label class="form-label">Procedure Performed</label><textarea name="procedure_performed" rows="2" class="form-control">{{ $record->procedure_performed }}</textarea></div>
                <div class="col-12"><label class="form-label">Operative Notes *</label><textarea name="operative_notes" rows="4" class="form-control" required>{{ $record->operative_notes }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Specimens Collected</label><textarea name="specimens_collected" rows="2" class="form-control">{{ $record->specimens_collected }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Implants Used</label><textarea name="implants_used" rows="2" class="form-control">{{ $record->implants_used }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Complications</label><textarea name="complications" rows="2" class="form-control">{{ $record->complications }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Post-Op Instructions</label><textarea name="post_op_instructions" rows="2" class="form-control">{{ $record->post_op_instructions }}</textarea></div>
                <div class="col-12">
                    <div class="form-check"><input type="hidden" name="counts_verified" value="0">
                        <input class="form-check-input" type="checkbox" name="counts_verified" value="1" id="cv" @checked($record->counts_verified)>
                        <label class="form-check-label" for="cv">Instrument/Sponge/Needle counts verified</label>
                    </div>
                </div>
            </div>
        </div><div class="card-footer text-end"><button class="btn btn-primary">Save Record</button></div></div>
    </form>
</div>
@endsection
