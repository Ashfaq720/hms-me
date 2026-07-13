<form method="POST" action="{{ route('admin.pharmacy.controlled-drugs.store') }}">
    @csrf
    <div class="row g-3 px-1">

        {{-- Row 1: Entry Date / Doctor Name / DEA Number --}}
        <div class="col-md-4">
            <label class="form-label small fw-medium">Entry Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="entry_date" class="form-control form-control-sm"
                   value="{{ old('entry_date', now()->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-medium">Doctor Name <span class="text-danger">*</span></label>
            <input type="text" name="doctor_name" class="form-control form-control-sm"
                   placeholder="Dr. Full Name" value="{{ old('doctor_name') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-medium">Dr. DEA Number</label>
            <input type="text" name="dea_number" class="form-control form-control-sm"
                   placeholder="e.g. AS1234563" value="{{ old('dea_number') }}">
        </div>

        {{-- Row 2: Medicine / Generic Name --}}
        <div class="col-md-6">
            <label class="form-label small fw-medium">Medicine (optional)</label>
            <select name="medicine_id" class="form-select form-select-sm select2" id="cdMedicineSelect">
                <option value="">— Select Medicine —</option>
                @foreach($medicines as $m)
                    <option value="{{ $m->id }}"
                            data-generic="{{ $m->generic_name ?? '' }}"
                            {{ old('medicine_id') == $m->id ? 'selected' : '' }}>
                        {{ $m->medicine_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-medium">Generic Name <span class="text-danger">*</span></label>
            <input type="text" name="generic_name" id="cdGenericName" class="form-control form-control-sm"
                   placeholder="e.g. Morphine Sulfate" value="{{ old('generic_name') }}" required>
        </div>

        {{-- Row 3: Lot Number / Schedule --}}
        <div class="col-md-4">
            <label class="form-label small fw-medium">Lot Number <span class="text-danger">*</span></label>
            <input type="text" name="lot_number" class="form-control form-control-sm"
                   placeholder="e.g. LOT12345" value="{{ old('lot_number') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-medium">Schedule <span class="text-danger">*</span></label>
            <select name="schedule" class="form-select form-select-sm" required>
                <option value="">— Select —</option>
                @foreach(['Schedule II','Schedule III','Schedule IV','Schedule V'] as $sch)
                    <option value="{{ $sch }}" {{ old('schedule') === $sch ? 'selected' : '' }}>{{ $sch }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-medium">Expiration Date</label>
            <input type="date" name="expiration_date" class="form-control form-control-sm"
                   value="{{ old('expiration_date') }}">
        </div>

        {{-- Row 4: NDC Code / Action / Quantity / Unit --}}
        <div class="col-md-3">
            <label class="form-label small fw-medium">NDC Code</label>
            <input type="text" name="ndc_code" class="form-control form-control-sm"
                   placeholder="e.g. 0002-1234-56" value="{{ old('ndc_code') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-medium">Action <span class="text-danger">*</span></label>
            <select name="action_type" class="form-select form-select-sm" required>
                <option value="received" {{ old('action_type', 'received') === 'received' ? 'selected' : '' }}>Received</option>
                <option value="removed"  {{ old('action_type') === 'removed'  ? 'selected' : '' }}>Removed</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-medium">Quantity <span class="text-danger">*</span></label>
            <input type="number" name="quantity" class="form-control form-control-sm"
                   placeholder="0" step="0.01" min="0.01" value="{{ old('quantity') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-medium">Unit <span class="text-danger">*</span></label>
            <select name="unit" class="form-select form-select-sm" required>
                @foreach(['mg','ml','g','tablet','capsule','vial','patch','suppository'] as $u)
                    <option value="{{ $u }}" {{ old('unit', 'mg') === $u ? 'selected' : '' }}>{{ $u }}</option>
                @endforeach
            </select>
        </div>

        {{-- Row 5: Inventory Status / Notes --}}
        <div class="col-md-4">
            <label class="form-label small fw-medium">Inventory Status <span class="text-danger">*</span></label>
            <select name="inventory_status" class="form-select form-select-sm" required>
                <option value="available"    {{ old('inventory_status', 'available') === 'available'    ? 'selected' : '' }}>Available</option>
                <option value="low_stock"    {{ old('inventory_status') === 'low_stock'    ? 'selected' : '' }}>Low Stock</option>
                <option value="out_of_stock" {{ old('inventory_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label small fw-medium">Notes</label>
            <input type="text" name="notes" class="form-control form-control-sm"
                   placeholder="Optional notes..." value="{{ old('notes') }}">
        </div>

        {{-- Actions --}}
        <div class="col-12 d-flex justify-content-end gap-2 border-top pt-3 mt-1">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning fw-semibold px-4">
                <i class="bi bi-plus-lg me-1"></i> Add Entry
            </button>
        </div>
    </div>
</form>

<script>
(function () {
    // Auto-fill generic name from selected medicine
    const sel = document.getElementById('cdMedicineSelect');
    const gen = document.getElementById('cdGenericName');
    if (sel && gen) {
        sel.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const g   = opt ? opt.getAttribute('data-generic') : '';
            if (g) gen.value = g;
        });
    }
})();
</script>
