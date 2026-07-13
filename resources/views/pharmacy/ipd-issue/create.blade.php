<form action="{{ route('admin.pharmacy.ipd-issue.store') }}" method="POST" id="ipdIssueForm">
    @csrf

    {{-- Section: Patient & Requisition Info --}}
    <div class="text-center mb-4">
        <h6 class="fw-semibold text-primary mb-0">Patient & Requisition Info</h6>
    </div>

    <div class="row g-3">
        {{-- Ipd No --}}
        <div class="col-md-6">
            <label class="form-label small text-muted">Ipd No.</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-clipboard2 text-muted"></i></span>
                <input type="text" id="ipd_no_display" class="form-control bg-light" value="{{ $issueNo }}" readonly>
            </div>
        </div>

        {{-- Patient Name --}}
        <div class="col-md-6">
            <label class="form-label small text-muted">Patient Name <span class="text-danger">*</span></label>
            <select name="ipd_patient_id" id="ipd_patient_id" class="form-select select2" required>
                <option value="">Patient Name</option>
                @foreach($ipdPatients as $ipd)
                    <option value="{{ $ipd->id }}"
                            data-ipd-no="{{ $ipd->ipd_no }}"
                            data-patient-id="{{ $ipd->patient_id }}">
                        {{ $ipd->patient->patient_name ?? 'Unknown' }} ({{ $ipd->ipd_no }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Ward/Bed --}}
        <div class="col-md-3">
            <label class="form-label small text-muted">Ward/Bed <span class="text-danger">*</span></label>
            <select name="ward_bed" id="ward_bed" class="form-select select2" required>
                <option value="">Select Ward/Bed</option>
                @foreach($beds as $bed)
                    <option value="{{ $bed->name }}">{{ $bed->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Requisition No --}}
        <div class="col-md-3">
            <label class="form-label small text-muted">Requisition No. <span class="text-danger">*</span></label>
            <input type="text" name="requisition_no" class="form-control bg-light" value="{{ $requisitionNo }}" readonly>
        </div>

        {{-- Requested By --}}
        <div class="col-md-3">
            <label class="form-label small text-muted">Requested By <span class="text-danger">*</span></label>
            <select name="request_source" class="form-select select2" required>
                <option value="">Select Staff</option>
                @foreach($users as $user)
                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Request Date --}}
        <div class="col-md-3">
            <label class="form-label small text-muted">Request Date <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar3 text-muted"></i></span>
                <input type="date" name="request_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
        </div>

        {{-- Prescription No --}}
        <div class="col-md-3">
            <label class="form-label small text-muted">Prescription No</label>
            <select name="prescription_id" id="prescription_id" class="form-select select2">
                <option value="">Select Rx</option>
                @foreach($prescriptions as $rx)
                    <option value="{{ $rx->id }}" data-ipd="{{ $rx->ipd_patient_id }}">{{ $rx->prescription_no }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <hr class="my-4">

    {{-- Section: Add Medicines --}}
    <h6 class="fw-semibold mb-3">Add Medicines</h6>

    <div class="table-responsive">
        <table class="table align-middle mb-0" id="medicineTable">
            <thead class="table-light">
                <tr>
                    <th class="py-2 small fw-semibold" style="min-width:220px;"><i class="bi bi-capsule me-1 text-primary"></i> Drug Name <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold" style="min-width:120px;">Duration <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold" style="min-width:100px;">Qty Req. <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold" style="min-width:100px;">Available Qty</th>
                    <th class="py-2 small fw-semibold" style="min-width:160px;">Medicine Store <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold text-center" style="width:50px;"></th>
                </tr>
            </thead>
            <tbody id="medicineRows">
                <tr class="medicine-row" data-row="0">
                    <td class="py-2">
                        <select name="items[0][medicine_id]" class="form-select form-select-sm medicine-select select2" required>
                            <option value="">Select Drug</option>
                            @foreach($medicines as $med)
                                <option value="{{ $med->id }}">{{ $med->medicine_name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="py-2">
                        <input type="text" name="items[0][duration]" class="form-control form-control-sm" placeholder="e.g. IV BD" required>
                    </td>
                    <td class="py-2">
                        <input type="number" name="items[0][qty_required]" class="form-control form-control-sm" placeholder="0" min="1" required>
                    </td>
                    <td class="py-2">
                        <span class="available-qty-display fw-medium text-success">—</span>
                    </td>
                    <td class="py-2">
                        <select name="items[0][store]" class="form-select form-select-sm" required>
                            @foreach($stores as $store)
                                <option value="{{ $store }}">{{ $store }}</option>
                            @endforeach
                            @if($stores->isEmpty())
                                <option value="Main Pharmacy">Main Pharmacy</option>
                            @endif
                        </select>
                    </td>
                    <td class="py-2 text-center">
                        {{-- First row: no remove button --}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Add Medicine Button --}}
    <div class="border border-dashed rounded-3 text-center py-2 mt-2 mb-4" style="cursor:pointer;border-color:#ccc !important;" id="addMedicineBtn">
        <span class="text-muted"><i class="bi bi-plus-lg me-1"></i> Add Medicine</span>
    </div>

    {{-- Remarks --}}
    <div class="mb-3">
        <label class="form-label fw-semibold small">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i> Submit Ipd Issue
        </button>
    </div>
</form>

<script>
(function() {
    let rowIndex = 1;
    const medicines = @json($medicines->map(fn($m) => ['id' => $m->id, 'name' => $m->medicine_name]));
    const stores = @json($stores->isNotEmpty() ? $stores : collect(['Main Pharmacy']));
    const mqtyUrl = "{{ url('admin/pharmacy/ipd-issue/medicine-qty') }}";

    // ── Helper: init Select2 on a given select element ──
    function initSelect2(el, placeholder) {
        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            $(el).select2({ dropdownParent: $('#commonModal'), placeholder: placeholder || 'Select', width: '100%' });
        }
    }

    // ── Helper: build a new medicine row ──
    function buildRow(idx) {
        let medOpts = '<option value="">Select Drug</option>';
        medicines.forEach(function(m) { medOpts += '<option value="' + m.id + '">' + m.name + '</option>'; });

        let storeOpts = '';
        stores.forEach(function(s) { storeOpts += '<option value="' + s + '">' + s + '</option>'; });

        const tr = document.createElement('tr');
        tr.className = 'medicine-row';
        tr.setAttribute('data-row', idx);
        tr.innerHTML =
            '<td class="py-2">' +
                '<select name="items[' + idx + '][medicine_id]" class="form-select form-select-sm medicine-select" required>' + medOpts + '</select>' +
            '</td>' +
            '<td class="py-2">' +
                '<input type="text" name="items[' + idx + '][duration]" class="form-control form-control-sm" placeholder="e.g. IV BD" required>' +
            '</td>' +
            '<td class="py-2">' +
                '<input type="number" name="items[' + idx + '][qty_required]" class="form-control form-control-sm" placeholder="0" min="1" required>' +
            '</td>' +
            '<td class="py-2">' +
                '<span class="available-qty-display fw-medium text-muted">&mdash;</span>' +
            '</td>' +
            '<td class="py-2">' +
                '<select name="items[' + idx + '][store]" class="form-select form-select-sm" required>' + storeOpts + '</select>' +
            '</td>' +
            '<td class="py-2 text-center">' +
                '<button type="button" class="btn btn-sm btn-outline-danger rounded-circle remove-row" style="width:28px;height:28px;padding:0;" title="Remove">' +
                    '<i class="bi bi-x-lg" style="font-size:0.7rem;"></i>' +
                '</button>' +
            '</td>';

        return tr;
    }

    // ── Helper: fetch available qty for a medicine ──
    function fetchAvailableQty(selectEl) {
        const row = selectEl.closest('tr');
        const qtyDisplay = row.querySelector('.available-qty-display');
        const medicineId = selectEl.value;

        if (!medicineId) {
            qtyDisplay.textContent = '—';
            qtyDisplay.className = 'available-qty-display fw-medium text-muted';
            return;
        }

        qtyDisplay.textContent = '...';
        qtyDisplay.className = 'available-qty-display fw-medium text-muted';

        fetch(mqtyUrl + '/' + medicineId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var qty = parseInt(data.available_qty) || 0;
                qtyDisplay.textContent = qty;
                qtyDisplay.className = 'available-qty-display fw-medium ' + (qty > 0 ? 'text-success' : 'text-danger');
            })
            .catch(function() {
                qtyDisplay.textContent = '—';
                qtyDisplay.className = 'available-qty-display fw-medium text-muted';
            });
    }

    // ── Add Medicine row ──
    var addBtn = document.getElementById('addMedicineBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            var tbody = document.getElementById('medicineRows');
            var idx = rowIndex++;
            var tr = buildRow(idx);
            tbody.appendChild(tr);
            initSelect2(tr.querySelector('.medicine-select'), 'Select Drug');
            tr.querySelector('.medicine-select').focus();
        });
    }

    // ── Remove medicine row (delegated) ──
    var tbody = document.getElementById('medicineRows');
    if (tbody) {
        tbody.addEventListener('click', function(e) {
            var btn = e.target.closest('.remove-row');
            if (btn) {
                var tr = btn.closest('tr');
                // Destroy select2 before removing to prevent orphans
                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $(tr).find('.select2-hidden-accessible').select2('destroy');
                }
                tr.remove();
            }
        });

        // ── Native change handler for medicine-select (non-select2 fallback) ──
        tbody.addEventListener('change', function(e) {
            if (e.target.classList.contains('medicine-select')) {
                fetchAvailableQty(e.target);
            }
        });
    }

    // ── jQuery / Select2 event handlers ──
    if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
        // Medicine select → fetch qty
        $('#commonModalBody').off('select2:select', '.medicine-select').on('select2:select', '.medicine-select', function() {
            fetchAvailableQty(this);
        });

        // Patient select → update Ipd No display
        $('#commonModalBody').off('select2:select', '#ipd_patient_id').on('select2:select', '#ipd_patient_id', function() {
            var opt = $(this).find(':selected');
            var ipdNo = opt.data('ipd-no');
            if (ipdNo) {
                $('#ipd_no_display').val(ipdNo);
            }
        });
    }
})();
</script>
