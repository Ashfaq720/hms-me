<form action="{{ route('admin.pharmacy.opd-dispense.store') }}" method="POST" id="opdDispenseForm">
    @csrf

    {{-- Section Header --}}
    <div class="text-center mb-4">
        <h6 class="fw-semibold text-primary mb-0">Patient & Dispense Info</h6>
    </div>

    <div class="row g-3">

        {{-- Dispense No --}}
        <div class="col-md-6">
            <label class="form-label small text-muted">Dispense No.</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-receipt text-muted"></i></span>
                <input type="text" class="form-control bg-light" value="{{ $dispenseNo }}" readonly>
            </div>
        </div>

        {{-- Patient Name --}}
        <div class="col-md-6">
            <label class="form-label small text-muted">Patient Name <span class="text-danger">*</span></label>
            <select name="opd_patient_id" id="opd_patient_id" class="form-select select2" required>
                <option value="">Select Patient</option>
                @foreach($opdPatients as $op)
                    <option value="{{ $op->id }}"
                            data-case-id="{{ $op->case_id }}"
                            data-patient-id="{{ $op->patient_id }}">
                        {{ $op->patient->patient_name ?? 'Unknown' }} ({{ $op->case_id }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- OPD Case No (auto-filled) --}}
        <div class="col-md-3">
            <label class="form-label small text-muted">OPD Case No.</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-clipboard2 text-muted"></i></span>
                <input type="text" id="case_id_display" class="form-control bg-light" placeholder="Auto-filled" readonly>
            </div>
        </div>

        {{-- Prescription No --}}
        <div class="col-md-3">
            <label class="form-label small text-muted">Prescription No</label>
            <select name="prescription_id" id="prescription_id" class="form-select select2">
                <option value="">Select Rx</option>
                @foreach($prescriptions as $rx)
                    <option value="{{ $rx->id }}" data-opd="{{ $rx->opd_patient_id }}">{{ $rx->prescription_no }}</option>
                @endforeach
            </select>
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

        {{-- Dispense Date --}}
        <div class="col-md-3">
            <label class="form-label small text-muted">Dispense Date <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar3 text-muted"></i></span>
                <input type="date" name="dispense_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
        </div>

        {{-- Payment Status --}}
        <div class="col-md-3">
            <label class="form-label small text-muted">Payment Status <span class="text-danger">*</span></label>
            <select name="payment_status" class="form-select" required>
                <option value="unpaid">Unpaid</option>
                <option value="paid">Paid</option>
                <option value="partial">Partial</option>
            </select>
        </div>

    </div>

    <hr class="my-4">

    {{-- Medicine Rows --}}
    <h6 class="fw-semibold mb-3">Add Medicines</h6>

    <div class="table-responsive">
        <table class="table align-middle mb-0" id="medicineTable">
            <thead class="table-light">
                <tr>
                    <th class="py-2 small fw-semibold" style="min-width:220px;"><i class="bi bi-capsule me-1 text-primary"></i> Drug Name <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold" style="min-width:120px;">Dosage <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold" style="min-width:90px;">Qty <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold" style="min-width:100px;">Available Qty</th>
                    <th class="py-2 small fw-semibold" style="min-width:160px;">Store <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold" style="min-width:100px;">Unit Price</th>
                    <th class="py-2 small fw-semibold text-end" style="min-width:100px;">Sub Total</th>
                    <th class="py-2 small" style="width:40px;"></th>
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
                        <input type="text" name="items[0][dosage]" class="form-control form-control-sm" placeholder="e.g. 1 tab BD" required>
                    </td>
                    <td class="py-2">
                        <input type="number" name="items[0][qty_required]" class="form-control form-control-sm qty-input" placeholder="0" min="1" required>
                    </td>
                    <td class="py-2">
                        <span class="available-qty-display fw-medium text-success">—</span>
                    </td>
                    <td class="py-2">
                        <select name="items[0][store]" class="form-select form-select-sm store-select" required>
                            @foreach($stores as $store)
                                <option value="{{ $store }}">{{ $store }}</option>
                            @endforeach
                            @if($stores->isEmpty())
                                <option value="Main Pharmacy">Main Pharmacy</option>
                            @endif
                        </select>
                    </td>
                    <td class="py-2">
                        <span class="unit-price-display fw-medium text-muted">0.00</span>
                    </td>
                    <td class="py-2 text-end">
                        <span class="subtotal-display fw-medium">0.00</span>
                    </td>
                    <td class="py-2 text-center">
                        {{-- First row no remove --}}
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <td colspan="6" class="py-2 text-end fw-semibold small">Total Amount:</td>
                    <td class="py-2 text-end fw-bold text-primary" id="grandTotal">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Add Medicine Button --}}
    <div class="border border-dashed rounded-3 text-center py-2 mt-2 mb-4"
         style="cursor:pointer;border-color:#ccc !important;" id="addMedicineBtn">
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
            <i class="bi bi-check-circle me-1"></i> Submit OPD Dispense
        </button>
    </div>
</form>

<script>
(function () {
    let rowIndex = 1;
    const medicines = @json($medicines->map(fn($m) => ['id' => $m->id, 'name' => $m->medicine_name]));
    const stores    = @json($stores->isNotEmpty() ? $stores : collect(['Main Pharmacy']));
    const mqtyUrl   = "{{ url('admin/pharmacy/opd-dispense/medicine-qty') }}";

    // ── Select2 init helper ──
    function initSelect2(el, placeholder) {
        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            $(el).select2({ dropdownParent: $('#commonModal'), placeholder: placeholder || 'Select', width: '100%' });
        }
    }

    // ── Build a new medicine row ──
    function buildRow(idx) {
        let medOpts = '<option value="">Select Drug</option>';
        medicines.forEach(m => { medOpts += `<option value="${m.id}">${m.name}</option>`; });

        let storeOpts = '';
        stores.forEach(s => { storeOpts += `<option value="${s}">${s}</option>`; });

        const tr = document.createElement('tr');
        tr.className = 'medicine-row';
        tr.dataset.row = idx;
        tr.innerHTML = `
            <td class="py-2">
                <select name="items[${idx}][medicine_id]" class="form-select form-select-sm medicine-select" required>
                    ${medOpts}
                </select>
            </td>
            <td class="py-2">
                <input type="text" name="items[${idx}][dosage]" class="form-control form-control-sm" placeholder="e.g. 1 tab BD" required>
            </td>
            <td class="py-2">
                <input type="number" name="items[${idx}][qty_required]" class="form-control form-control-sm qty-input" placeholder="0" min="1" required>
            </td>
            <td class="py-2">
                <span class="available-qty-display fw-medium text-muted">&mdash;</span>
            </td>
            <td class="py-2">
                <select name="items[${idx}][store]" class="form-select form-select-sm store-select" required>
                    ${storeOpts}
                </select>
            </td>
            <td class="py-2">
                <span class="unit-price-display fw-medium text-muted">0.00</span>
            </td>
            <td class="py-2 text-end">
                <span class="subtotal-display fw-medium">0.00</span>
            </td>
            <td class="py-2 text-center">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle remove-row"
                        style="width:28px;height:28px;padding:0;" title="Remove">
                    <i class="bi bi-x-lg" style="font-size:0.7rem;"></i>
                </button>
            </td>`;
        return tr;
    }

    // ── Recalculate grand total ──
    function recalcTotal() {
        let total = 0;
        document.querySelectorAll('#medicineRows .subtotal-display').forEach(el => {
            total += parseFloat(el.dataset.value || 0);
        });
        document.getElementById('grandTotal').textContent = total.toFixed(2);
    }

    // ── Fetch available qty & unit price for a row ──
    function fetchMedicineData(selectEl) {
        const row          = selectEl.closest('tr');
        const qtyDisplay   = row.querySelector('.available-qty-display');
        const priceDisplay = row.querySelector('.unit-price-display');
        const subtotal     = row.querySelector('.subtotal-display');
        const qtyInput     = row.querySelector('.qty-input');
        const medicineId   = selectEl.value;

        if (!medicineId) {
            qtyDisplay.textContent = '—';
            qtyDisplay.className   = 'available-qty-display fw-medium text-muted';
            priceDisplay.textContent = '0.00';
            subtotal.textContent   = '0.00';
            subtotal.dataset.value = 0;
            recalcTotal();
            return;
        }

        qtyDisplay.textContent = '…';

        fetch(`${mqtyUrl}/${medicineId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const qty   = parseInt(data.available_qty) || 0;
                const price = parseFloat(data.unit_price)  || 0;

                qtyDisplay.textContent = qty;
                qtyDisplay.className   = 'available-qty-display fw-medium ' + (qty > 0 ? 'text-success' : 'text-danger');

                priceDisplay.textContent = price.toFixed(2);
                priceDisplay.dataset.price = price;

                const reqQty = parseInt(qtyInput.value) || 0;
                const sub    = price * reqQty;
                subtotal.textContent   = sub.toFixed(2);
                subtotal.dataset.value = sub;
                recalcTotal();
            })
            .catch(() => {
                qtyDisplay.textContent = '—';
                qtyDisplay.className   = 'available-qty-display fw-medium text-muted';
            });
    }

    // ── Qty input → update subtotal ──
    document.getElementById('medicineRows').addEventListener('input', function (e) {
        if (e.target.classList.contains('qty-input')) {
            const row      = e.target.closest('tr');
            const price    = parseFloat(row.querySelector('.unit-price-display').dataset.price || 0);
            const qty      = parseInt(e.target.value) || 0;
            const subtotal = row.querySelector('.subtotal-display');
            subtotal.textContent   = (price * qty).toFixed(2);
            subtotal.dataset.value = price * qty;
            recalcTotal();
        }
    });

    // ── Delegated: medicine select change ──
    document.getElementById('medicineRows').addEventListener('change', function (e) {
        if (e.target.classList.contains('medicine-select')) {
            fetchMedicineData(e.target);
        }
    });

    // ── Delegated: remove row ──
    document.getElementById('medicineRows').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (btn) {
            const tr = btn.closest('tr');
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $(tr).find('.select2-hidden-accessible').select2('destroy');
            }
            tr.remove();
            recalcTotal();
        }
    });

    // ── Add Medicine ──
    document.getElementById('addMedicineBtn').addEventListener('click', function () {
        const tbody = document.getElementById('medicineRows');
        const idx   = rowIndex++;
        const tr    = buildRow(idx);
        tbody.appendChild(tr);
        initSelect2(tr.querySelector('.medicine-select'), 'Select Drug');
        tr.querySelector('.medicine-select').focus();
    });

    // ── Patient select → update Case No & filter prescriptions ──
    if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
        $('#commonModalBody').off('select2:select', '#opd_patient_id').on('select2:select', '#opd_patient_id', function () {
            const opt    = $(this).find(':selected');
            const caseId = opt.data('case-id');
            document.getElementById('case_id_display').value = caseId || '';
        });

        // Medicine select2 → fetch data
        $('#commonModalBody').off('select2:select', '.medicine-select').on('select2:select', '.medicine-select', function () {
            fetchMedicineData(this);
        });
    }

    // Native fallback for patient select
    document.getElementById('opd_patient_id').addEventListener('change', function () {
        const opt    = this.options[this.selectedIndex];
        const caseId = opt ? opt.dataset.caseId : '';
        document.getElementById('case_id_display').value = caseId || '';
    });
})();
</script>
