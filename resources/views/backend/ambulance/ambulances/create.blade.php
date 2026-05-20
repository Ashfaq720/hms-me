@extends('backend.layouts.master')

@section('title', 'Add Ambulance')

@section('content')
<div class="container">
    <h1>Add Ambulance</h1>
    <a href="{{ route('amb.ambulances.index') }}" class="btn btn-light">Back to List</a>

    <form action="{{ route('amb.ambulances.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="reg_no">Registration Number</label>
            <input type="text" class="form-control @error('reg_no') is-invalid @enderror" id="reg_no" name="reg_no" value="{{ old('reg_no') }}" required>
            @error('reg_no')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="type">Type</label>
            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                <option value="BLS" @selected(old('type') == 'BLS')>Basic Life Support (BLS)</option>
                <option value="EMERGENCY" @selected(old('type') == 'EMERGENCY')>Emergency</option>
                <option value="ALS" @selected(old('type') == 'ALS')>Advanced Life Support (ALS)</option>
                <option value="ICU" @selected(old('type') == 'ICU')>Intensive Care Unit (ICU)</option>
                <option value="NEONATAL" @selected(old('type') == 'NEONATAL')>Neonatal</option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="ownership">Ownership</label>
            <select class="form-control @error('ownership') is-invalid @enderror" id="ownership" name="ownership" required>
                <option value="HOSPITAL" @selected(old('ownership') == 'HOSPITAL')>Hospital</option>
                <option value="OUTSOURCED" @selected(old('ownership') == 'OUTSOURCED')>Outsourced</option>
            </select>
            @error('ownership')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="vendor_id">Vendor (if outsourced)</label>
            <select class="form-control @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id">
                <option value="">Select Vendor</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
                @endforeach
            </select>
            @error('vendor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="stretcher_capacity">Stretcher Capacity</label>
            <input type="number" class="form-control @error('stretcher_capacity') is-invalid @enderror" id="stretcher_capacity" name="stretcher_capacity" value="{{ old('stretcher_capacity') }}" required>
            @error('stretcher_capacity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="attendants_capacity">Attendant Capacity</label>
            <input type="number" class="form-control @error('attendants_capacity') is-invalid @enderror" id="attendants_capacity" name="attendants_capacity" value="{{ old('attendants_capacity') }}" required>
            @error('attendants_capacity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="oxygen_capacity">Oxygen Capacity (L)</label>
            <input type="text" class="form-control @error('oxygen_capacity') is-invalid @enderror" id="oxygen_capacity" name="oxygen_capacity" value="{{ old('oxygen_capacity') }}">
            @error('oxygen_capacity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="AVAILABLE" @selected(old('status') == 'AVAILABLE')>Available</option>
                <option value="ON_TRIP" @selected(old('status') == 'ON_TRIP')>On Trip</option>
                <option value="MAINTENANCE" @selected(old('status') == 'MAINTENANCE')>Under Maintenance</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="fitness_expiry">Fitness Expiry Date</label>
            <input type="date" class="form-control @error('fitness_expiry') is-invalid @enderror" id="fitness_expiry" name="fitness_expiry" value="{{ old('fitness_expiry') }}">
            @error('fitness_expiry')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="insurance_expiry">Insurance Expiry Date</label>
            <input type="date" class="form-control @error('insurance_expiry') is-invalid @enderror" id="insurance_expiry" name="insurance_expiry" value="{{ old('insurance_expiry') }}">
            @error('insurance_expiry')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary mt-4">Save Ambulance</button>
    </form>
</div>
@endsection
