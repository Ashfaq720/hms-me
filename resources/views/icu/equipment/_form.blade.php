<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Equipment Code <span class="text-danger">*</span></label>
        <input type="text" name="equipment_code"
            value="{{ old('equipment_code', $item->equipment_code ?? '') }}" class="form-control" required>
        @error('equipment_code')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="equipment_name"
            value="{{ old('equipment_name', $item->equipment_name ?? '') }}" class="form-control" required>
        @error('equipment_name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="equipment_type" class="form-select" required>
            @foreach (['Ventilator', 'Monitor', 'InfusionPump', 'SyringePump', 'OxygenSupport', 'DialysisMachine', 'ECG', 'PulseOximeter', 'TemperatureSensor', 'Other'] as $t)
                <option value="{{ $t }}"
                    {{ old('equipment_type', $item->equipment_type ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        @error('equipment_type')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    @php $lockUnit = ! empty($icuType ?? null); @endphp
    <div class="col-md-2">
        <label class="form-label">Unit</label>
        <select name="icu_type" class="form-select" {{ $lockUnit ? 'disabled' : '' }}>
            <option value="">--</option>
            @foreach (['ICU', 'CCU', 'NICU', 'PICU'] as $u)
                <option value="{{ $u }}"
                    {{ old('icu_type', $item->icu_type ?? $icuType ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
            @endforeach
        </select>
        @if ($lockUnit)
            <input type="hidden" name="icu_type" value="{{ $icuType }}">
        @endif
    </div>

    <div class="col-md-4">
        <label class="form-label">Serial No</label>
        <input type="text" name="serial_no" value="{{ old('serial_no', $item->serial_no ?? '') }}"
            class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
            @foreach (['Available', 'InUse', 'Maintenance', 'Cleaning', 'Damaged', 'Reserved'] as $s)
                <option value="{{ $s }}"
                    {{ old('status', $item->status ?? 'Available') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Location</label>
        <input type="text" name="location" value="{{ old('location', $item->location ?? '') }}" class="form-control"
            placeholder="e.g. ICU Floor-2">
    </div>

    <div class="col-md-4">
        <label class="form-label">Default {{ ($icuType ?? null) ?: 'ICU' }} Bed (optional)</label>
        <select name="default_bed_id" class="form-select">
            <option value="">--</option>
            @foreach ($beds as $b)
                <option value="{{ $b->id }}"
                    {{ old('default_bed_id', $item->default_bed_id ?? '') == $b->id ? 'selected' : '' }}>
                    {{ $b->name }} [{{ optional($b->bedType)->name }}]
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Charge Type <span class="text-danger">*</span></label>
        <select name="charge_type" class="form-select" required>
            @foreach (['Hour', 'Day', 'Session', 'Fixed'] as $c)
                <option value="{{ $c }}"
                    {{ old('charge_type', $item->charge_type ?? 'Day') === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Charge Rate (৳) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="charge_rate"
            value="{{ old('charge_rate', $item->charge_rate ?? 0) }}" class="form-control" required>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="eq_active" name="is_active" value="1"
                {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="eq_active">Active</label>
        </div>
    </div>

    <div class="col-md-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $item->remarks ?? '') }}</textarea>
    </div>
</div>
