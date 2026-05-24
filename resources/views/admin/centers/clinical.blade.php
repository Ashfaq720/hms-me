@extends('backend.layouts.master')
@section('title', 'Clinical Center')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-heart-pulse text-danger"></i> Clinical Center</h4>
            <small class="text-muted">All patient-facing modules — one-click access</small>
        </div>
        <a href="{{ route('admin.centers.master-data') }}" class="btn btn-sm btn-outline-primary">← Master Data</a>
    </div>

    <div class="row g-3 mb-3">
        @php $modules = [
            ['OPD',  $stats['opd_today'], $stats['opd_total'],    'primary',  'stethoscope',  'opd-patients.index',         'Today / All'],
            ['IPD',  $stats['ipd_admitted'], $stats['ipd_total'], 'info',     'hospital',     'ipd-patients.index',         'Admitted / All'],
            ['ER',   $stats['er_today'], $stats['er_total'],      'danger',   'heart',        'er.dashboard',               'Today / All'],
            ['ICU',  $stats['icu_admitted'], null,                'danger',   'heart-pulse',  'icu.dashboard',              'Admitted'],
            ['NICU', $stats['nicu_admitted'], null,               'warning',  'emoji-smile',  'nicu.dashboard',             'Admitted'],
            ['OT',   $stats['ot_scheduled'], $stats['ot_requests'],'primary', 'scissors',     'ot.dashboard',               'Scheduled / Requests'],
        ]; @endphp
        @foreach ($modules as [$name, $primary, $secondary, $colour, $icon, $route, $tooltip])
            <div class="col-md-4 col-lg-2">
                <a href="{{ \Illuminate\Support\Facades\Route::has($route) ? route($route) : '#' }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center clinical-tile" style="border-top:4px solid var(--bs-{{ $colour }}) !important;">
                        <div class="card-body py-3">
                            <i class="bi bi-{{ $icon }} display-5 text-{{ $colour }}"></i>
                            <h5 class="mt-2 mb-0">{{ $name }}</h5>
                            <h3 class="mb-0">{{ $primary }}@if (!is_null($secondary))<small class="text-muted"> / {{ $secondary }}</small>@endif</h3>
                            <small class="text-muted">{{ $tooltip }}</small>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 p-3 h-100">
                <small class="text-primary">Total Patients in System</small>
                <h2 class="mb-0">{{ number_format($stats['patients']) }}</h2>
                <a href="{{ route('patients.index') }}" class="btn btn-sm btn-link p-0">View all →</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 p-3 h-100">
                <small class="text-info">Active Encounters</small>
                <h2 class="mb-0">{{ number_format($stats['encounters']) }}</h2>
                <small class="text-muted">All-time across OPD/IPD/ER/ICU/OT</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 p-3 h-100">
                <small class="text-warning">Quick Actions</small>
                <div class="d-flex flex-wrap gap-1 mt-2">
                    @php $actions = [
                        ['opd-patients.create', 'New OPD', 'plus-lg'],
                        ['ipd-patients.create', 'New IPD', 'plus-lg'],
                        ['front_desk.er_registration', 'New ER', 'plus-lg'],
                        ['appointments.create', 'New Appointment', 'calendar-plus'],
                    ]; @endphp
                    @foreach ($actions as [$r, $label, $ico])
                        @if (\Illuminate\Support\Facades\Route::has($r))
                            <a href="{{ route($r) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-{{ $ico }}"></i> {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .clinical-tile { transition: transform .15s ease, box-shadow .2s ease; }
    .clinical-tile:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
</style>
@endpush
@endsection
