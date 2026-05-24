@extends('backend.layouts.master')
@section('title', 'NICU Feeding')
@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="bi bi-cup-hot"></i> Feeding Schedules</h4>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Baby</th><th>Feed Type</th><th>Interval</th><th>Volume (ml)</th><th>Start</th><th>End</th><th>Active</th></tr></thead>
                <tbody>
                @forelse ($schedules as $s)
                    <tr>
                        <td>{{ optional(optional($s->admission)->patient)->patient_name ?? '—' }}</td>
                        <td><span class="badge bg-primary">{{ $s->feed_type }}</span></td>
                        <td>q{{ $s->interval_hours }}h</td>
                        <td>{{ $s->volume_ml }}</td>
                        <td>{{ $s->start_date?->toDateString() }}</td>
                        <td>{{ $s->end_date?->toDateString() ?? '—' }}</td>
                        <td><span class="badge bg-{{ $s->is_active ? 'success' : 'secondary' }}">{{ $s->is_active ? 'Active' : 'Closed' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No schedules</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($schedules, 'links'))<div class="p-3">{{ $schedules->links() }}</div>@endif
    </div>
</div>
@endsection
