@php
    $serviceTypes = ['consultation', 'bed', 'icu_bed', 'nicu_bed', 'ot_room', 'nursing', 'procedure', 'lab_test', 'radiology', 'pharmacy', 'equipment', 'ambulance', 'package', 'administrative', 'other'];
    $chargeUnits = ['per_use', 'per_hour', 'per_day', 'per_session', 'per_unit', 'per_km', 'per_test', 'per_dose', 'per_package'];
    $patientTypes = ['all', 'self', 'corporate', 'insurance', 'staff'];
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Code <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $catalog->code) }}" required>
        @error('code') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-8">
        <label class="form-label">Service Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $catalog->name) }}" required>
        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Service type <span class="text-danger">*</span></label>
        <select name="service_type" class="form-select" required>
            @foreach ($serviceTypes as $t)
                <option value="{{ $t }}" @selected(old('service_type', $catalog->service_type) === $t)>{{ ucwords(str_replace('_', ' ', $t)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Charge unit <span class="text-danger">*</span></label>
        <select name="charge_unit" class="form-select" required>
            @foreach ($chargeUnits as $u)
                <option value="{{ $u }}" @selected(old('charge_unit', $catalog->charge_unit) === $u)>{{ ucwords(str_replace('_', ' ', $u)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Patient type</label>
        <select name="patient_type" class="form-select">
            @foreach ($patientTypes as $p)
                <option value="{{ $p }}" @selected(old('patient_type', $catalog->patient_type ?? 'all') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Base price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="base_price" class="form-control" value="{{ old('base_price', $catalog->base_price ?? 0) }}" required>
        @error('base_price') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Tax %</label>
        <input type="number" step="0.01" min="0" max="100" name="tax_percent" class="form-control" value="{{ old('tax_percent', $catalog->tax_percent ?? 0) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Department code</label>
        <input type="text" name="department_code" class="form-control" value="{{ old('department_code', $catalog->department_code) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Valid from</label>
        <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from', optional($catalog->valid_from)->toDateString()) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Valid to</label>
        <input type="date" name="valid_to" class="form-control" value="{{ old('valid_to', optional($catalog->valid_to)->toDateString()) }}">
    </div>

    <div class="col-md-6 d-flex align-items-end gap-3 flex-wrap">
        <div class="form-check">
            <input type="hidden" name="discount_allowed" value="0">
            <input type="checkbox" name="discount_allowed" value="1" id="discount_allowed" class="form-check-input"
                @checked(old('discount_allowed', $catalog->discount_allowed ?? true))>
            <label for="discount_allowed" class="form-check-label">Discount allowed</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="insurance_covered" value="0">
            <input type="checkbox" name="insurance_covered" value="1" id="insurance_covered" class="form-check-input"
                @checked(old('insurance_covered', $catalog->insurance_covered ?? true))>
            <label for="insurance_covered" class="form-check-label">Insurance covered</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="package_eligible" value="0">
            <input type="checkbox" name="package_eligible" value="1" id="package_eligible" class="form-check-input"
                @checked(old('package_eligible', $catalog->package_eligible ?? true))>
            <label for="package_eligible" class="form-check-label">Package eligible</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                @checked(old('is_active', $catalog->is_active ?? true))>
            <label for="is_active" class="form-check-label">Active</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control">{{ old('description', $catalog->description) }}</textarea>
    </div>
</div>
