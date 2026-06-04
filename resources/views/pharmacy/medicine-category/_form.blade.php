<form action="{{ $action }}" method="POST" id="medicineCategoryForm">
    @csrf
    @if(isset($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $medicineCategory->name ?? '') }}" class="form-control" required>
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="status" id="category_status" {{ old('status', $medicineCategory->status ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="category_status">Active Status</label>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
        </div>
    </div>
</form>
