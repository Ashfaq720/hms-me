<form action="{{ route('opd-patients.charges.store', $opdPatient->id) }}" method="POST" id="opdChargesForm">
    @csrf

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Doctor</label>
            <select name="doctor_id" class="form-select form-select-sm">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('doctor_id', $opdPatient->doctor_id) == $doctor->id)>
                        {{ $doctor->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select form-select-sm">
                <option value="">-- Select Department --</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(old('department_id', $opdPatient->department_id) == $dept->id)>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle" id="opdChargesTable">
            <thead class="table-light">
                <tr>
                    <th style="min-width:240px">Charge Item <span class="text-danger">*</span></th>
                    <th style="min-width:170px">Date <span class="text-danger">*</span></th>
                    <th style="width:75px">Qty <span class="text-danger">*</span></th>
                    <th style="width:100px">Unit Price</th>
                    <th style="width:80px">Tax (%)</th>
                    <th style="width:110px">Net Amount</th>
                    <th style="min-width:150px">Notes</th>
                    <th style="width:45px"></th>
                </tr>
            </thead>
            <tbody id="opdChargeRows">
                <tr class="charge-row">
                    <td>
                        <select name="items[0][charge_id]" class="form-select form-select-sm charge-select" required>
                            <option value="">-- Select --</option>
                            @foreach ($charges as $charge)
                                <option value="{{ $charge->id }}"
                                    data-price="{{ $charge->standard_charge }}"
                                    data-tax="{{ $charge->tax ?? 0 }}">
                                    {{ $charge->charge_name }}
                                    ({{ optional($charge->chargeCategory)->name }})
                                    - &#2547; {{ number_format($charge->standard_charge, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="datetime-local" name="items[0][date]" class="form-control form-control-sm"
                            value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </td>
                    <td>
                        <input type="number" name="items[0][quantity]" class="form-control form-control-sm qty-input"
                            value="1" min="1" required>
                    </td>
                    <td><input type="text" class="form-control form-control-sm bg-light unit-price" readonly value="0.00"></td>
                    <td><input type="text" class="form-control form-control-sm bg-light tax-rate" readonly value="0.00"></td>
                    <td><input type="text" class="form-control form-control-sm bg-light fw-semibold row-net" readonly value="0.00"></td>
                    <td><input type="text" name="items[0][notes]" class="form-control form-control-sm" placeholder="Optional"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row" disabled>
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                    <td><input type="text" id="opdGrandTotal" class="form-control form-control-sm bg-light fw-bold" readonly value="0.00"></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <button type="button" class="btn btn-sm btn-outline-primary" id="opdAddRowBtn">
            <i class="bi bi-plus-circle"></i> Add Row
        </button>
        <div class="d-flex gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">Save All Charges</button>
        </div>
    </div>
</form>

<script>
(function () {
    const tbody   = document.getElementById('opdChargeRows');
    const addBtn  = document.getElementById('opdAddRowBtn');
    const grandEl = document.getElementById('opdGrandTotal');
    let rowIndex  = 1;

    const chargeOptionsHtml = tbody.querySelector('.charge-select').innerHTML;
    const defaultDate = '{{ now()->format("Y-m-d\TH:i") }}';

    function calcRow(row) {
        const sel   = row.querySelector('.charge-select');
        const opt   = sel.options[sel.selectedIndex];
        const price = parseFloat(opt?.dataset?.price) || 0;
        const tax   = parseFloat(opt?.dataset?.tax) || 0;
        const qty   = parseInt(row.querySelector('.qty-input').value) || 1;
        const amount = price * qty;
        const taxAmt = Math.round(amount * tax / 100 * 100) / 100;
        const net    = amount + taxAmt;
        row.querySelector('.unit-price').value = price.toFixed(2);
        row.querySelector('.tax-rate').value   = tax.toFixed(2);
        row.querySelector('.row-net').value    = net.toFixed(2);
        calcGrandTotal();
    }

    function calcGrandTotal() {
        let total = 0;
        tbody.querySelectorAll('.row-net').forEach(el => { total += parseFloat(el.value) || 0; });
        grandEl.value = total.toFixed(2);
    }

    function toggleRemoveButtons() {
        const rows = tbody.querySelectorAll('.charge-row');
        rows.forEach(r => { r.querySelector('.remove-row').disabled = rows.length <= 1; });
    }

    addBtn.addEventListener('click', function () {
        const i = rowIndex++;
        const tr = document.createElement('tr');
        tr.classList.add('charge-row');
        tr.innerHTML = `
            <td><select name="items[${i}][charge_id]" class="form-select form-select-sm charge-select" required>${chargeOptionsHtml}</select></td>
            <td><input type="datetime-local" name="items[${i}][date]" class="form-control form-control-sm" value="${defaultDate}" required></td>
            <td><input type="number" name="items[${i}][quantity]" class="form-control form-control-sm qty-input" value="1" min="1" required></td>
            <td><input type="text" class="form-control form-control-sm bg-light unit-price" readonly value="0.00"></td>
            <td><input type="text" class="form-control form-control-sm bg-light tax-rate" readonly value="0.00"></td>
            <td><input type="text" class="form-control form-control-sm bg-light fw-semibold row-net" readonly value="0.00"></td>
            <td><input type="text" name="items[${i}][notes]" class="form-control form-control-sm" placeholder="Optional"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>`;
        tbody.appendChild(tr);
        toggleRemoveButtons();
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (!btn) return;
        btn.closest('.charge-row').remove();
        toggleRemoveButtons();
        calcGrandTotal();
    });
    tbody.addEventListener('change', function (e) {
        if (e.target.classList.contains('charge-select')) calcRow(e.target.closest('.charge-row'));
    });
    tbody.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty-input')) calcRow(e.target.closest('.charge-row'));
    });

    calcRow(tbody.querySelector('.charge-row'));
})();
</script>
