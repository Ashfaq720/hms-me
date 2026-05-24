@extends('backend.layouts.master')

@section('title', 'Surgery Schedules')

@section('content')
<div class="container-fluid">
    <div class="app-page-head d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <h1 class="app-page-title mb-0">Surgery Schedules</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('ot.schedules.calendar') }}" class="btn btn-info"><i class="bi bi-calendar3"></i> Calendar</a>
            <a href="{{ route('ot.schedules.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Schedule</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form class="card card-body mb-3" method="GET">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <input type="date" name="date" class="form-control" style="width: auto;" value="{{ request('date') }}">

            <select name="room_id" class="form-select" style="width: auto; min-width: 160px;">
                <option value="">All Rooms</option>
                @foreach($rooms as $r)<option value="{{ $r->id }}" @selected(request('room_id') == $r->id)>{{ $r->name }}</option>@endforeach
            </select>

            <select name="status" class="form-select" style="width: auto; min-width: 160px;">
                <option value="">All Status</option>
                @foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>@endforeach
            </select>

            <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
            @if(request()->hasAny(['date','room_id','status']))
                <a href="{{ route('ot.schedules.index') }}" class="btn btn-outline-secondary" title="Clear filters">
                    <i class="bi bi-x-circle"></i>
                </a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Schedule</th><th>Patient</th><th>Procedure</th><th>Room</th><th>Start</th><th>End</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($schedules as $s)
                        <tr>
                            <td>
                                <a href="{{ route('ot.schedules.show', $s->id) }}">{{ $s->schedule_no }}</a>
                                @if($s->emergency_fast_track)<span class="badge bg-danger ms-1">ER</span>@endif
                            </td>
                            <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                            <td>{{ optional($s->surgeryRequest?->surgeryType)->name ?? '—' }}</td>
                            <td>{{ optional($s->room)->name }}</td>
                            <td>{{ $s->scheduled_start?->format('Y-m-d H:i') }}</td>
                            <td>{{ $s->scheduled_end?->format('H:i') }}</td>
                            <td><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                            <td class="text-end"><a href="{{ route('ot.schedules.show', $s->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No schedules.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $schedules->links() }}</div>
</div>
@endsection
