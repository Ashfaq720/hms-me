@extends('backend.layouts.master')
@section('title','OT Teams')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">OT Team Management</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Schedule</th><th>Patient</th><th>Room</th><th>Start</th><th>Team</th><th></th></tr></thead>
        <tbody>
            @forelse($schedules as $s)
                <tr>
                    <td>{{ $s->schedule_no }}</td>
                    <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                    <td>{{ optional($s->room)->name }}</td>
                    <td>{{ $s->scheduled_start?->format('Y-m-d H:i') }}</td>
                    <td>{{ $s->teamMembers->count() }} members</td>
                    <td class="text-end"><a href="{{ route('ot.teams.show', $s->id) }}" class="btn btn-sm btn-outline-primary">Manage</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No active schedules.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $schedules->links() }}</div>
</div>
@endsection
