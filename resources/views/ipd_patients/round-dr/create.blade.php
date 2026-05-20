<form action="{{ route('ipd-patients.round-drs.store', $id) }}" method="POST">
    @csrf

    <div class="row g-3">

        {{-- Date & Time --}}
        <div class="col-md-6">
            <label for="datetime" class="form-label">Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="datetime" id="datetime"
                class="form-control @error('datetime') is-invalid @enderror"
                value="{{ old('datetime', now()->format('Y-m-d\TH:i')) }}" required>
            @error('datetime')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Shift --}}
        <div class="col-md-6">
            <label for="shift" class="form-label">Shift</label>
            <select name="shift" id="shift" class="form-select @error('shift') is-invalid @enderror">
                <option value="">-- Select Shift --</option>
                @foreach (['Morning', 'Evening', 'Night'] as $s)
                    <option value="{{ $s }}" @selected(old('shift') == $s)>{{ $s }}</option>
                @endforeach
            </select>
            @error('shift')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Doctor --}}
        <div class="col-md-6">
            <label for="doctor_id" class="form-label">Doctor</label>
            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>{{ $doctor->name }}</option>
                @endforeach
            </select>
            @error('doctor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Visit Count --}}
        <div class="col-md-6">
            <label for="visit_count" class="form-label">Visit Count</label>
            <input type="number" min="0" name="visit_count" id="visit_count"
                class="form-control @error('visit_count') is-invalid @enderror"
                value="{{ old('visit_count', 1) }}">
            @error('visit_count')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Clinical Observation --}}
        <div class="col-12">
            <label for="clinical_observation" class="form-label">Clinical Observation</label>
            <textarea name="clinical_observation" id="clinical_observation" rows="3"
                class="form-control @error('clinical_observation') is-invalid @enderror"
                placeholder="Enter clinical observation">{{ old('clinical_observation') }}</textarea>
            @error('clinical_observation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Notes --}}
        <div class="col-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" id="notes" rows="3"
                class="form-control @error('notes') is-invalid @enderror"
                placeholder="Enter notes">{{ old('notes') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>

    </div>
</form>
