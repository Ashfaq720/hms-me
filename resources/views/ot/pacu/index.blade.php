@extends('backend.layouts.master')
@section('title','PACU')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title mb-0">Recovery / PACU</h1>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('ot.pacu.index', ['scope' => 'active']) }}"     class="btn btn-{{ ($scope ?? 'active') === 'active'     ? 'primary' : 'outline-primary' }}">In PACU</a>
            <a href="{{ route('ot.pacu.index', ['scope' => 'discharged']) }}" class="btn btn-{{ ($scope ?? 'active') === 'discharged' ? 'primary' : 'outline-primary' }}">Discharged</a>
            <a href="{{ route('ot.pacu.index', ['scope' => 'all']) }}"        class="btn btn-{{ ($scope ?? 'active') === 'all'        ? 'primary' : 'outline-primary' }}">All</a>
        </div>
    </div>
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Patient</th><th>Schedule</th><th>Bed</th><th>Admitted</th><th>Discharged</th><th>Aldrete</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse($records as $r)
                <tr>
                    <td>{{ optional($r->schedule?->surgeryRequest?->patient)->patient_name }}</td>
                    <td><a href="{{ route('ot.pacu.show', $r->surgery_schedule_id) }}">{{ optional($r->schedule)->schedule_no }}</a></td>
                    <td>{{ $r->bed_no ?? '—' }}</td>
                    <td>{{ $r->admitted_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ optional($r->discharged_at)->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ $r->aldrete_score ?? '—' }}/10</td>
                    <td>
                        @if($r->status === 'Discharged')
                            <span class="badge bg-success">Discharged</span>
                        @else
                            <span class="badge bg-info">{{ $r->status ?? 'In Recovery' }}</span>
                        @endif
                    </td>
                    <td class="text-end"><a href="{{ route('ot.pacu.show', $r->surgery_schedule_id) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-3">PACU empty for this filter.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $records->links() }}</div>
</div>
@endsection
