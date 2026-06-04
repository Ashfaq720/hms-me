<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="chargeForm">
    @csrf

    @if(isset($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Charge Type <span class="text-danger">*</span></label>
            <select name="charge_type_id" class="form-select" required>
                <option value="">Select</option>
                @foreach($chargeTypes as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('charge_type_id', $charge->charge_type_id ?? '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            @error('charge_type_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Charge Category <span class="text-danger">*</span></label>
            <select name="charge_category_id" class="form-select" required>
                <option value="">Select</option>
                @foreach($chargeCategories as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('charge_category_id', $charge->charge_category_id ?? '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            @error('charge_category_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Unit Type <span class="text-danger">*</span></label>
            <select name="unite_type_id" class="form-select" required>
                <option value="">Select</option>
                @foreach($unitTypes as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('unite_type_id', $charge->unite_type_id ?? '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            @error('unite_type_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Charge Name <span class="text-danger">*</span></label>
            <input type="text"
                   name="charge_name"
                   value="{{ old('charge_name', $charge->charge_name ?? '') }}"
                   class="form-control"
                   required>

            @error('charge_name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Tax Category <span class="text-danger">*</span></label>
            <select name="tax_category_id" class="form-select" required>
                <option value="">Select</option>
                @foreach($taxCategories as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('tax_category_id', $charge->tax_category_id ?? '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            @error('tax_category_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Tax</label>
            <div class="input-group">
                <input type="number"
                       step="0.01"
                       min="0"
                       name="tax"
                       value="{{ old('tax', $charge->tax ?? '') }}"
                       class="form-control">
                <span class="input-group-text">%</span>
            </div>

            @error('tax')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Standard Charge ($) <span class="text-danger">*</span></label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="standard_charge"
                   value="{{ old('standard_charge', $charge->standard_charge ?? '') }}"
                   class="form-control"
                   required>

            @error('standard_charge')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea name="description"
                      class="form-control"
                      rows="3">{{ old('description', $charge->description ?? '') }}</textarea>

            @error('description')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">
                {{ $buttonText ?? 'Save' }}
            </button>
        </div>
    </div>
</form>
