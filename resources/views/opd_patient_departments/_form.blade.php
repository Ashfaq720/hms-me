@php
    $paymentModes = ['Cash','Card','MFS','Bank','Insurance','Due'];
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Patient</label>
        <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
            <option value="">-- Select Patient --</option>
            @foreach($patients as $p)
                <option value="{{ $p->id }}" @selected(old('patient_id', $row->patient_id ?? '') == $p->id)>
                    {{ $p->patient_name }}
                </option>
            @endforeach
        </select>
        @error('patient_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Doctor</label>
        <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
            <option value="">-- Select Doctor --</option>
            @foreach($doctors as $d)
                <option value="{{ $d->id }}" @selected(old('doctor_id', $row->doctor_id ?? '') == $d->id)>
                    {{ $d->name }}
                </option>
            @endforeach
        </select>
        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">OPD Number</label>
        <input type="text" name="opd_number"
               value="{{ old('opd_number', $row->opd_number ?? '') }}"
               class="form-control @error('opd_number') is-invalid @enderror" required>
        @error('opd_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Appointment Date</label>
        <input type="datetime-local" name="appointment_date"
               value="{{ old('appointment_date', isset($row->appointment_date) ? $row->appointment_date->format('Y-m-d\TH:i') : '') }}"
               class="form-control @error('appointment_date') is-invalid @enderror" required>
        @error('appointment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Case</label>
        <input type="text" name="case"
               value="{{ old('case', $row->case ?? '') }}"
               class="form-control @error('case') is-invalid @enderror">
        @error('case') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Height (cm)</label>
        <input type="number" step="0.01" name="height"
               value="{{ old('height', $row->height ?? '') }}"
               class="form-control @error('height') is-invalid @enderror">
        @error('height') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Weight (kg)</label>
        <input type="number" step="0.01" name="weight"
               value="{{ old('weight', $row->weight ?? '') }}"
               class="form-control @error('weight') is-invalid @enderror">
        @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">BP</label>
        <input type="text" name="bp" placeholder="120/80"
               value="{{ old('bp', $row->bp ?? '') }}"
               class="form-control @error('bp') is-invalid @enderror">
        @error('bp') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Standard Charge</label>
        <input type="number" step="0.01" name="standard_charge"
               value="{{ old('standard_charge', $row->standard_charge ?? 0) }}"
               class="form-control @error('standard_charge') is-invalid @enderror" required>
        @error('standard_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Payment Mode</label>
        <select name="payment_mode" class="form-select @error('payment_mode') is-invalid @enderror" required>
            <option value="">-- Select Payment Mode --</option>
            @foreach($paymentModes as $mode)
                <option value="{{ $mode }}" @selected(old('payment_mode', $row->payment_mode ?? '') == $mode)>
                    {{ $mode }}
                </option>
            @endforeach
        </select>
        @error('payment_mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="is_old_patient" value="0">
            <input class="form-check-input" type="checkbox" name="is_old_patient" value="1" id="is_old_patient"
                @checked(old('is_old_patient', $row->is_old_patient ?? 0))>
            <label class="form-check-label" for="is_old_patient">Old Patient</label>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Symptoms</label>
        <textarea name="symptoms" rows="3"
                  class="form-control @error('symptoms') is-invalid @enderror">{{ old('symptoms', $row->symptoms ?? '') }}</textarea>
        @error('symptoms') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="3"
                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $row->notes ?? '') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
