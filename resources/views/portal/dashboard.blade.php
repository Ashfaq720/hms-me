@extends('portal.layout')
@section('title', 'My Dashboard')

@section('content')
{{-- HERO --}}
<div class="pp-hero mb-4 d-flex flex-wrap align-items-center gap-3">
    <div class="d-flex align-items-center gap-3 flex-grow-1">
        @if ($patient->image)
            <img src="{{ str_starts_with($patient->image, 'http') ? $patient->image : asset('storage/'.$patient->image) }}"
                 alt="patient" class="rounded-circle border border-3 border-white shadow"
                 style="width:80px; height:80px; object-fit:cover;">
        @else
            <div class="rounded-circle bg-white shadow d-flex align-items-center justify-content-center"
                 style="width:80px; height:80px;">
                <i class="bi bi-person-fill text-secondary" style="font-size:2rem;"></i>
            </div>
        @endif
        <div>
            <small class="text-muted text-uppercase fw-semibold" style="letter-spacing:.05em;">Welcome back</small>
            <h3 class="mb-1">{{ $patient->patient_name }}</h3>
            <div class="d-flex flex-wrap gap-3 small text-muted">
                <span><i class="bi bi-card-text"></i> <code>{{ $patient->mrn ?? '—' }}</code></span>
                @if ($patient->blood_group)<span><i class="bi bi-droplet-fill text-danger"></i> {{ $patient->blood_group }}</span>@endif
                @if ($patient->portal_last_login_at)
                    <span><i class="bi bi-clock-history"></i> Last login {{ \Carbon\Carbon::parse($patient->portal_last_login_at)->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="text-end d-none d-md-block">
        <small class="text-muted d-block">Today</small>
        <strong class="fs-5">{{ now()->format('D, d M Y') }}</strong>
    </div>
</div>

{{-- KPI Tiles --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-tile" style="--accent:#3b82f6; --accent-bg:rgba(59,130,246,.1);">
            <div class="icon"><i class="bi bi-calendar3"></i></div>
            <div class="label">Total Visits</div>
            <div class="value">{{ $stats['total_visits'] }}</div>
            <div class="sub">All-time</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        @php
            $unpaidColor = $stats['unpaid_bills'] > 0 ? '#ef4444' : '#10b981';
            $unpaidBg    = $stats['unpaid_bills'] > 0 ? 'rgba(239,68,68,.1)' : 'rgba(16,185,129,.1)';
        @endphp
        <div class="kpi-tile" style="--accent:{{ $unpaidColor }}; --accent-bg:{{ $unpaidBg }};">
            <div class="icon"><i class="bi bi-receipt"></i></div>
            <div class="label">Unpaid Bills</div>
            <div class="value">{{ $stats['unpaid_bills'] }}</div>
            <div class="sub">Due ৳ {{ number_format($stats['total_due'], 0) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile" style="--accent:#8b5cf6; --accent-bg:rgba(139,92,246,.1);">
            <div class="icon"><i class="bi bi-prescription2"></i></div>
            <div class="label">Prescriptions</div>
            <div class="value">{{ $stats['prescriptions'] }}</div>
            <div class="sub">Active &amp; past</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile" style="--accent:#f59e0b; --accent-bg:rgba(245,158,11,.1);">
            <div class="icon"><i class="bi bi-eyedropper"></i></div>
            <div class="label">Lab Tests</div>
            <div class="value">{{ $stats['lab_orders'] }}</div>
            <div class="sub">All-time</div>
        </div>
    </div>
</div>

{{-- Quick actions --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('portal.bills') }}" class="quick-action">
            <span class="icon" style="background:rgba(239,68,68,.1); color:#dc2626;"><i class="bi bi-credit-card"></i></span>
            <div>
                <div class="fw-semibold">Pay Bills</div>
                <small class="text-muted">View & pay</small>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('portal.prescriptions') }}" class="quick-action">
            <span class="icon" style="background:rgba(139,92,246,.1); color:#7c3aed;"><i class="bi bi-prescription2"></i></span>
            <div>
                <div class="fw-semibold">Prescriptions</div>
                <small class="text-muted">View medications</small>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('portal.profile') }}" class="quick-action">
            <span class="icon" style="background:rgba(59,130,246,.1); color:#2563eb;"><i class="bi bi-person-vcard"></i></span>
            <div>
                <div class="fw-semibold">My Profile</div>
                <small class="text-muted">Update password</small>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="tel:999" class="quick-action">
            <span class="icon" style="background:rgba(239,68,68,.1); color:#dc2626;"><i class="bi bi-telephone-fill"></i></span>
            <div>
                <div class="fw-semibold">Emergency</div>
                <small class="text-muted">Call 999</small>
            </div>
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Visit timeline --}}
    <div class="col-lg-6">
        <div class="portal-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history text-primary me-2"></i>Visit Timeline</span>
                <small class="text-muted">last 15</small>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0 pp-table">
                    <thead><tr><th>Type</th><th>Date</th><th>Ref</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($visits as $v)
                            <tr>
                                <td><span class="pp-badge {{ $v->type === 'IPD' ? 'success' : 'info' }}">{{ $v->type }}</span></td>
                                <td><small>{{ \Carbon\Carbon::parse($v->date)->format('d M Y') }}</small></td>
                                <td><small><code>{{ $v->reference }}</code></small></td>
                                <td><small class="text-muted">{{ $v->status }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-inbox display-6 d-block opacity-25"></i>No visits yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent bills --}}
    <div class="col-lg-6">
        <div class="portal-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt text-danger me-2"></i>Recent Bills</span>
                <a href="{{ route('portal.bills') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0 pp-table">
                    <thead><tr><th>Bill</th><th class="text-end">Grand</th><th class="text-end">Due</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($bills as $b)
                            <tr>
                                <td><small><strong>{{ $b->bill_no }}</strong></small><br><small class="text-muted">{{ \Carbon\Carbon::parse($b->bill_date)->format('d M') }}</small></td>
                                <td class="text-end">৳ {{ number_format($b->grand_total, 0) }}</td>
                                <td class="text-end {{ $b->balance_due > 0.01 ? 'text-danger fw-bold' : '' }}">৳ {{ number_format($b->balance_due, 0) }}</td>
                                <td><span class="pp-badge {{ $b->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($b->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-inbox display-6 d-block opacity-25"></i>No bills</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent prescriptions --}}
    <div class="col-12">
        <div class="portal-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-prescription2 text-success me-2"></i>Recent Prescriptions</span>
                <a href="{{ route('portal.prescriptions') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0 pp-table">
                    <thead><tr><th>Date</th><th>Rx No</th><th>Doctor</th><th>Findings</th><th>Medicines</th></tr></thead>
                    <tbody>
                        @forelse ($prescriptions as $rx)
                            <tr>
                                <td><small>{{ \Carbon\Carbon::parse($rx->date ?? $rx->created_at)->format('d M Y') }}</small></td>
                                <td><strong>{{ $rx->prescription_no ?? '#'.$rx->id }}</strong></td>
                                <td><small>{{ optional($rx->doctor)->name ?? '—' }}</small></td>
                                <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($rx->findings ?? '—', 60) }}</small></td>
                                <td><span class="pp-badge info">{{ $rx->medicines->count() }} drugs</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox display-6 d-block opacity-25"></i>No prescriptions</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
