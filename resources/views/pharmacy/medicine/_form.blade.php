<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="medicineForm">
    @csrf

    @if(isset($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Medicine Name <span class="text-danger">*</span></label>
            <input type="text" name="medicine_name" value="{{ old('medicine_name', $medicine->medicine_name ?? '') }}" class="form-control" required>
            @error('medicine_name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Medicine Category <span class="text-danger">*</span></label>
            <select name="medicine_category_id" class="form-select" required>
                <option value="">Select</option>
                @foreach($medicine_categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('medicine_category_id', $medicine->medicine_category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('medicine_category_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Medicine Company</label>
            <select name="company_id" class="form-select">
                <option value="">Select</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}"
                        {{ old('company_id', $medicine->company_id ?? '') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            @error('company_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Medicine Composition</label>
            <input type="text" name="medicine_composition" value="{{ old('medicine_composition', $medicine->medicine_composition ?? '') }}" class="form-control">
            @error('medicine_composition')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Medicine Group</label>
            <select name="medical_group_id" class="form-select">
                <option value="">Select</option>
                @foreach($medical_groups as $group)
                    <option value="{{ $group->id }}"
                        {{ old('medical_group_id', $medicine->medical_group_id ?? '') == $group->id ? 'selected' : '' }}>
                        {{ $group->name }}
                    </option>
                @endforeach
            </select>
            @error('medical_group_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Unit <span class="text-danger">*</span></label>
            <select name="medicine_unit_id" class="form-select" required>
                <option value="">Select</option>
                @foreach($medicine_units as $unit)
                    <option value="{{ $unit->id }}"
                        {{ old('medicine_unit_id', $medicine->medicine_unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }}
                    </option>
                @endforeach
            </select>
            @error('medicine_unit_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Min Level</label>
            <input type="text" name="min_level" value="{{ old('min_level', $medicine->min_level ?? '') }}" class="form-control">
            @error('min_level')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Re-Order Level</label>
            <input type="text" name="reorder_level" value="{{ old('reorder_level', $medicine->reorder_level ?? '') }}" class="form-control">
            @error('reorder_level')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Tax</label>
            <div class="input-group">
                <input type="number" step="0.01" name="tax" value="{{ old('tax', $medicine->tax ?? '') }}" class="form-control">
                <span class="input-group-text">%</span>
            </div>
            @error('tax')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Box/Packing <span class="text-danger">*</span></label>
            <input type="text" name="box_packing" value="{{ old('box_packing', $medicine->box_packing ?? '') }}" class="form-control" required>
            @error('box_packing')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">VAT A/C</label>
            <input type="text" name="vat_ac" value="{{ old('vat_ac', $medicine->vat_ac ?? '') }}" class="form-control">
            @error('vat_ac')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Rack Number</label>
            <input type="text" name="rack_number" value="{{ old('rack_number', $medicine->rack_number ?? '') }}" class="form-control">
            @error('rack_number')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Available Qty</label>
            <input type="number" name="available_qty" value="{{ old('available_qty', $medicine->available_qty ?? 0) }}" class="form-control" min="0">
            @error('available_qty')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Note</label>
            <textarea name="note" class="form-control" rows="2">{{ old('note', $medicine->note ?? '') }}</textarea>
            @error('note')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Medicine Photo ( JPG | JPEG | PNG )</label>
            <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
            @error('photo')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror

            @if(!empty($medicine?->photo))
                <div class="mt-2">
                    <img src="{{ asset('uploads/pharmacy/medicines/' . $medicine->photo) }}"
                         alt="Medicine Photo"
                         style="width: 70px; height: 70px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                </div>
            @endif
        </div>

        <div class="col-md-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="status" id="medicine_status"
                       {{ old('status', $medicine->status ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="medicine_status">Active Status</label>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 border-top pt-3 mt-3">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> {{ $buttonText ?? 'Save' }}
            </button>
        </div>
    </div>
</form>
