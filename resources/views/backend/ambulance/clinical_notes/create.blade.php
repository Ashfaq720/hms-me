@extends('backend.layouts.master')

@section('title', 'Add Clinical Notes')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Clinical Notes — Trip #{{ $trip->id }}</h1>
        <a href="{{ route('amb.trips.show', $trip) }}" class="btn btn-light">Back to Trip</a>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <strong>Patient:</strong>
            {{ $trip->request->patient?->name ?? ($trip->request->temp_patient_id ?? 'Unknown') }}
            &nbsp;|&nbsp;
            <strong>Status:</strong> {{ str_replace('_', ' ', $trip->status) }}
        </div>
        <div class="card-body">
            <form action="{{ route('amb.trips.clinical_notes.store', $trip) }}" method="POST">
                @csrf

                <h6 class="text-muted mb-3">Vital Signs</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Blood Pressure (BP)</label>
                        <input type="text" name="bp" class="form-control" value="{{ old('bp') }}" placeholder="120/80">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pulse (bpm)</label>
                        <input type="number" name="pulse" class="form-control" value="{{ old('pulse') }}" placeholder="90">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SpO₂ (%)</label>
                        <input type="number" step="0.01" name="spo2" class="form-control" value="{{ old('spo2') }}" placeholder="96">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Temperature (°F)</label>
                        <input type="number" step="0.01" name="temperature" class="form-control" value="{{ old('temperature') }}" placeholder="98.6">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Respiratory Rate</label>
                        <input type="number" name="respiratory_rate" class="form-control" value="{{ old('respiratory_rate') }}" placeholder="18">
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="text-muted mb-3">Support & Intervention</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="oxygen_given" value="1"
                                {{ old('oxygen_given') ? 'checked' : '' }} id="oxygen_given">
                            <label class="form-check-label" for="oxygen_given">Oxygen Given</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="ventilator_used" value="1"
                                {{ old('ventilator_used') ? 'checked' : '' }} id="ventilator_used">
                            <label class="form-check-label" for="ventilator_used">Ventilator Used</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Emergency Intervention <span class="text-danger">*</span></label>
                        <select name="emergency_intervention" class="form-select @error('emergency_intervention') is-invalid @enderror" required>
                            <option value="NONE" @selected(old('emergency_intervention','NONE') == 'NONE')>None</option>
                            <option value="CPR" @selected(old('emergency_intervention') == 'CPR')>CPR</option>
                            <option value="OXYGEN" @selected(old('emergency_intervention') == 'OXYGEN')>Oxygen</option>
                            <option value="IV_SUPPORT" @selected(old('emergency_intervention') == 'IV_SUPPORT')>IV Support</option>
                            <option value="DEFIBRILLATION" @selected(old('emergency_intervention') == 'DEFIBRILLATION')>Defibrillation</option>
                            <option value="OTHER" @selected(old('emergency_intervention') == 'OTHER')>Other</option>
                        </select>
                        @error('emergency_intervention')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Clinical Notes</label>
                    <textarea name="clinical_notes" class="form-control" rows="4"
                        placeholder="Describe patient condition, observations during transport...">{{ old('clinical_notes') }}</textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Notes</button>
                    <a href="{{ route('amb.trips.show', $trip) }}" class="btn btn-light ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
