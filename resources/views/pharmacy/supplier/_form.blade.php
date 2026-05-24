<form action="{{ $action }}" method="POST" id="supplierForm">
    @csrf
    @if(isset($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
            <input type="text" name="supplier_name" value="{{ old('supplier_name', $supplier->supplier_name ?? '') }}" class="form-control" required>
            @error('supplier_name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Contact Supplier</label>
            <input type="text" name="contact_supplier" value="{{ old('contact_supplier', $supplier->contact_supplier ?? '') }}" class="form-control">
            @error('contact_supplier')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Contact Person Name</label>
            <input type="text" name="contact_person_name" value="{{ old('contact_person_name', $supplier->contact_person_name ?? '') }}" class="form-control">
            @error('contact_person_name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Contact Person Telephone</label>
            <input type="text" name="contact_person_telephone" value="{{ old('contact_person_telephone', $supplier->contact_person_telephone ?? '') }}" class="form-control">
            @error('contact_person_telephone')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Drug License Number</label>
            <input type="text" name="drug_license_number" value="{{ old('drug_license_number', $supplier->drug_license_number ?? '') }}" class="form-control">
            @error('drug_license_number')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address ?? '') }}</textarea>
            @error('address')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="status" id="supplier_status" {{ old('status', $supplier->status ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="supplier_status">Active Status</label>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
        </div>
    </div>
</form>
