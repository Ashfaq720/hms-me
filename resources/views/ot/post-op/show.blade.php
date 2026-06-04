@extends('backend.layouts.master')
@section('title','Post-Op — ' . $schedule->schedule_no)
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between">
        <h1 class="app-page-title">Post-Op Notes — {{ $schedule->schedule_no }}</h1>
        <a href="{{ route('ot.schedules.show', $schedule->id) }}" class="btn btn-outline-secondary">Back</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <form action="{{ $note->exists ? route('ot.post-op.update', $schedule->id) : route('ot.post-op.store', $schedule->id) }}" method="POST">@csrf
        @if($note->exists)@method('PUT')@endif
        <div class="card"><div class="card-body">
            <div class="row g-3">
                <div class="col-12"><label class="form-label">Procedure Summary *</label><textarea name="procedure_summary" rows="3" class="form-control" required>{{ $note->procedure_summary }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Immediate Findings</label><textarea name="immediate_findings" rows="2" class="form-control">{{ $note->immediate_findings }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Post-Op Diagnosis</label><textarea name="post_op_diagnosis" rows="2" class="form-control">{{ $note->post_op_diagnosis }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Orders</label><textarea name="orders" rows="3" class="form-control">{{ $note->orders }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Medications</label><textarea name="medications" rows="3" class="form-control">{{ $note->medications }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Care Instructions</label><textarea name="care_instructions" rows="2" class="form-control">{{ $note->care_instructions }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Follow-up Plan</label><textarea name="follow_up_plan" rows="2" class="form-control">{{ $note->follow_up_plan }}</textarea></div>
                <div class="col-md-3"><label class="form-label">Disposition</label>
                    <select name="disposition" class="form-select">
                        <option value="">—</option>
                        @foreach(['PACU','Ward','ICU','CCU','Home'] as $d)<option value="{{ $d }}" @selected($note->disposition === $d)>{{ $d }}</option>@endforeach
                    </select>
                </div>
            </div>
        </div><div class="card-footer text-end"><button class="btn btn-primary">Save Notes</button></div></div>
    </form>
</div>
@endsection
