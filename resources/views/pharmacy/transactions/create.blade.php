<form action="{{ route('admin.pharmacy.transactions.store') }}" method="POST" id="txnForm">
    @csrf

    {{-- Transaction Type Tabs --}}
    <div class="mb-4">
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-primary txn-type-btn active" data-type="opd">
                <i class="bi bi-prescription2 me-1"></i> OPD Dispense
            </button>
            <button type="button" class="btn btn-outline-info txn-type-btn" data-type="ipd">
                <i class="bi bi-hospital me-1"></i> Ipd Issue
            </button>
            <button type="button" class="btn btn-outline-success txn-type-btn" data-type="otc">
                <i class="bi bi-shop-window me-1"></i> Counter Sale (OTC)
            </button>
        </div>
        <input type="hidden" name="transaction_type" id="transaction_type" value="opd">
    </div>

    {{-- Transaction No (read-only display) --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label small text-muted">Transaction No.</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-receipt text-muted"></i></span>
                <input type="text" id="txn_no_display" class="form-control bg-light" value="{{ $nextOpdNo }}" readonly>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted">Handled By</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }}" readonly>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted">Date</label>
            <input type="date" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly>
        </div>
    </div>

    <hr class="my-3">

    {{-- OPD Section --}}
    <div id="section_opd" class="txn-section">
        <h6 class="fw-semibold text-primary mb-3"><i class="bi bi-prescription2 me-1"></i> OPD Details</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label small text-muted">OPD Patient <span class="text-danger">*</span></label>
                <select name="opd_patient_id" id="opd_patient_id" class="form-select select2">
                    <option value="">Select Patient</option>
                    @foreach($opdPatients as $op)
                        <option value="{{ $op->id }}" data-case="{{ $op->case_id }}" data-pid="{{ $op->patient_id }}">
                            {{ $op->patient->patient_name ?? 'Unknown' }} ({{ $op->case_id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">OPD Case No.</label>
                <input type="text" id="opd_case_display" class="form-control bg-light" placeholder="Auto-filled" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Prescription</label>
                <select name="prescription_id" class="form-select select2">
                    <option value="">Select Rx (optional)</option>
                    @foreach($prescriptions as $rx)
                        <option value="{{ $rx->id }}" data-opd="{{ $rx->opd_patient_id }}">{{ $rx->prescription_no }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Payment Status <span class="text-danger">*</span></label>
                <select name="payment_status" class="form-select opd-pay-status">
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Ipd Section --}}
    <div id="section_ipd" class="txn-section d-none">
        <h6 class="fw-semibold text-info mb-3"><i class="bi bi-hospital me-1"></i> Ipd Details</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label small text-muted">Ipd Patient <span class="text-danger">*</span></label>
                <select name="ipd_patient_id" id="ipd_patient_id" class="form-select select2">
                    <option value="">Select Admitted Patient</option>
                    @foreach($ipdPatients as $ip)
                        <option value="{{ $ip->id }}" data-pid="{{ $ip->patient_id }}">
                            {{ $ip->patient->patient_name ?? 'Unknown' }} ({{ $ip->ipd_no ?? $ip->id }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Medicine Order (populated via AJAX after patient selection) --}}
            <div class="col-md-6">
                <label class="form-label small text-muted d-flex align-items-center gap-2">
                    Medicine Order
                    <span id="ipdOrderBadge" class="badge bg-info rounded-pill" style="display:none;"></span>
                </label>
                <div class="d-flex gap-2">
                    <div style="flex:1 1 0;min-width:0;">
                        <select id="ipd_medicine_order" class="form-select select2" style="width:100%;">
                            <option value="">Select patient first</option>
                        </select>
                    </div>
                    <button type="button" id="loadAllOrdersBtn"
                            class="btn btn-info btn-sm text-nowrap"
                            style="display:none;">
                        <i class="bi bi-list-check me-1"></i><span id="loadAllLabel">Load All</span>
                    </button>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted">Ward / Bed <span class="text-danger">*</span></label>
                <input type="text" name="ward_bed" class="form-control" placeholder="e.g. Ward A - Bed 5">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Requisition No <span class="text-danger">*</span></label>
                <input type="text" name="requisition_no" class="form-control" value="{{ $nextIpdNo }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Request Source <span class="text-danger">*</span></label>
                <select name="request_source" class="form-select select2">
                    <option value="">Select Staff</option>
                    @foreach($users as $user)
                        <option value="{{ $user->name }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Request Date</label>
                <input type="date" name="request_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-12">
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle me-1"></i> Ipd issues go to the patient's running bill. Stock is deducted on approval.
                </div>
            </div>
        </div>
    </div>

    {{-- OTC Section --}}
    <div id="section_otc" class="txn-section d-none">
        <h6 class="fw-semibold text-success mb-3"><i class="bi bi-shop-window me-1"></i> Counter Sale Details</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" placeholder="Walk-in (optional)">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Customer Phone</label>
                <input type="text" name="customer_phone" class="form-control" placeholder="Optional">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Payment Method <span class="text-danger">*</span></label>
                <select name="payment_method" class="form-select">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="mobile_banking">Mobile Banking</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Payment Status <span class="text-danger">*</span></label>
                <select name="payment_status" class="form-select otc-pay-status">
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Discount (TK)</label>
                <input type="number" name="discount_amount" class="form-control" placeholder="0.00" min="0" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Paid Amount (TK)</label>
                <input type="number" name="paid_amount" class="form-control" placeholder="0.00" min="0" step="0.01">
            </div>
        </div>
    </div>

    <hr class="my-3">

    {{-- Medicine Items Table --}}
    <h6 class="fw-semibold mb-3">Add Medicines</h6>

    <div class="table-responsive">
        <table class="table align-middle mb-0" id="medicineTable">
            <thead class="table-light">
                <tr>
                    <th class="py-2 small fw-semibold" style="min-width:220px;">Drug Name <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold dosage-col" style="min-width:130px;">Dosage / Freq.</th>
                    <th class="py-2 small fw-semibold duration-col" style="min-width:110px;">Duration</th>
                    <th class="py-2 small fw-semibold" style="min-width:80px;">Qty <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold" style="min-width:90px;">Avail. Qty</th>
                    <th class="py-2 small fw-semibold" style="min-width:150px;">Store <span class="text-danger">*</span></th>
                    <th class="py-2 small fw-semibold" style="min-width:90px;">Unit Price</th>
                    <th class="py-2 small fw-semibold text-end" style="min-width:90px;">Sub Total</th>
                    <th style="width:40px;"></th>
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
                    <td class="py-2 dosage-col">
                        <input type="text" name="items[0][dosage]" class="form-control form-control-sm" placeholder="e.g. 1 tab BD">
                    </td>
                    <td class="py-2 duration-col">
                        <input type="text" name="items[0][duration]" class="form-control form-control-sm" placeholder="e.g. 5 days">
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
                    <td class="py-2 text-center"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <td colspan="7" class="py-2 text-end fw-semibold small">Total Amount:</td>
                    <td class="py-2 text-end fw-bold text-primary" id="grandTotal">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="border border-dashed rounded-3 text-center py-2 mt-2 mb-4"
         style="cursor:pointer;border-color:#ccc !important;" id="addMedicineBtn">
        <span class="text-muted"><i class="bi bi-plus-lg me-1"></i> Add Medicine</span>
    </div>

    {{-- Note --}}
    <div class="mb-3">
        <label class="form-label fw-semibold small">Note / Remarks</label>
        <textarea name="note" class="form-control" rows="2" placeholder="Optional..."></textarea>
    </div>

    <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id="submitTxnBtn">
            <i class="bi bi-check-circle me-1"></i> <span id="submitBtnText">Submit OPD Dispense</span>
        </button>
    </div>
</form>

<script>
(function () {
    const nextNos  = { opd: "{{ $nextOpdNo }}", ipd: "{{ $nextIpdNo }}", otc: "{{ $nextOtcNo }}" };
    const btnLabels = { opd: "Submit OPD Dispense", ipd: "Submit Ipd Issue", otc: "Submit Counter Sale" };
    const medicines = @json($medicines->map(fn($m) => ['id' => $m->id, 'name' => $m->medicine_name]));
    const stores    = @json($stores->isNotEmpty() ? $stores : collect(['Main Pharmacy']));
    const mqtyUrl   = "{{ url('admin/pharmacy/transactions/medicine-qty') }}";
    const allPrescriptions  = @json($prescriptions->map(fn($rx) => ['id' => $rx->id, 'no' => $rx->prescription_no, 'opd_patient_id' => $rx->opd_patient_id]));
    const allMedicineOrders = @json($medicineOrders);
    const rxMedUrl      = "{{ url('admin/pharmacy/transactions/prescription-medicines') }}";
    let   currentIpdOrders = [];

    let rowIndex = 1;

    function initSelect2(el, placeholder) {
        if (typeof $ === 'undefined' || !$.fn.select2) return;
        const $el = $(el);
        if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
        $el.select2({
            dropdownParent: $el.closest('.modal').length ? $el.closest('.modal') : $('#commonModal'),
            placeholder: placeholder || 'Select',
            width: '100%',
            theme: 'bootstrap-5',
            allowClear: true,
        });
    }

    // ── Filter prescriptions to the selected OPD patient ──
    function filterPrescriptions(opdPatientId) {
        const $rxSel = $('#section_opd [name="prescription_id"]');
        if (!$rxSel.length) return;
        if ($rxSel.hasClass('select2-hidden-accessible')) $rxSel.select2('destroy');
        $rxSel.empty().append('<option value="">Select Rx (optional)</option>');
        const list = opdPatientId
            ? allPrescriptions.filter(rx => String(rx.opd_patient_id) === String(opdPatientId))
            : allPrescriptions;
        list.forEach(rx => $rxSel.append(new Option(rx.no, rx.id)));
        initSelect2($rxSel[0], 'Select Rx (optional)');
    }

    // ── Filter medicine orders to the selected Ipd patient (mirrors filterPrescriptions) ──
    function populateIpdOrders(ipdPatientId) {
        const $sel  = $('#ipd_medicine_order');
        const badge = document.getElementById('ipdOrderBadge');
        const btn   = document.getElementById('loadAllOrdersBtn');

        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        $sel.empty();
        currentIpdOrders    = [];
        badge.style.display = 'none';
        btn.style.display   = 'none';

        if (!ipdPatientId) {
            $sel.append('<option value="">Select patient first</option>');
            initSelect2($sel[0], 'Select patient first');
            return;
        }

        const list = allMedicineOrders.filter(o => String(o.ipd_id) === String(ipdPatientId));

        if (!list.length) {
            $sel.append('<option value="">No pending orders for this patient</option>');
            initSelect2($sel[0], 'No pending orders');
            return;
        }

        currentIpdOrders = list;
        $sel.append('<option value="">— select an order —</option>');
        list.forEach(o => {
            const label = `${o.medicine_name} × ${o.qty}`
                + (o.prescribed_by ? ` (Dr. ${o.prescribed_by})` : '');
            $sel.append(new Option(label, o.id));
        });

        badge.textContent   = list.length;
        badge.style.display = '';
        document.getElementById('loadAllLabel').textContent = `Load All (${list.length})`;
        btn.style.display = '';

        initSelect2($sel[0], '— select an order —');
    }

    // ── Append a single Ipd order as a medicine row ──
    function appendIpdOrderRow(order) {
        // Remove the single empty placeholder row if nothing has been selected yet
        const rows = document.querySelectorAll('#medicineRows tr.medicine-row');
        if (rows.length === 1 && !rows[0].querySelector('.medicine-select')?.value) {
            rows[0].remove();
            rowIndex = 0;
        }
        const idx = rowIndex;
        const tr  = buildRow(rowIndex++);
        document.getElementById('medicineRows').appendChild(tr);

        // Carry the source order ID so the controller can mark it dispensed on submit
        const orderIdInput = document.createElement('input');
        orderIdInput.type  = 'hidden';
        orderIdInput.name  = `items[${idx}][medicine_order_id]`;
        orderIdInput.value = order.id;
        tr.querySelector('td').appendChild(orderIdInput);

        // Set qty FIRST so fetchMedicineData calculates subtotal correctly
        const qtyInput = tr.querySelector('.qty-input');
        if (qtyInput) qtyInput.value = order.qty || 1;

        const medSel = tr.querySelector('.medicine-select');
        if (typeof $ !== 'undefined' && $.fn.select2) {
            initSelect2(medSel, 'Select Drug');
            $(medSel).val(order.medicine_id).trigger('change.select2');
        } else {
            medSel.value = order.medicine_id;
        }
        fetchMedicineData(tr);
    }

    // ── Load ALL current Ipd orders into the medicine table ──
    function loadAllIpdOrders() {
        if (!currentIpdOrders.length) return;
        const tbody = document.getElementById('medicineRows');
        tbody.innerHTML = '';
        rowIndex = 0;
        currentIpdOrders.forEach(order => {
            const idx = rowIndex;
            const tr  = buildRow(rowIndex++);
            tbody.appendChild(tr);

            const orderIdInput = document.createElement('input');
            orderIdInput.type  = 'hidden';
            orderIdInput.name  = `items[${idx}][medicine_order_id]`;
            orderIdInput.value = order.id;
            tr.querySelector('td').appendChild(orderIdInput);

            const qtyInput = tr.querySelector('.qty-input');
            if (qtyInput) qtyInput.value = order.qty || 1;
            const medSel = tr.querySelector('.medicine-select');
            if (typeof $ !== 'undefined' && $.fn.select2) {
                initSelect2(medSel, 'Select Drug');
                $(medSel).val(order.medicine_id).trigger('change.select2');
            } else {
                medSel.value = order.medicine_id;
            }
            fetchMedicineData(tr);
        });
        recalcTotal();

        // Clear the dropdown — all remaining orders are now loaded
        const $sel = $('#ipd_medicine_order');
        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        $sel.empty().append('<option value="">— all orders loaded —</option>');
        initSelect2($sel[0], '— all orders loaded —');
        currentIpdOrders = [];
        document.getElementById('ipdOrderBadge').style.display = 'none';
        document.getElementById('loadAllOrdersBtn').style.display = 'none';
    }

    // ── Populate medicine rows from a prescription's medicines ──
    function fillMedicinesFromPrescription(meds) {
        if (!meds || !meds.length) return;
        const tbody = document.getElementById('medicineRows');
        tbody.innerHTML = '';
        rowIndex = 0;
        meds.forEach(med => {
            const tr = buildRow(rowIndex++);
            tbody.appendChild(tr);

            // Set qty first so fetchMedicineData calculates subtotal correctly
            const qtyInput = tr.querySelector('.qty-input');
            if (qtyInput) qtyInput.value = 1;

            // change.select2 syncs Select2's display without firing unrelated jQuery handlers
            const medSel = tr.querySelector('.medicine-select');
            if (typeof $ !== 'undefined' && $.fn.select2) {
                initSelect2(medSel, 'Select Drug');
                $(medSel).val(med.medicine_id).trigger('change.select2');
            } else {
                medSel.value = med.medicine_id;
            }

            const dosageInput = tr.querySelector('[name*="[dosage]"]');
            if (dosageInput) dosageInput.value = med.dosage || '';
            const durationInput = tr.querySelector('[name*="[duration]"]');
            if (durationInput) durationInput.value = med.duration || '';

            // Directly fetch available qty + unit price for this row
            fetchMedicineData(tr);
        });
        recalcTotal();
    }

    // ── Switch type ──
    document.querySelectorAll('.txn-type-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.txn-type-btn').forEach(b => {
                b.classList.remove('btn-primary', 'btn-info', 'btn-success');
                b.classList.add('btn-outline-primary', 'btn-outline-info', 'btn-outline-success');
                const type = b.dataset.type;
                if (type === 'opd') { b.classList.remove('btn-outline-primary'); b.classList.add('btn-outline-primary'); }
                if (type === 'ipd') { b.classList.remove('btn-outline-info');    b.classList.add('btn-outline-info');    }
                if (type === 'otc') { b.classList.remove('btn-outline-success'); b.classList.add('btn-outline-success'); }
            });

            const t = this.dataset.type;
            // Active state
            this.classList.remove('btn-outline-primary', 'btn-outline-info', 'btn-outline-success');
            if (t === 'opd') this.classList.add('btn-primary');
            if (t === 'ipd') this.classList.add('btn-info');
            if (t === 'otc') this.classList.add('btn-success');

            document.getElementById('transaction_type').value = t;
            document.getElementById('txn_no_display').value   = nextNos[t];
            document.getElementById('submitBtnText').textContent = btnLabels[t];

            document.querySelectorAll('.txn-section').forEach(s => s.classList.add('d-none'));
            document.getElementById('section_' + t).classList.remove('d-none');

            // Toggle dosage/duration columns
            // OPD: both visible | Ipd: duration only | OTC: dosage only
            const hideDosage   = t === 'ipd';
            const hideDuration = t === 'otc';
            document.querySelectorAll('.dosage-col').forEach(c => c.classList.toggle('d-none', hideDosage));
            document.querySelectorAll('.duration-col').forEach(c => c.classList.toggle('d-none', hideDuration));
        });
    });

    // ── Build medicine row ──
    function buildRow(idx) {
        let medOpts = '<option value="">Select Drug</option>';
        medicines.forEach(m => { medOpts += `<option value="${m.id}">${m.name}</option>`; });
        let storeOpts = '';
        stores.forEach(s => { storeOpts += `<option value="${s}">${s}</option>`; });

        const currentType  = document.getElementById('transaction_type').value;
        const hideDosage   = currentType === 'ipd';
        const hideDuration = currentType === 'otc';

        const tr = document.createElement('tr');
        tr.className = 'medicine-row';
        tr.dataset.row = idx;
        tr.innerHTML = `
            <td class="py-2">
                <select name="items[${idx}][medicine_id]" class="form-select form-select-sm medicine-select" required>
                    ${medOpts}
                </select>
            </td>
            <td class="py-2 dosage-col${hideDosage ? ' d-none' : ''}">
                <input type="text" name="items[${idx}][dosage]" class="form-control form-control-sm" placeholder="e.g. 1 tab BD">
            </td>
            <td class="py-2 duration-col${hideDuration ? ' d-none' : ''}">
                <input type="text" name="items[${idx}][duration]" class="form-control form-control-sm" placeholder="e.g. 5 days">
            </td>
            <td class="py-2">
                <input type="number" name="items[${idx}][qty_required]" class="form-control form-control-sm qty-input" placeholder="0" min="1" required>
            </td>
            <td class="py-2"><span class="available-qty-display fw-medium text-muted">&mdash;</span></td>
            <td class="py-2">
                <select name="items[${idx}][store]" class="form-select form-select-sm store-select" required>
                    ${storeOpts}
                </select>
            </td>
            <td class="py-2"><span class="unit-price-display fw-medium text-muted">0.00</span></td>
            <td class="py-2 text-end"><span class="subtotal-display fw-medium">0.00</span></td>
            <td class="py-2 text-center">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle remove-row"
                        style="width:28px;height:28px;padding:0;">
                    <i class="bi bi-x-lg" style="font-size:0.7rem;"></i>
                </button>
            </td>`;
        return tr;
    }

    function recalcTotal() {
        let total = 0;
        document.querySelectorAll('#medicineRows .subtotal-display').forEach(el => {
            total += parseFloat(el.dataset.value || 0);
        });
        document.getElementById('grandTotal').textContent = total.toFixed(2);
    }

    // Fetch price + available qty for the row, scoped to the currently selected store
    function fetchMedicineData(row) {
        const medSel  = row.querySelector('.medicine-select');
        const storeSel = row.querySelector('.store-select');
        const medId   = medSel ? medSel.value : '';
        const store   = storeSel ? storeSel.value : '';

        const qtyEl   = row.querySelector('.available-qty-display');
        const priceEl = row.querySelector('.unit-price-display');
        const subEl   = row.querySelector('.subtotal-display');
        const qtyInput = row.querySelector('.qty-input');

        if (!medId) {
            qtyEl.textContent      = '—';
            qtyEl.className        = 'available-qty-display fw-medium text-muted';
            priceEl.textContent    = '0.00';
            priceEl.dataset.price  = 0;
            subEl.textContent      = '0.00';
            subEl.dataset.value    = 0;
            recalcTotal();
            return;
        }

        qtyEl.textContent = '…';
        qtyEl.className   = 'available-qty-display fw-medium text-muted';

        const url = `${mqtyUrl}/${medId}` + (store ? `?store=${encodeURIComponent(store)}` : '');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const availQty = parseInt(data.available_qty) || 0;
                const price    = parseFloat(data.unit_price)  || 0;

                qtyEl.textContent = availQty;
                qtyEl.className   = 'available-qty-display fw-medium ' +
                    (availQty > 0 ? 'text-success' : 'text-danger');

                // Warn if no stock in this specific store
                if (store && !data.store_found) {
                    qtyEl.textContent = '0 (no stock in store)';
                    qtyEl.className   = 'available-qty-display fw-medium text-danger';
                }

                priceEl.textContent   = price.toFixed(2);
                priceEl.dataset.price = price;

                const qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;
                const sub = price * qty;
                subEl.textContent  = sub.toFixed(2);
                subEl.dataset.value = sub;
                recalcTotal();
            })
            .catch(() => {
                qtyEl.textContent = '—';
                qtyEl.className   = 'available-qty-display fw-medium text-muted';
            });
    }

    // Qty typed → recalc subtotal for that row
    document.getElementById('medicineRows').addEventListener('input', function (e) {
        if (!e.target.classList.contains('qty-input')) return;
        const row   = e.target.closest('tr');
        const price = parseFloat(row.querySelector('.unit-price-display').dataset.price || 0);
        const qty   = parseInt(e.target.value) || 0;
        const subEl = row.querySelector('.subtotal-display');
        subEl.textContent   = (price * qty).toFixed(2);
        subEl.dataset.value = price * qty;
        recalcTotal();
    });

    // Medicine changed (native change — covers non-Select2 fallback)
    document.getElementById('medicineRows').addEventListener('change', function (e) {
        if (e.target.classList.contains('medicine-select')) {
            fetchMedicineData(e.target.closest('tr'));
        }
        // Store changed → re-fetch price for the new store
        if (e.target.classList.contains('store-select')) {
            const row    = e.target.closest('tr');
            const medSel = row.querySelector('.medicine-select');
            if (medSel && medSel.value) fetchMedicineData(row);
        }
    });

    document.getElementById('medicineRows').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (btn) { btn.closest('tr').remove(); recalcTotal(); }
    });

    document.getElementById('addMedicineBtn').onclick = function () {
        const tr = buildRow(rowIndex++);
        document.getElementById('medicineRows').appendChild(tr);
        initSelect2(tr.querySelector('.medicine-select'), 'Select Drug');
    };

    // Select2 events (jQuery delegated — fires instead of / in addition to native change)
    if (typeof $ !== 'undefined' && $.fn.select2) {
        // Patient select → fill OPD case no + filter prescriptions
        $('#commonModalBody')
            .off('select2:select select2:clear', '#opd_patient_id')
            .on('select2:select select2:clear', '#opd_patient_id', function () {
                document.getElementById('opd_case_display').value =
                    $(this).find(':selected').data('case') || '';
                filterPrescriptions($(this).val());
            });

        // Prescription select → auto-fill medicine rows
        $('#commonModalBody')
            .off('select2:select select2:clear', '[name="prescription_id"]')
            .on('select2:select', '[name="prescription_id"]', function () {
                const rxId = $(this).val();
                if (!rxId) return;
                fetch(`${rxMedUrl}/${rxId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => { if (data.medicines && data.medicines.length) fillMedicinesFromPrescription(data.medicines); })
                    .catch(() => {});
            });

        // Ipd patient select → fetch medicine orders
        $('#commonModalBody')
            .off('select2:select select2:clear', '#ipd_patient_id')
            .on('select2:select select2:clear', '#ipd_patient_id', function () {
                populateIpdOrders($(this).val());
            });

        // Ipd medicine order select → append row (same pattern as OPD prescription)
        $('#commonModalBody')
            .off('select2:select', '#ipd_medicine_order')
            .on('select2:select', '#ipd_medicine_order', function () {
                const $sel = $(this);
                const id   = $sel.val();
                if (!id) return;
                const order = currentIpdOrders.find(o => String(o.id) === String(id));
                if (!order) return;

                appendIpdOrderRow(order);

                // Remove from dropdown + tracking array so the same order can't be added twice
                $sel.find(`option[value="${id}"]`).remove();
                currentIpdOrders = currentIpdOrders.filter(o => String(o.id) !== String(id));
                $sel.val('').trigger('change.select2');

                // Update badge / Load All button
                const rem   = currentIpdOrders.length;
                const badge = document.getElementById('ipdOrderBadge');
                if (rem > 0) {
                    badge.textContent = rem;
                    document.getElementById('loadAllLabel').textContent = `Load All (${rem})`;
                } else {
                    badge.style.display = 'none';
                    document.getElementById('loadAllOrdersBtn').style.display = 'none';
                }
            });

        // Medicine select → fetch price (Select2 fires its own event; native change may also fire
        // so we use a flag to avoid double-fetch in the same tick)
        $('#commonModalBody')
            .off('select2:select', '.medicine-select')
            .on('select2:select',  '.medicine-select', function () {
                const row = this.closest('tr');
                row._fetchPending = true;
                fetchMedicineData(row);
                // Let the native change handler know Select2 already handled this
                setTimeout(() => { row._fetchPending = false; }, 0);
            });
    }

    // Prevent double-fetch when Select2 already handled the change
    const _origChangeHandler = document.getElementById('medicineRows').onchange;
    document.getElementById('medicineRows').addEventListener('change', function (e) {
        if (e.target.classList.contains('medicine-select')) {
            const row = e.target.closest('tr');
            if (row._fetchPending) return; // Select2 already fired
        }
    }, true); // capture phase — runs before the delegated handler above

    document.getElementById('opd_patient_id')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('opd_case_display').value = opt ? (opt.dataset.case || '') : '';
        filterPrescriptions(this.value);
    });

    document.getElementById('ipd_patient_id')?.addEventListener('change', function () {
        populateIpdOrders(this.value);
    });

    document.getElementById('loadAllOrdersBtn')?.addEventListener('click', function () {
        loadAllIpdOrders();
    });
})();
</script>
