@extends('backend.layouts.master')
@section('title','OT Room — ' . $room->name)
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between">
        <h1 class="app-page-title">{{ $room->name }} <small class="text-muted">({{ $room->code }})</small></h1>
        <a href="{{ route('ot.rooms.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
    <div class="row g-3">
        <div class="col-md-4"><div class="card">
            <div class="card-header"><strong>Details</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Type</dt><dd class="col-7">{{ $room->type ?? '—' }}</dd>
                    <dt class="col-5">Floor</dt><dd class="col-7">{{ optional($room->floor)->name ?? '—' }}</dd>
                    <dt class="col-5">Block</dt><dd class="col-7">{{ $room->block ?? '—' }}</dd>
                    <dt class="col-5">Emergency</dt><dd class="col-7">{{ $room->is_emergency ? 'Yes' : 'No' }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7"><span class="badge bg-info">{{ $room->status }}</span></dd>
                </dl>
            </div>
        </div></div>
        <div class="col-md-8"><div class="card">
            <div class="card-header"><strong>Upcoming Surgeries</strong></div>
            <ul class="list-group list-group-flush">
                @forelse($upcoming as $u)
                    <li class="list-group-item">
                        <a href="{{ route('ot.schedules.show', $u->id) }}">{{ $u->schedule_no }}</a> —
                        {{ $u->scheduled_start?->format('Y-m-d H:i') }} —
                        {{ optional($u->surgeryRequest?->patient)->patient_name }}
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center">No upcoming surgeries.</li>
                @endforelse
            </ul>
        </div></div>
    </div>
</div>
@endsection
