<form action="{{ route('ipd-patients.surgery-requests.store', $ipdPatient->id) }}" method="POST">
    @csrf

    <div class="alert alert-info py-2 mb-3">
        <i class="bi bi-info-circle me-1"></i>
        This creates a draft surgery request linked to this IPD admission. The OT module will fill in surgeon, date,
        procedure and other details.
    </div>

    <div class="row g-3">

        <div class="col-md-6">
            <label class="form-label text-muted small">IPD No</label>
            <input type="text" class="form-control" value="{{ $ipdPatient->ipd_no }}" disabled>
            <input type="hidden" name="ipd_no" class="form-control" value="{{ $ipdPatient->ipd_no }}" disabled>
        </div>
        <input type="hidden" name="case_id" class="form-control" value="{{ $ipdPatient->case_id }}" disabled>

        <div class="col-md-6">
            <label class="form-label text-muted small">Patient</label>
            <input type="text" class="form-control"
                value="{{ $ipdPatient->patient->patient_name ?? '-' }} ({{ $ipdPatient->patient->mrn ?? '' }})"
                disabled>
            <input type="hidden" name="patient_id" class="form-control" value="{{ $ipdPatient->patient->id ?? '' }}"
                disabled>
        </div>

        <div class="col-md-4">
            <label for="requested_surgery_date" class="form-label">Requested Surgery Date</label>
            <input type="date" name="requested_surgery_date" id="requested_surgery_date"
                class="form-control @error('requested_surgery_date') is-invalid @enderror"
                value="{{ old('requested_surgery_date') }}">
            @error('requested_surgery_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="requested_surgery_time" class="form-label">Requested Surgery Time</label>
            <input type="time" name="requested_surgery_time" id="requested_surgery_time"
                class="form-control @error('requested_surgery_time') is-invalid @enderror"
                value="{{ old('requested_surgery_time') }}">
            @error('requested_surgery_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="priority" class="form-label">Priority</label>
            <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror">
                @foreach (['Normal', 'Emergency', 'STAT'] as $p)
                    <option value="{{ $p }}" @selected(old('priority', 'Normal') === $p)>{{ $p }}</option>
                @endforeach
            </select>
            @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="clinical_indication" class="form-label">Clinical Indication <small
                    class="text-muted">(optional)</small></label>
            <textarea name="clinical_indication" id="clinical_indication" rows="3"
                class="form-control @error('clinical_indication') is-invalid @enderror"
                placeholder="Brief reason for surgery (OT module can update later)">{{ old('clinical_indication') }}</textarea>
            @error('clinical_indication')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Request
            </button>
        </div>
    </div>
</form>
