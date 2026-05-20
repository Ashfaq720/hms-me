<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="bed_type_name" name="name" value="{{ old('name', $bedType->name ?? '') }}"
            class="form-control" required>

        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_icu" value="0">
            <input class="form-check-input" type="checkbox" id="bed_type_is_icu" name="is_icu" value="1"
                {{ old('is_icu', $bedType->is_icu ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="bed_type_is_icu">
                Is ICU / Critical Care Bed Type
            </label>
            <div class="form-text">Beds with this type will appear as ICU beds (e.g. ICU, CCU, NICU, PICU).</div>
        </div>
        @error('is_icu')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- ICU sub-fields, only meaningful when is_icu is checked --}}
    <div id="bed_type_icu_fields"
        class="row g-3 mt-1"
        style="{{ old('is_icu', $bedType->is_icu ?? false) ? '' : 'display:none;' }}">

        <div class="col-md-6">
            <label class="form-label">ICU Type</label>
            <select name="icu_type" class="form-select">
                <option value="">-- Select --</option>
                @foreach (['ICU', 'CCU', 'NICU', 'PICU'] as $t)
                    <option value="{{ $t }}"
                        {{ old('icu_type', $bedType->icu_type ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Allowed Isolation Type</label>
            <select name="allowed_isolation_type" class="form-select">
                <option value="">-- None --</option>
                @foreach (['Airborne', 'Contact', 'Droplet', 'Standard'] as $t)
                    <option value="{{ $t }}"
                        {{ old('allowed_isolation_type', $bedType->allowed_isolation_type ?? '') === $t ? 'selected' : '' }}>
                        {{ $t }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <div class="form-check form-switch">
                <input type="hidden" name="has_ventilator_support" value="0">
                <input class="form-check-input" type="checkbox" id="bed_type_has_vent" name="has_ventilator_support"
                    value="1"
                    {{ old('has_ventilator_support', $bedType->has_ventilator_support ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="bed_type_has_vent">Ventilator support</label>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-check form-switch">
                <input type="hidden" name="has_monitor_support" value="0">
                <input class="form-check-input" type="checkbox" id="bed_type_has_mon" name="has_monitor_support"
                    value="1"
                    {{ old('has_monitor_support', $bedType->has_monitor_support ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="bed_type_has_mon">Monitor support</label>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-check form-switch">
                <input type="hidden" name="is_isolation_bed" value="0">
                <input class="form-check-input" type="checkbox" id="bed_type_is_iso" name="is_isolation_bed" value="1"
                    {{ old('is_isolation_bed', $bedType->is_isolation_bed ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="bed_type_is_iso">Isolation bed</label>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var icuToggle = document.getElementById('bed_type_is_icu');
        var icuFields = document.getElementById('bed_type_icu_fields');
        if (icuToggle && icuFields) {
            icuToggle.addEventListener('change', function () {
                icuFields.style.display = icuToggle.checked ? '' : 'none';
            });
        }
    })();
</script>
