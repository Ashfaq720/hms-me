@extends('backend.layouts.master')

@section('title', 'Edit Ambulance')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Edit Ambulance — {{ $ambulance->reg_no }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('amb.ambulances.show', $ambulance) }}" class="btn btn-light">View Detail</a>
            <a href="{{ route('amb.ambulances.index') }}" class="btn btn-light">Back to List</a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('amb.ambulances.update', $ambulance) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    {{-- Registration Number --}}
                    <div class="col-md-4">
                        <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                        <input type="text" name="reg_no"
                            class="form-control @error('reg_no') is-invalid @enderror"
                            value="{{ old('reg_no', $ambulance->reg_no) }}" required>
                        @error('reg_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Type --}}
                    <div class="col-md-4">
                        <label class="form-label">Ambulance Type <span class="text-danger">*</span></label>
                        <select name="type" id="amb_type"
                            class="form-select @error('type') is-invalid @enderror" required>
                            <option value="BLS"       @selected(old('type', $ambulance->type) == 'BLS')>Basic Life Support (BLS)</option>
                            <option value="GENERAL"   @selected(old('type', $ambulance->type) == 'GENERAL')>General</option>
                            <option value="EMERGENCY" @selected(old('type', $ambulance->type) == 'EMERGENCY')>Emergency</option>
                            <option value="ALS"       @selected(old('type', $ambulance->type) == 'ALS')>Advanced Life Support (ALS)</option>
                            <option value="ICU"       @selected(old('type', $ambulance->type) == 'ICU')>ICU</option>
                            <option value="NEONATAL"  @selected(old('type', $ambulance->type) == 'NEONATAL')>Neonatal</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="AVAILABLE"   @selected(old('status', $ambulance->status) == 'AVAILABLE')>Available</option>
                            <option value="ON_TRIP"     @selected(old('status', $ambulance->status) == 'ON_TRIP')>On Trip</option>
                            <option value="MAINTENANCE" @selected(old('status', $ambulance->status) == 'MAINTENANCE')>Under Maintenance</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Ownership --}}
                    <div class="col-md-4">
                        <label class="form-label">Ownership <span class="text-danger">*</span></label>
                        <select name="ownership" id="ownership"
                            class="form-select @error('ownership') is-invalid @enderror" required>
                            <option value="HOSPITAL"   @selected(old('ownership', $ambulance->ownership) == 'HOSPITAL')>Hospital Owned</option>
                            <option value="OUTSOURCED" @selected(old('ownership', $ambulance->ownership) == 'OUTSOURCED')>Outsourced</option>
                        </select>
                        @error('ownership')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Vendor --}}
                    <div class="col-md-4" id="vendor_field"
                        style="{{ old('ownership', $ambulance->ownership) === 'OUTSOURCED' ? '' : 'display:none' }}">
                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}"
                                    @selected(old('vendor_id', $ambulance->vendor_id) == $vendor->id)>
                                    {{ $vendor->vendor_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Capacity --}}
                    <div class="col-md-2">
                        <label class="form-label">Stretcher Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="stretcher_capacity" min="1"
                            class="form-control @error('stretcher_capacity') is-invalid @enderror"
                            value="{{ old('stretcher_capacity', $ambulance->stretcher_capacity) }}" required>
                        @error('stretcher_capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Attendant Seats <span class="text-danger">*</span></label>
                        <input type="number" name="attendants_capacity" min="0"
                            class="form-control @error('attendants_capacity') is-invalid @enderror"
                            value="{{ old('attendants_capacity', $ambulance->attendants_capacity) }}" required>
                        @error('attendants_capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Oxygen Capacity</label>
                        <input type="text" name="oxygen_capacity" class="form-control"
                            value="{{ old('oxygen_capacity', $ambulance->oxygen_capacity) }}" placeholder="e.g. 10L">
                    </div>

                    {{-- Expiry Dates --}}
                    <div class="col-md-4">
                        <label class="form-label">Fitness Expiry Date</label>
                        <input type="date" name="fitness_expiry"
                            class="form-control @error('fitness_expiry') is-invalid @enderror"
                            value="{{ old('fitness_expiry', $ambulance->fitness_expiry?->format('Y-m-d')) }}">
                        @error('fitness_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($ambulance->isFitnessExpired())
                            <div class="form-text text-danger"><i class="bi bi-exclamation-circle"></i> Currently expired — ambulance cannot be assigned.</div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Insurance Expiry Date</label>
                        <input type="date" name="insurance_expiry"
                            class="form-control @error('insurance_expiry') is-invalid @enderror"
                            value="{{ old('insurance_expiry', $ambulance->insurance_expiry?->format('Y-m-d')) }}">
                        @error('insurance_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($ambulance->isInsuranceExpired())
                            <div class="form-text text-danger"><i class="bi bi-exclamation-circle"></i> Currently expired — ambulance cannot be assigned.</div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">License Validity Date</label>
                        <input type="date" name="license_validity"
                            class="form-control @error('license_validity') is-invalid @enderror"
                            value="{{ old('license_validity', $ambulance->license_validity?->format('Y-m-d')) }}">
                        @error('license_validity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($ambulance->license_validity && $ambulance->license_validity->isPast())
                            <div class="form-text text-danger"><i class="bi bi-exclamation-circle"></i> Currently expired — ambulance cannot be assigned.</div>
                        @endif
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Ambulance</button>
                    <a href="{{ route('amb.ambulances.show', $ambulance) }}" class="btn btn-light ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('ownership').addEventListener('change', function () {
        document.getElementById('vendor_field').style.display =
            this.value === 'OUTSOURCED' ? '' : 'none';
    });
</script>
@endpush
@endsection
