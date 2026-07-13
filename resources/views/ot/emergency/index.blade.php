@extends('backend.layouts.master')
@section('title','Emergency OT')
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between">
        <h1 class="app-page-title text-danger">Emergency OT</h1>
        <a href="{{ route('ot.emergency.create') }}" class="btn btn-danger"><i class="bi bi-exclamation-triangle"></i> New Emergency Case</a>
    </div>
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Schedule</th><th>Patient</th><th>Room</th><th>Start</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse($emergencies as $s)
                <tr>
                    <td>{{ $s->schedule_no }}</td>
                    <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                    <td>{{ optional($s->room)->name }}</td>
                    <td>{{ $s->scheduled_start?->format('Y-m-d H:i') }}</td>
                    <td><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('ot.schedules.show', $s->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @if(! $s->approved_at)
                            <form action="{{ route('ot.emergency.approve', $s->id) }}" method="POST" class="d-inline">@csrf
                                <button class="btn btn-sm btn-outline-success">Approve</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No emergency cases.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $emergencies->links() }}</div>
</div>
@endsection
