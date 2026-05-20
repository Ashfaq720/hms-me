<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="chargeCategoryForm">
    @csrf

    @if (isset($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Charge Type <span class="text-danger">*</span></label>
            <select name="charge_type_id" class="form-control" required>
                <option value="">Select Charge Type</option>
                @foreach ($chargeTypes ?? [] as $type)
                    <option value="{{ $type->id }}"
                        {{ old('charge_type_id', $chargeCategory->charge_type_id ?? '') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>

            @error('charge_type_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-12">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $chargeCategory->name ?? '') }}"
                class="form-control" required>

            @error('name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Enter description here...">{{ old('description', $chargeCategory->description ?? '') }}</textarea>

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
