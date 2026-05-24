@extends('backend.layouts.master')
@section('title', 'Package Management')
@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-box-seam text-primary"></i> Package Management</h4>
            <small class="text-muted">All hospital packages — Master, services, pricing, enrolments &amp; reports in one place</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('packages.reports.index') }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports
            </a>
            <a href="{{ route('packages.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> New Package
            </a>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    {{-- 4 KPI cards --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body py-2 px-3">
                    <small class="text-primary"><i class="bi bi-box-seam"></i> Total Packages</small>
                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body py-2 px-3">
                    <small class="text-success"><i class="bi bi-check-circle"></i> Active</small>
                    <h4 class="mb-0">{{ $stats['active'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                <div class="card-body py-2 px-3">
                    <small class="text-info"><i class="bi bi-people"></i> Enrolments</small>
                    <h4 class="mb-0">{{ $stats['enrolments'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                <div class="card-body py-2 px-3">
                    <small class="text-warning"><i class="bi bi-cash-stack"></i> Revenue</small>
                    <h4 class="mb-0">৳ {{ number_format($stats['revenue'], 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter chips by type --}}
    @php
        $typeIcons = [
            'OPD' => ['stethoscope', 'primary'],
            'IPD' => ['hospital', 'info'],
            'OT'  => ['scissors', 'danger'],
            'ICU' => ['heart-pulse', 'danger'],
            'CCU' => ['heart', 'warning'],
            'NICU'=> ['emoji-smile', 'warning'],
            'MATERNITY' => ['gift', 'success'],
            'PATHOLOGY' => ['eyedropper', 'info'],
            'RADIOLOGY' => ['broadcast', 'primary'],
            'DIAGNOSTIC'=> ['activity', 'success'],
            'PHARMACY'  => ['capsule', 'primary'],
            'PHYSIOTHERAPY' => ['person-arms-up', 'info'],
            'DENTAL' => ['emoji-smile-upside-down', 'info'],
            'CORPORATE' => ['briefcase', 'secondary'],
            'WELLNESS'  => ['heart', 'success'],
        ];
        $currentType = request('type');
    @endphp
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-wrap gap-1 align-items-center">
                <small class="text-muted me-2"><i class="bi bi-funnel"></i> Filter by type:</small>

                <a href="{{ route('packages.index') }}"
                    class="btn btn-sm {{ ! $currentType ? 'btn-dark' : 'btn-outline-dark' }}">
                    All <span class="badge bg-white text-dark ms-1">{{ $stats['total'] }}</span>
                </a>

                @foreach (\App\Models\Package::categories() as $code => $label)
                    @php [$icon, $colour] = $typeIcons[$code] ?? ['box', 'secondary']; @endphp
                    @if (($typeCounts[$code] ?? 0) > 0)
                        <a href="{{ route('packages.index', ['type' => $code]) }}"
                            class="btn btn-sm {{ $currentType === $code ? 'btn-' . $colour : 'btn-outline-' . $colour }}">
                            <i class="bi bi-{{ $icon }}"></i> {{ $code }}
                            <span class="badge bg-{{ $currentType === $code ? 'light text-' . $colour : $colour }} ms-1">{{ $typeCounts[$code] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>

            <form method="GET" class="d-flex gap-2 mt-2">
                @if ($currentType)<input type="hidden" name="type" value="{{ $currentType }}">@endif
                <input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="🔍 Search name or code…">
                <select name="status" class="form-select form-select-sm" style="width:160px;">
                    <option value="">All status</option>
                    @foreach (['active', 'inactive', 'archived'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>
    </div>

    {{-- Packages grid (card layout) --}}
    @if ($packages->count())
        <div class="row g-3">
            @foreach ($packages as $p)
                @php
                    [$pkgIcon, $pkgColour] = $typeIcons[$p->package_type] ?? ['box', 'secondary'];
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 package-card" style="border-top:4px solid var(--bs-{{ $pkgColour }}) !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-{{ $pkgColour }} bg-opacity-15 text-{{ $pkgColour }}">
                                        <i class="bi bi-{{ $pkgIcon }}"></i> {{ $p->package_type }}
                                    </span>
                                    @if ($p->admission_type && $p->admission_type !== 'ANY')
                                        <span class="badge bg-light text-muted">{{ $p->admission_type }}</span>
                                    @endif
                                    @php $sCol = ['active' => 'success', 'inactive' => 'secondary', 'archived' => 'dark'][$p->status] ?? 'secondary'; @endphp
                                    <span class="badge bg-{{ $sCol }}">{{ ucfirst($p->status) }}</span>
                                </div>
                                <code class="text-muted small">{{ $p->code }}</code>
                            </div>

                            <h6 class="mb-1">{{ $p->name }}</h6>

                            @if ($p->description)
                                <small class="text-muted d-block mb-2" style="height:32px;overflow:hidden;">{{ \Illuminate\Support\Str::limit($p->description, 80) }}</small>
                            @endif

                            <div class="row g-1 small text-muted mb-2">
                                <div class="col-6"><i class="bi bi-building"></i> {{ optional($p->department)->name ?? '—' }}</div>
                                <div class="col-6"><i class="bi bi-house"></i> {{ optional($p->bedType)->name ?? '—' }}</div>
                                <div class="col-6"><i class="bi bi-calendar3"></i> {{ $p->validity_days ?? '—' }} days</div>
                                <div class="col-6"><i class="bi bi-percent"></i> {{ $p->discount ?? 0 }}% off</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <div>
                                    <small class="text-muted d-block">Base price</small>
                                    <strong class="text-{{ $pkgColour }}">৳ {{ number_format((float) $p->total_amount, 0) }}</strong>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-info bg-opacity-15 text-info" title="Services">
                                        <i class="bi bi-list-check"></i> {{ $p->services->count() }}
                                    </span>
                                    <span class="badge bg-warning bg-opacity-15 text-warning" title="Price rules">
                                        <i class="bi bi-tag"></i> {{ $p->priceRules->count() ?? 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 d-flex justify-content-end gap-1 pt-0">
                            <a href="{{ route('packages.show', $p->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('packages.edit', $p->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('packages.destroy', $p->id) }}"
                                  onsubmit="return confirm('Archive this package?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Archive"><i class="bi bi-archive"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $packages->links() }}</div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-box display-1 text-muted"></i>
                <h5 class="mt-3">No packages match your filter</h5>
                <p class="text-muted">Try clearing the filter or create a new package.</p>
                <div class="mt-3">
                    <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">Clear filter</a>
                    <a href="{{ route('packages.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Package
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .package-card { transition: transform .12s ease, box-shadow .15s ease; }
    .package-card:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(0,0,0,.08); }
</style>
@endpush
@endsection
