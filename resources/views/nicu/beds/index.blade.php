@extends('backend.layouts.master')

@section('title', 'NICU Bed Grid')

@section('content')
<div class="container-fluid">

    <div class="app-page-head d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0"><i class="bi bi-thermometer-half text-primary"></i> Incubator / Warmer Grid</h1>
            <div class="text-muted small">Live occupancy of NICU beds grouped by type.</div>
        </div>
        <a href="{{ route('nicu.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </div>

    @forelse($bedTypes as $type)
        @php $rows = $beds->where('bed_type_id', $type->id); @endphp
        @if($rows->count())
            <div class="card mb-3">
                <div class="card-header py-2">
                    <strong>{{ $type->name }}</strong>
                    <span class="badge bg-light text-dark border ms-2">{{ $rows->count() }} bed(s)</span>
                </div>
                <div class="card-body py-2">
                    <div class="row g-2">
                        @foreach($rows as $bed)
                            @php $occ = $occupants[$bed->id] ?? null; @endphp
                            <div class="col-md-3 col-sm-6">
                                <div class="border rounded p-2 d-flex justify-content-between align-items-center
                                    {{ $occ ? 'bg-danger-subtle border-danger' : 'bg-success-subtle border-success' }}">
                                    <div>
                                        <div class="fw-semibold">{{ $bed->name }}</div>
                                        @if($occ)
                                            <a href="{{ route('nicu.admissions.show', $occ) }}" class="small text-decoration-none">
                                                {{ $occ->baby?->patient_name ?? $occ->admission_no }}
                                            </a>
                                        @else
                                            <small class="text-success">Available</small>
                                        @endif
                                    </div>
                                    <span class="badge {{ $occ ? 'bg-danger' : 'bg-success' }}">
                                        {{ $occ ? 'Occupied' : 'Free' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @empty
        <div class="alert alert-warning">No NICU bed types configured yet. Go to <a href="{{ route('bed-types.index') }}">Bed Types</a> and add types like <em>Incubator</em>, <em>Warmer</em>, or <em>NICU</em>.</div>
    @endforelse

</div>
@endsection
