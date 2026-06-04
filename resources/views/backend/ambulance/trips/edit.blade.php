@extends('backend.layouts.master')

@section('title', 'Edit Trip #' . $trip->id)

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div>
            <h1 class="app-page-title">Edit Trip #{{ $trip->id }}</h1>
            <p class="text-muted mb-0">
                Status: <strong>{{ str_replace('_', ' ', $trip->status) }}</strong>
                @if(!$canEditPickup)
                    &mdash; <span class="text-warning">Pickup location is locked (patient onboard or later)</span>
                @endif
            </p>
        </div>
        <a href="{{ route('amb.trips.show', $trip) }}" class="btn btn-light">Cancel</a>
    </div>

    <div class="row mt-4 justify-content-center">
        <div class="col-lg-7">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><strong>Trip / Request Details</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('amb.trips.update', $trip) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">

                            {{-- Pickup location (locked after PATIENT_ONBOARD) --}}
                            @if($canEditPickup)
                            <div class="col-12">
                                <label class="form-label">Pickup Location <span class="text-danger">*</span></label>
                                <input type="text" name="pick_up_location" class="form-control @error('pick_up_location') is-invalid @enderror"
                                    value="{{ old('pick_up_location', $trip->request->pick_up_location) }}" required>
                                @error('pick_up_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @else
                            <div class="col-12">
                                <label class="form-label">Pickup Location</label>
                                <input type="text" class="form-control" value="{{ $trip->request->pick_up_location }}" disabled>
                                <div class="form-text text-warning">Locked — patient is already onboard.</div>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">Drop Location</label>
                                <input type="text" name="drop_location" class="form-control"
                                    value="{{ old('drop_location', $trip->request->drop_location) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Destination Hospital / ER</label>
                                <input type="text" name="destination_hospital" class="form-control"
                                    value="{{ old('destination_hospital', $trip->request->destination_hospital) }}"
                                    placeholder="e.g. City General Hospital ER">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    @foreach(['CRITICAL','HIGH','NORMAL','LOW'] as $p)
                                        <option value="{{ $p }}" {{ old('priority', $trip->request->priority) === $p ? 'selected' : '' }}>
                                            {{ ucfirst(strtolower($p)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Case Tag</label>
                                <select name="case_tag" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach(['TRAUMA','STROKE','CARDIAC','RESPIRATORY','OTHER'] as $tag)
                                        <option value="{{ $tag }}" {{ old('case_tag', $trip->request->case_tag) === $tag ? 'selected' : '' }}>
                                            {{ ucfirst(strtolower($tag)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ETA (minutes — manual override)</label>
                                <input type="number" name="eta_minutes" class="form-control @error('eta_minutes') is-invalid @enderror"
                                    value="{{ old('eta_minutes', $trip->eta_minutes) }}" min="1" max="999">
                                @error('eta_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Reason for Edit <span class="text-danger">*</span></label>
                                <input type="text" name="edit_reason" class="form-control @error('edit_reason') is-invalid @enderror"
                                    value="{{ old('edit_reason') }}"
                                    placeholder="Why is this change being made?" required>
                                @error('edit_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Changes
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
