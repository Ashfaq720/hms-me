@extends('backend.layouts.master')
@section('title','Post-Op')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title mb-0">Post-Operative Management</h1>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('ot.post-op.index', ['scope' => 'active']) }}"    class="btn btn-{{ ($scope ?? 'active') === 'active'    ? 'primary' : 'outline-primary' }}">Active</a>
            <a href="{{ route('ot.post-op.index', ['scope' => 'completed']) }}" class="btn btn-{{ ($scope ?? 'active') === 'completed' ? 'primary' : 'outline-primary' }}">Completed</a>
            <a href="{{ route('ot.post-op.index', ['scope' => 'all']) }}"       class="btn btn-{{ ($scope ?? 'active') === 'all'       ? 'primary' : 'outline-primary' }}">All</a>
        </div>
    </div>
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Schedule</th><th>Patient</th><th>Status</th><th>Notes Saved</th><th>Signed At</th><th></th></tr></thead>
        <tbody>
            @forelse($schedules as $s)
                <tr>
                    <td>{{ $s->schedule_no }}</td>
                    <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                    <td><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                    <td>{{ $s->postOpNote ? 'Yes' : '—' }}</td>
                    <td>{{ optional($s->postOpNote?->signed_at)->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="text-end"><a href="{{ route('ot.post-op.show', $s->id) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Nothing here.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $schedules->links() }}</div>
</div>
@endsection
