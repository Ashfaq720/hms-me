@extends('backend.layouts.master')
@section('title','Surgery Execution')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title mb-0">Surgery Execution / Intra-Operative</h1>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('ot.intra-op.index', ['scope' => 'active']) }}"    class="btn btn-{{ ($scope ?? 'active') === 'active'    ? 'primary' : 'outline-primary' }}">Active</a>
            <a href="{{ route('ot.intra-op.index', ['scope' => 'completed']) }}" class="btn btn-{{ ($scope ?? 'active') === 'completed' ? 'primary' : 'outline-primary' }}">Completed</a>
            <a href="{{ route('ot.intra-op.index', ['scope' => 'all']) }}"       class="btn btn-{{ ($scope ?? 'active') === 'all'       ? 'primary' : 'outline-primary' }}">All</a>
        </div>
    </div>

    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Schedule</th><th>Patient</th><th>Room</th><th>Status</th><th>Actual Start</th><th>Op Notes</th><th></th></tr></thead>
        <tbody>
            @forelse($schedules as $s)
                <tr>
                    <td>{{ $s->schedule_no }}</td>
                    <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                    <td>{{ optional($s->room)->name }}</td>
                    <td><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                    <td>{{ optional($s->actual_start)->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>
                        @if($s->intraOpRecord && $s->intraOpRecord->operative_notes)
                            <span class="badge bg-success">Saved</span>
                        @else
                            <span class="badge bg-secondary">Empty</span>
                        @endif
                    </td>
                    <td class="text-end"><a href="{{ route('ot.intra-op.show', $s->id) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No cases for this filter.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $schedules->links() }}</div>
</div>
@endsection
