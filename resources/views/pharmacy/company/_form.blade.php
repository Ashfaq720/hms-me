<form action="{{ $action }}" method="POST" id="companyForm">
    @csrf

    @if(isset($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Company Name <span class="text-danger">*</span></label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $company->name ?? '') }}"
                   class="form-control"
                   placeholder="Enter company name"
                   required>

            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="status" id="status"
                       {{ old('status', $company->status ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="status">Active Status</label>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">
                {{ $buttonText ?? 'Save' }}
            </button>
        </div>
    </div>
</form>
