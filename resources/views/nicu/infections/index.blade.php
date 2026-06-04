@extends('backend.layouts.master')
@section('title', 'NICU Infections')
@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="bi bi-shield-exclamation"></i> Infection Control</h4>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Baby</th><th>Type</th><th>Organism</th><th>Detected</th><th>Resolved</th><th>Isolation</th><th>Antibiotics</th><th>Cluster?</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($infections as $i)
                    <tr class="{{ $i->alert_cluster ? 'table-danger' : '' }}">
                        <td>{{ optional(optional($i->admission)->patient)->patient_name ?? '—' }}</td>
                        <td>{{ $i->infection_type }}</td>
                        <td>{{ $i->organism }}</td>
                        <td>{{ $i->detected_on?->toDateString() }}</td>
                        <td>{{ $i->resolved_on?->toDateString() ?? '—' }}</td>
                        <td>{{ $i->isolation_required }}</td>
                        <td>{{ $i->antibiotics_used }}</td>
                        <td>@if ($i->alert_cluster)<span class="badge bg-danger">CLUSTER</span>@endif</td>
                        <td><span class="badge bg-{{ $i->status === 'active' ? 'warning text-dark' : 'success' }}">{{ ucfirst($i->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No infections</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($infections, 'links'))<div class="p-3">{{ $infections->links() }}</div>@endif
    </div>
</div>
@endsection
