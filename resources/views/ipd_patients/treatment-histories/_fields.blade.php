@php
    $h = $history ?? null;
@endphp
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Date & Time <span class="text-danger">*</span></label>
        <input type="datetime-local" name="date" class="form-control"
            value="{{ old('date', $h?->date?->format('Y-m-d\TH:i')) }}" required>
        @error('date')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Doctor</label>
        <select name="doctor_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach ($doctors as $doctor)
                <option value="{{ $doctor->id }}"
                    {{ old('doctor_id', $h?->doctor_id) == $doctor->id ? 'selected' : '' }}>
                    {{ $doctor->name }}
                </option>
            @endforeach
        </select>
        @error('doctor_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Diagnosis</label>
        <select name="diagnosis" class="form-select">
            <option value="">-- Select --</option>
            @foreach ($labInvestigationTypes as $type)
                <option value="{{ $type->name }}"
                    {{ old('diagnosis', $h?->diagnosis) == $type->name ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
        @error('diagnosis')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Prescribe Medicine</label>
        <input type="text" name="prescribe_medicine" class="form-control"
            value="{{ old('prescribe_medicine', $h?->prescribe_medicine) }}">
        @error('prescribe_medicine')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Tx Note</label>
        <textarea name="tx_note" rows="5" class="form-control">{{ old('tx_note', $h?->tx_note) }}</textarea>
        @error('tx_note')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
</div>
