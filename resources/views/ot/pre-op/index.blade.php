@extends('backend.layouts.master')

@section('title', 'Pre-Operative')

@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3"><h1 class="app-page-title">Pre-Operative Checklist</h1></div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <form method="GET" class="card card-body mb-3">
        <div class="d-flex gap-2">
            <input type="date" name="date" class="form-control" value="{{ request('date', now()->toDateString()) }}">
            <button class="btn btn-secondary">Filter</button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Schedule</th><th>Patient</th><th>Room</th><th>Time</th><th>Pre-Op Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($schedules as $s)
                        @php $cl = $s->preOpChecklist; @endphp
                        <tr>
                            <td>{{ $s->schedule_no }}</td>
                            <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                            <td>{{ optional($s->room)->name }}</td>
                            <td>{{ $s->scheduled_start?->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($cl)
                                    <div class="progress" style="height:6px;width:120px">
                                        <div class="progress-bar bg-{{ $cl->isReady() ? 'success' : 'warning' }}" style="width: {{ $cl->completionPercent() }}%"></div>
                                    </div>
                                    <small>{{ $cl->completionPercent() }}% @if($cl->emergency_override)<span class="badge bg-danger">OVERRIDE</span>@endif</small>
                                @else
                                    <span class="badge bg-secondary">Not started</span>
                                @endif
                            </td>
                            <td class="text-end"><a href="{{ route('ot.pre-op.show', $s->id) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No pre-op items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $schedules->links() }}</div>
</div>
@endsection
