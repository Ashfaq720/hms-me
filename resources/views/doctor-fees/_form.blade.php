<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Doctor<span class="text-danger">*</span></label>
        <select id="doctor_id" name="doctor_id" class="form-select" required>
            <option value="" disabled {{ old('doctor_id', $data->doctor_id ?? '') ? '' : 'selected' }}>Select
                Doctor
            </option>
            @foreach ($doctors as $f)
                <option value="{{ $f->id }}"
                    {{ old('doctor_id', $data->doctor_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }}</option>
            @endforeach
        </select>
        @error('doctor_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">First Visit Fee </label>
        <input type="number" id="first_visit_fee" name="first_visit_fee"
            value="{{ old('first_visit_fee', default: $data->first_visit_fee ?? '') }}" class="form-control">
        @error('first_visit_fee')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Follow Up Window </label>
        <input type="number" id="follow_up_window" name="follow_up_window"
            value="{{ old('follow_up_window', default: $data->follow_up_window ?? '') }}" class="form-control">
        @error('follow_up_window')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Follow Up Fee </label>
        <input type="number" id="follow_up_fee" name="follow_up_fee"
            value="{{ old('follow_up_fee', default: $data->follow_up_fee ?? '') }}" class="form-control">
        @error('follow_up_fee')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Ipd Visit Fee </label>
        <input type="number" id="ipd_visit_fee" name="ipd_visit_fee"
            value="{{ old('ipd_visit_fee', default: $data->ipd_visit_fee ?? '') }}" class="form-control">
        @error('ipd_visit_fee')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">OPD Visit Fee </label>
        <input type="number" id="opd_visit_fee" name="opd_visit_fee"
            value="{{ old('opd_visit_fee', default: $data->opd_visit_fee ?? '') }}" class="form-control">
        @error('opd_visit_fee')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
