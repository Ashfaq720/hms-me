@extends('backend.layouts.master')
@section('title','Anesthesia')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Anesthesia Management</h1>
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Schedule</th><th>Patient</th><th>Room</th><th>Status</th><th>Induction</th><th></th></tr></thead>
        <tbody>
            @forelse($schedules as $s)
                <tr>
                    <td>{{ $s->schedule_no }}</td>
                    <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                    <td>{{ optional($s->room)->name }}</td>
                    <td><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                    <td>{{ optional($s->anesthesiaRecord?->induction_time)->format('H:i') ?? '—' }}</td>
                    <td class="text-end"><a href="{{ route('ot.anesthesia.show', $s->id) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No active anesthesia cases.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $schedules->links() }}</div>
</div>
@endsection
