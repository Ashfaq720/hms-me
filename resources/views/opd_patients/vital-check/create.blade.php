<form action="{{ route('opd-patients.vital-checks.store', $id) }}" method="POST">
    @csrf
    <div class="row g-3">

        {{-- Weight --}}
        <div class="col-12 col-lg-6">
            <label class="form-label">Weight <span class="text-muted">(kg)</span></label>
            <input type="number" step="0.01" name="weight" class="form-control @error('weight') is-invalid @enderror"
                value="{{ old('weight') }}" placeholder="Enter Weight">
            @error('weight')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Height --}}
        <div class="col-12 col-lg-6">
            <label class="form-label">Height <span class="text-muted">(inch)</span></label>
            <input type="number" step="0.01" name="height" class="form-control @error('height') is-invalid @enderror"
                value="{{ old('height') }}" placeholder="Enter Height">
            @error('height')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Blood Pressure --}}
        <div class="col-12 col-lg-6">
            <label class="form-label">Blood Pressure <span class="text-muted">(mmHg)</span></label>
            <input type="text" name="blood_pressure" class="form-control @error('blood_pressure') is-invalid @enderror"
                value="{{ old('blood_pressure') }}" placeholder="120/80">
            @error('blood_pressure')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Temperature --}}
        <div class="col-12 col-lg-6">
            <label class="form-label">Temperature <span class="text-muted">(°F)</span></label>
            <input type="number" step="0.01" name="temperature"
                class="form-control @error('temperature') is-invalid @enderror"
                value="{{ old('temperature') }}" placeholder="Enter Temperature">
            @error('temperature')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Heart Rate --}}
        <div class="col-12 col-lg-6">
            <label class="form-label">Heart Rate <span class="text-muted">(bpm)</span></label>
            <input type="number" name="heart_rate" class="form-control @error('heart_rate') is-invalid @enderror"
                value="{{ old('heart_rate') }}" placeholder="Enter Heart Rate">
            @error('heart_rate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- SPO2 --}}
        <div class="col-12 col-lg-6">
            <label class="form-label">SPO2 <span class="text-muted">(%)</span></label>
            <input type="number" name="spo2" class="form-control @error('spo2') is-invalid @enderror"
                value="{{ old('spo2') }}" placeholder="Enter Spo2">
            @error('spo2')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Respiratory Rate --}}
        <div class="col-12 col-lg-12">
            <label class="form-label">Respiratory Rate <span class="text-muted">(breaths/min)</span></label>
            <input type="number" name="respiratory_rate"
                class="form-control @error('respiratory_rate') is-invalid @enderror"
                value="{{ old('respiratory_rate') }}" placeholder="Enter Respiratory Rate">
            @error('respiratory_rate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remarks --}}
        <div class="col-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3"
                placeholder="Notes...">{{ old('remarks') }}</textarea>
            @error('remarks')
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
