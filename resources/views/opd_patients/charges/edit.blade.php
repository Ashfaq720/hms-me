<form action="{{ route('opd-patients.charges.update', [$opdPatient->id, $patientCharge->id]) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Charge Item <span class="text-danger">*</span></label>
            <select name="charge_id" id="opdEditChargeSelect" class="form-select form-select-sm" required>
                <option value="">-- Select Charge --</option>
                @foreach ($charges as $charge)
                    <option value="{{ $charge->id }}"
                        data-price="{{ $charge->standard_charge }}"
                        data-tax="{{ $charge->tax ?? 0 }}"
                        @selected(old('charge_id', $patientCharge->charge_item == $charge->charge_name ? $charge->id : null) == $charge->id)>
                        {{ $charge->charge_name }} ({{ optional($charge->chargeCategory)->name }})
                        - &#2547; {{ number_format($charge->standard_charge, 2) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Date <span class="text-danger">*</span></label>
            <input type="datetime-local" name="date" class="form-control form-control-sm"
                value="{{ old('date', $patientCharge->date?->format('Y-m-d\TH:i')) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Doctor</label>
            <select name="doctor_id" class="form-select form-select-sm">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('doctor_id', $patientCharge->doctor_id) == $doctor->id)>
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
                    <option value="{{ $dept->id }}" @selected(old('department_id', $patientCharge->department_id) == $dept->id)>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Quantity <span class="text-danger">*</span></label>
            <input type="number" name="quantity" id="opdEditQty" class="form-control form-control-sm"
                value="{{ old('quantity', $patientCharge->quantity) }}" min="1" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Unit Price</label>
            <input type="text" id="opdEditUnit" class="form-control form-control-sm bg-light" readonly value="{{ number_format($patientCharge->unit_price, 2) }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Tax (%)</label>
            <input type="text" id="opdEditTax" class="form-control form-control-sm bg-light" readonly value="0.00">
        </div>

        <div class="col-md-3">
            <label class="form-label">Net Amount</label>
            <input type="text" id="opdEditNet" class="form-control form-control-sm bg-light fw-bold" readonly value="{{ number_format($patientCharge->net_amount, 2) }}">
        </div>

        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Optional notes">{{ old('notes', $patientCharge->notes) }}</textarea>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Update Charge</button>
        </div>
    </div>
</form>

<script>
(function () {
    const sel = document.getElementById('opdEditChargeSelect');
    const qty = document.getElementById('opdEditQty');
    const unit = document.getElementById('opdEditUnit');
    const tax = document.getElementById('opdEditTax');
    const net = document.getElementById('opdEditNet');

    function calc() {
        const opt = sel.options[sel.selectedIndex];
        const p = parseFloat(opt?.dataset?.price) || 0;
        const t = parseFloat(opt?.dataset?.tax) || 0;
        const q = parseInt(qty.value) || 1;
        const amount = p * q;
        const taxAmt = Math.round(amount * t / 100 * 100) / 100;
        unit.value = p.toFixed(2);
        tax.value = t.toFixed(2);
        net.value = (amount + taxAmt).toFixed(2);
    }
    sel.addEventListener('change', calc);
    qty.addEventListener('input', calc);
    calc();
})();
</script>
