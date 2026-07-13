@extends('backend.layouts.master')
@section('title', 'Package Expiry Alerts')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> Expiring in 7 Days</h4>
        <a href="{{ route('packages.reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Reports</a>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light"><tr><th>Enrolment</th><th>Patient</th><th>Package</th><th>End Date</th><th>Days Left</th><th class="text-end">Outstanding ৳</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                @php $left = $r->end_date ? now()->diffInDays($r->end_date, false) : null; @endphp
                <tr>
                    <td>{{ $r->enrollment_no }}</td>
                    <td>{{ optional($r->patient)->patient_name }}<br><small class="text-muted">{{ optional($r->patient)->mrn }}</small></td>
                    <td>{{ optional($r->package)->name }}</td>
                    <td>{{ $r->end_date?->toDateString() }}</td>
                    <td><span class="badge bg-{{ $left < 1 ? 'danger' : ($left < 3 ? 'warning text-dark' : 'info') }}">{{ $left }}d</span></td>
                    <td class="text-end">{{ number_format(max((float) $r->agreed_price - (float) $r->paid_amount, 0), 2) }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst($r->status) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No expiring enrolments in next 7 days</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
