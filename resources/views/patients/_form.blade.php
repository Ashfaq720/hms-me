@php
    $genders = ['Male', 'Female', 'Other'];
    $maritals = ['Single', 'Married', 'Divorced', 'Widowed'];
    $bloods = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
@endphp

<div class="row g-3">
    <div class="col-md-9">
        <label class="form-label">Patient Name <span class="text-danger">*</span></label>
        <input type="text" name="patient_name" value="{{ old('patient_name', $patient->patient_name ?? '') }}"
            class="form-control" required>
        @error('patient_name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">DOB</label>
        <input type="date" name="dob" value="{{ old('dob', optional($patient->dob ?? null)->format('Y-m-d')) }}"
            class="form-control">
        @error('dob')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Mobile <span class="text-danger">*</span></label>
        <input type="text" name="mobileno" value="{{ old('mobileno', $patient->mobileno ?? '') }}"
            class="form-control" required>
        @error('mobileno')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $patient->email ?? '') }}" class="form-control">
        @error('email')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control">
        @error('image')
            <div class="text-danger small">{{ $message }}</div>
        @enderror

        @if (!empty($patient?->image))
            <div class="mt-2">
                <img src="{{ asset('storage/' . $patient->image) }}" width="90"
                    style="border-radius:10px;object-fit:cover;">
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <label class="form-label">Gender <span class="text-danger">*</span></label>
        <select name="gender" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach ($genders as $g)
                <option value="{{ $g }}" @selected(old('gender', $patient->gender ?? '') === $g)>{{ $g }}</option>
            @endforeach
        </select>
        @error('gender')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Marital Status <span class="text-danger">*</span></label>
        <select name="marital_status" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach ($maritals as $m)
                <option value="{{ $m }}" @selected(old('marital_status', $patient->marital_status ?? '') === $m)>{{ $m }}</option>
            @endforeach
        </select>
        @error('marital_status')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Blood Group</label>
        <select name="blood_group" class="form-select">
            <option value="">-- Select --</option>
            @foreach ($bloods as $b)
                <option value="{{ $b }}" @selected(old('blood_group', $patient->blood_group ?? '') === $b)>{{ $b }}</option>
            @endforeach
        </select>
        @error('blood_group')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address', $patient->address ?? '') }}</textarea>
        @error('address')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Guardian Name</label>
        <input type="text" name="guardian_name" value="{{ old('guardian_name', $patient->guardian_name ?? '') }}"
            class="form-control">
        @error('guardian_name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Patient Type</label>
        <input type="text" name="patient_type" value="{{ old('patient_type', $patient->patient_type ?? '') }}"
            class="form-control">
        @error('patient_type')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Identification Number</label>
        <input type="text" name="identification_number"
            value="{{ old('identification_number', $patient->identification_number ?? '') }}" class="form-control">
        @error('identification_number')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Known Allergies</label>
        <textarea name="known_allergies" class="form-control" rows="2">{{ old('known_allergies', $patient->known_allergies ?? '') }}</textarea>
        @error('known_allergies')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Note</label>
        <textarea name="note" class="form-control" rows="2">{{ old('note', $patient->note ?? '') }}</textarea>
        @error('note')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Insurance</label>
        <input type="string" name="insurance" value="{{ old('insurance', $patient->insurance ?? '') }}"
            class="form-control">
        @error('insurance')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Insurance Validity</label>
        <input type="date" name="insurance_validity"
            value="{{ old('insurance_validity', optional($patient->insurance_validity ?? null)->format('Y-m-d')) }}"
            class="form-control">
        @error('insurance_validity')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>


    <div class="col-md-12 d-flex gap-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_ipd" value="1" id="is_ipd"
                @checked(old('is_ipd', $patient->is_ipd ?? false))>
            <label class="form-check-label" for="is_ipd">Is Ipd</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_dead" value="1" id="is_dead"
                @checked(old('is_dead', $patient->is_dead ?? false))>
            <label class="form-check-label" for="is_dead">Is Dead</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                @checked(old('is_active', $patient->is_active ?? true))>
            <label class="form-check-label" for="is_active">Is Active</label>
        </div>
    </div>
</div>
