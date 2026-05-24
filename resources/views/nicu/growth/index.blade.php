@extends('backend.layouts.master')
@section('title', 'NICU Growth')
@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="bi bi-graph-up"></i> Growth Records</h4>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Date</th><th>Baby</th><th>Weight (g)</th><th>Length (cm)</th><th>HC (cm)</th><th>Change %</th><th>Alert</th></tr></thead>
                <tbody>
                @forelse ($records as $r)
                    <tr class="{{ $r->alert_weight_loss ? 'table-warning' : '' }}">
                        <td>{{ $r->measured_on?->toDateString() }}</td>
                        <td>{{ optional(optional($r->admission)->patient)->patient_name ?? '—' }}</td>
                        <td>{{ number_format($r->weight_g, 0) }}</td>
                        <td>{{ $r->length_cm }}</td>
                        <td>{{ $r->head_circumference_cm }}</td>
                        <td class="{{ ($r->weight_change_pct ?? 0) < 0 ? 'text-danger' : 'text-success' }}">{{ $r->weight_change_pct }}%</td>
                        <td>@if ($r->alert_weight_loss)<span class="badge bg-warning text-dark">Weight loss > 10%</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No growth records</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($records, 'links'))<div class="p-3">{{ $records->links() }}</div>@endif
    </div>
</div>
@endsection
