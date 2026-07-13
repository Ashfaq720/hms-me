@extends('backend.layouts.master')
@section('title', 'Reports Hub')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-file-earmark-bar-graph text-primary"></i> Reports Hub</h4>
            <small class="text-muted">Every report in the project — organised by area</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.hub.audit') }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-eye"></i> Audit Log</a>
            <a href="{{ route('admin.hub.wallet') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-wallet"></i> Doctor Wallet</a>
        </div>
    </div>

    @foreach ($sections as $sectionName => $items)
        <h6 class="text-muted mt-4 mb-2 text-uppercase fw-semibold small">
            <i class="bi bi-bookmark"></i> {{ $sectionName }}
        </h6>
        <div class="row g-2 mb-3">
            @foreach ($items as [$title, $route, $icon, $desc])
                @php $valid = $route && \Illuminate\Support\Facades\Route::has($route); @endphp
                <div class="col-md-3 col-lg-3 col-6">
                    <a href="{{ $valid ? route($route) : '#' }}"
                       class="text-decoration-none {{ $valid ? '' : 'opacity-50 pe-none' }}">
                        <div class="card border-0 shadow-sm h-100 report-tile">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-{{ $icon }} text-primary fs-3"></i>
                                    <div>
                                        <strong>{{ $title }}</strong>
                                        <div class="small text-muted">{{ $desc }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endforeach
</div>

@push('styles')
<style>
    .report-tile { transition: transform .12s ease, box-shadow .15s ease; }
    .report-tile:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(67,97,238,.12); }
</style>
@endpush
@endsection
