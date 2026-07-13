@extends('backend.layouts.master')
@section('title', 'NICU Medications')
@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="bi bi-capsule-pill"></i> Medication Orders (Weight-based MAR)</h4>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Baby</th><th>Drug</th><th>Dose (mg/kg)</th><th>Weight (kg)</th><th>Total Dose</th><th>Route</th><th>Freq</th><th>Start</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($orders as $o)
                    <tr>
                        <td>{{ optional(optional($o->admission)->patient)->patient_name ?? '—' }}</td>
                        <td><strong>{{ $o->drug_name }}</strong></td>
                        <td>{{ $o->dose_per_kg_mg }} mg/kg</td>
                        <td>{{ $o->weight_used_kg }}</td>
                        <td><strong>{{ $o->total_dose_mg }} mg</strong></td>
                        <td>{{ $o->route }}</td>
                        <td>{{ $o->frequency }}</td>
                        <td>{{ $o->start_date?->toDateString() }}</td>
                        <td><span class="badge bg-{{ $o->status === 'active' ? 'success' : 'secondary' }}">{{ $o->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No orders</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($orders, 'links'))<div class="p-3">{{ $orders->links() }}</div>@endif
    </div>
</div>
@endsection
