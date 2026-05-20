<form action="{{ route('opd-patients.move-to-ipd', $opdPatient->id) }}" method="POST">
    @csrf

    <div class="alert alert-info py-2 mb-3 small">
        <i class="bi bi-info-circle me-1"></i>
        Moving <strong>{{ $opdPatient->patient->patient_name ?? '-' }}</strong>
        (Case: <strong>{{ $opdPatient->case_id ?? '-' }}</strong>) from OPD to Ipd.
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Admission Date <span class="text-danger">*</span></label>
            <input type="datetime-local" name="admission_date" class="form-control form-control-sm"
                value="{{ old('admission_date', now()->format('Y-m-d\TH:i')) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Possible Discharge Date</label>
            <input type="datetime-local" name="possible_discharge_date" class="form-control form-control-sm"
                value="{{ old('possible_discharge_date') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Admission Type</label>
            <select name="admission_type" class="form-select form-select-sm">
                <option value="">-- Select --</option>
                @foreach (['general' => 'General', 'emergency' => 'Emergency', 'maternity' => 'Maternity', 'icu' => 'ICU', 'other' => 'Other'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('admission_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Consultant Doctor</label>
            <select name="doctor_id" class="form-select form-select-sm">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('doctor_id', $opdPatient->doctor_id) == $doctor->id)>
                        {{ $doctor->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-12">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select form-select-sm">
                <option value="">-- Select Department --</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(old('department_id', $opdPatient->department_id) == $dept->id)>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-12">
            <label class="form-label">Patient History</label>
            <textarea name="patient_history" rows="2" class="form-control form-control-sm" placeholder="Brief medical history">{{ old('patient_history') }}</textarea>
        </div>

        <div class="col-md-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" rows="2" class="form-control form-control-sm" placeholder="Optional remarks">{{ old('remarks', $opdPatient->remarks) }}</textarea>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-left-right me-1"></i> Move to Ipd
            </button>
        </div>
    </div>
</form>
