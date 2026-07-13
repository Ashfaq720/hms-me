@php
    $genders = ['Male', 'Female', 'Other'];
    $maritals = ['Single', 'Married', 'Divorced', 'Widowed'];
    $bloods = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    // Optional common doctor types
    $doctorTypes = ['Full Time', 'Part Time', 'Visiting', 'On Call'];
@endphp

<div class="row g-3">

    {{-- Name --}}
    <div class="col-md-8">
        <label class="form-label">Doctor Name <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $doctor->name ?? '') }}" class="form-control" required>
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Phone --}}
    <div class="col-md-4">
        <label class="form-label">Phone <span class="text-danger">*</span></label>
        <input type="text" name="phone" value="{{ old('phone', $doctor->phone ?? '') }}" class="form-control"
            required>
        @error('phone')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Emergency Phone --}}
    <div class="col-md-4">
        <label class="form-label">Emergency Phone</label>
        <input type="text" name="emergency_phone"
            value="{{ old('emergency_phone', $doctor->emergency_phone ?? '') }}" class="form-control">
        @error('emergency_phone')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Email --}}
    <div class="col-md-4">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" value="{{ old('email', $doctor->email ?? '') }}" class="form-control"
            required>
        @error('email')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Image --}}
    <div class="col-md-4">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control">
        @error('image')
            <div class="text-danger small">{{ $message }}</div>
        @enderror

        @if (!empty($doctor?->image))
            <div class="mt-2">
                <img src="{{ asset('storage/' . $doctor->image) }}" width="90"
                    style="border-radius:10px;object-fit:cover;">
            </div>
        @endif
    </div>

    {{-- Identification Number --}}
    <div class="col-md-4">
        <label class="form-label">Identification Number</label>
        <input type="text" name="identification_number"
            value="{{ old('identification_number', $doctor->identification_number ?? '') }}" class="form-control">
        @error('identification_number')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Qualification --}}
    <div class="col-md-4">
        <label class="form-label">Qualification</label>
        <input type="text" name="qualification" value="{{ old('qualification', $doctor->qualification ?? '') }}"
            class="form-control">
        @error('qualification')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Department --}}
    <div class="col-md-4">
        <label class="form-label">Department <span class="text-danger">*</span></label>
        <select name="department_id" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach ($departments as $d)
                <option value="{{ $d->id }}" @selected(old('department_id', $doctor->department_id ?? '') == $d->id)>
                    {{ $d->name }}
                </option>
            @endforeach
        </select>
        @error('department_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Specialist --}}
    <div class="col-md-4">
        <label class="form-label">Specialist <span class="text-danger">*</span></label>
        <select name="specialist_id" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach ($specialists as $s)
                <option value="{{ $s->id }}" @selected(old('specialist_id', $doctor->specialist_id ?? '') == $s->id)>
                    {{ $s->name }}
                </option>
            @endforeach
        </select>
        @error('specialist_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Designation --}}
    <div class="col-md-4">
        <label class="form-label">Designation <span class="text-danger">*</span></label>
        <select name="designation_id" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach ($designations as $des)
                <option value="{{ $des->id }}" @selected(old('designation_id', $doctor->designation_id ?? '') == $des->id)>
                    {{ $des->name }}
                </option>
            @endforeach
        </select>
        @error('designation_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Registration No --}}
    <div class="col-md-4">
        <label class="form-label">Registration No</label>
        <input type="text" name="registration_no"
            value="{{ old('registration_no', $doctor->registration_no ?? '') }}" class="form-control">
        @error('registration_no')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- License No --}}
    <div class="col-md-4">
        <label class="form-label">License No</label>
        <input type="text" name="license_no" value="{{ old('license_no', $doctor->license_no ?? '') }}"
            class="form-control">
        @error('license_no')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- License Expiry --}}
    <div class="col-md-4">
        <label class="form-label">License Expiry Date</label>
        <input type="date" name="license_expiry_date"
            value="{{ old('license_expiry_date', optional($doctor->license_expiry_date ?? null)->format('Y-m-d')) }}"
            class="form-control">
        @error('license_expiry_date')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Doctor Type --}}
    <div class="col-md-4">
        <label class="form-label">Doctor Type</label>
        <select name="doctor_type" class="form-select">
            <option value="">-- Select --</option>
            @foreach ($doctorTypes as $t)
                <option value="{{ $t }}" @selected(old('doctor_type', $doctor->doctor_type ?? '') == $t)>
                    {{ $t }}
                </option>
            @endforeach
        </select>
        @error('doctor_type')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Joining Date --}}
    <div class="col-md-4">
        <label class="form-label">Joining Date</label>
        <input type="date" name="joining_date"
            value="{{ old('joining_date', optional($doctor->joining_date ?? null)->format('Y-m-d')) }}"
            class="form-control">
        @error('joining_date')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Leaving Date --}}
    <div class="col-md-4">
        <label class="form-label">Leaving Date</label>
        <input type="date" name="leaving_date"
            value="{{ old('leaving_date', optional($doctor->leaving_date ?? null)->format('Y-m-d')) }}"
            class="form-control">
        @error('leaving_date')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Gender --}}
    <div class="col-md-4">
        <label class="form-label">Gender <span class="text-danger">*</span></label>
        <select name="gender" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach ($genders as $g)
                <option value="{{ $g }}" @selected(old('gender', $doctor->gender ?? '') === $g)>{{ $g }}</option>
            @endforeach
        </select>
        @error('gender')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Marital --}}
    <div class="col-md-6">
        <label class="form-label">Marital Status <span class="text-danger">*</span></label>
        <select name="marital_status" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach ($maritals as $m)
                <option value="{{ $m }}" @selected(old('marital_status', $doctor->marital_status ?? '') === $m)>{{ $m }}</option>
            @endforeach
        </select>
        @error('marital_status')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Blood --}}
    <div class="col-md-6">
        <label class="form-label">Blood Group</label>
        <select name="blood_group" class="form-select">
            <option value="">-- Select --</option>
            @foreach ($bloods as $b)
                <option value="{{ $b }}" @selected(old('blood_group', $doctor->blood_group ?? '') === $b)>{{ $b }}</option>
            @endforeach
        </select>
        @error('blood_group')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Address --}}
    <div class="col-md-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address', $doctor->address ?? '') }}</textarea>
        @error('address')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Notes --}}
    <div class="col-md-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $doctor->notes ?? '') }}</textarea>
        @error('notes')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Work History --}}
    <div class="col-md-12">
        <label class="form-label">Work History</label>
        <textarea name="work_history" class="form-control" rows="4">{{ old('work_history', $doctor->work_history ?? '') }}</textarea>
        @error('work_history')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- Active --}}
    <div class="col-md-12 d-flex gap-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                @checked(old('is_active', $doctor->is_active ?? 1))>
            <label class="form-check-label" for="is_active">Is Active</label>
        </div>
    </div>

</div>
