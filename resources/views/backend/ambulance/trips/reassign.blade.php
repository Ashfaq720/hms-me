@extends('backend.layouts.master')

@section('title', 'Reassign Trip #' . $trip->id)

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div>
            <h1 class="app-page-title">Reassign Trip #{{ $trip->id }}</h1>
            <p class="text-muted mb-0">
                Current status: <strong>{{ str_replace('_', ' ', $trip->status) }}</strong>
                &mdash; A reason is mandatory and will be recorded in the audit log.
            </p>
        </div>
        <a href="{{ route('amb.trips.show', $trip) }}" class="btn btn-light">Cancel</a>
    </div>

    <div class="row mt-4 justify-content-center">
        <div class="col-lg-8">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- Current assignment summary --}}
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning bg-opacity-10"><strong>Current Assignment</strong></div>
                <div class="card-body">
                    <div class="row row-cols-3 g-2 text-center">
                        <div>
                            <div class="text-muted small">Ambulance</div>
                            <strong>{{ $trip->ambulance->reg_no }}</strong>
                            <div class="small">{{ $trip->ambulance->type }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Driver</div>
                            <strong>{{ $trip->driver->name }}</strong>
                        </div>
                        <div>
                            <div class="text-muted small">Paramedic</div>
                            <strong>{{ $trip->paramedic?->name ?? '— None —' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>New Assignment</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('amb.trips.reassignStore', $trip) }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Ambulance <span class="text-danger">*</span></label>
                                <select name="ambulance_id" class="form-select @error('ambulance_id') is-invalid @enderror" required>
                                    <option value="">— Select —</option>
                                    @foreach($ambulances as $a)
                                        <option value="{{ $a->id }}"
                                            {{ old('ambulance_id', $trip->ambulance_id) == $a->id ? 'selected' : '' }}>
                                            {{ $a->reg_no }} ({{ $a->type }})
                                            @if($a->id === $trip->ambulance_id) — current @else — {{ $a->status }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('ambulance_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Only AVAILABLE + current shown. Compliance checks on Save.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Driver <span class="text-danger">*</span></label>
                                <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required>
                                    <option value="">— Select —</option>
                                    @foreach($drivers as $d)
                                        <option value="{{ $d->id }}"
                                            {{ old('driver_id', $trip->driver_id) == $d->id ? 'selected' : '' }}>
                                            {{ $d->name }}
                                            @if($d->id === $trip->driver_id) — current @endif
                                            (exp: {{ $d->license_expiry?->format('d M Y') ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('driver_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Paramedic <small class="text-muted">(Required for ALS/ICU/NEONATAL)</small></label>
                                <select name="paramedic_id" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach($paramedics as $p)
                                        <option value="{{ $p->id }}"
                                            {{ old('paramedic_id', $trip->paramedic_id) == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }} ({{ $p->certification }})
                                            @if($p->id === $trip->paramedic_id) — current @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Reason for Reassignment <span class="text-danger">*</span></label>
                                <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror"
                                    value="{{ old('reason') }}"
                                    placeholder="e.g. Vehicle breakdown, staff unavailability, emergency override..."
                                    required>
                                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">This is recorded permanently in the assignment audit log.</div>
                            </div>

                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-arrow-repeat me-1"></i> Confirm Reassignment
                                </button>
                                <a href="{{ route('amb.trips.show', $trip) }}" class="btn btn-light">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
