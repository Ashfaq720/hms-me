@extends('backend.layouts.master')
@section('title', 'Master Data Center')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-database-fill text-primary"></i> Master Data Center</h4>
            <small class="text-muted">All hospital reference data in one place — click any tile to manage</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.centers.equipment') }}" class="btn btn-sm btn-outline-info"><i class="bi bi-plug"></i> Equipment</a>
            <a href="{{ route('admin.centers.inventory') }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-boxes"></i> Inventory</a>
            <a href="{{ route('admin.centers.billing') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-cash-stack"></i> Billing</a>
            <a href="{{ route('admin.centers.clinical') }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-heart-pulse"></i> Clinical</a>
        </div>
    </div>

    @foreach ($sections as $groupName => $items)
        <h6 class="text-muted mt-4 mb-2 text-uppercase fw-semibold small">
            <i class="bi bi-bookmark"></i> {{ $groupName }}
        </h6>
        <div class="row g-2 mb-3">
            @foreach ($items as $key => $cfg)
                @php $exists = \Illuminate\Support\Facades\Route::has($cfg['route']); @endphp
                <div class="col-md-3 col-lg-2 col-6">
                    <a href="{{ $exists ? route($cfg['route']) : '#' }}"
                       class="text-decoration-none {{ $exists ? '' : 'pe-none opacity-50' }}">
                        <div class="card border-0 shadow-sm h-100 master-tile">
                            <div class="card-body py-3 px-3 text-center">
                                <i class="bi bi-{{ $cfg['icon'] }} display-6 text-primary"></i>
                                <h6 class="mt-2 mb-0">{{ $cfg['label'] }}</h6>
                                <span class="badge bg-light text-muted small mt-1">{{ $counts[$key] ?? 0 }} records</span>
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
    .master-tile { transition: transform .15s ease, box-shadow .15s ease; }
    .master-tile:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(67,97,238,.15); }
</style>
@endpush
@endsection
