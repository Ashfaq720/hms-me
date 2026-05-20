@php
    $payment = $payment ?? null;
    $val = function ($field, $default = null) use ($payment) {
        return old($field, $payment->$field ?? $default);
    };
@endphp

{{-- ===== Section: Payment Info ===== --}}
<div class="form-section-title"><i class="bi bi-info-circle"></i> Payment Info</div>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label">Type</label>
        <select name="type" class="form-select">
            @foreach (['payment', 'advance', 'refund', 'adjustment'] as $t)
                <option value="{{ $t }}" @selected($val('type', 'payment') == $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Payment Via <span class="text-danger">*</span></label>
        <select name="payment_via" id="payment_via" class="form-select">
            <option value="">-- Select --</option>
            @foreach (['cash', 'card', 'cheque', 'mfs', 'other'] as $v)
                <option value="{{ $v }}" @selected($val('payment_via') == $v)>{{ ucfirst($v) }}</option>
            @endforeach
        </select>
        @error('payment_via')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Payment Date</label>
        <input type="datetime-local" name="payment_date"
            value="{{ old('payment_date', isset($payment->payment_date) ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
            class="form-control">
        @error('payment_date')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Received By</label>
        <input type="text" name="received_by" value="{{ $val('received_by') }}" class="form-control"
            placeholder="Cashier / Staff name">
    </div>

    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (['successed', 'pending', 'failed', 'canceled'] as $s)
                <option value="{{ $s }}" @selected($val('status', 'successed') == $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- ===== Section: Amount Breakdown ===== --}}
<div class="form-section-title"><i class="bi bi-calculator"></i> Amount Breakdown</div>
<div class="row g-3 mb-2">
    <div class="col-md-3">
        <label class="form-label">Amount</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" name="amount" id="amt_amount" value="{{ $val('amount', 0) }}"
                class="form-control amount-input" min="0">
        </div>
        @error('amount')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">VAT (% of amount)</label>
        <div class="input-group">
            <input type="number" step="0.01" min="0" max="100" name="vat" id="amt_vat"
                value="{{ $val('vat', 0) }}" class="form-control amount-input">
            <span class="input-group-text">%</span>
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Tax (% of amount)</label>
        <div class="input-group">
            <input type="number" step="0.01" min="0" max="100" name="tax" id="amt_tax"
                value="{{ $val('tax', 0) }}" class="form-control amount-input">
            <span class="input-group-text">%</span>
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Discount (% of amount)</label>
        <div class="input-group">
            <input type="number" step="0.01" min="0" max="100" name="discount" id="amt_discount"
                value="{{ $val('discount', 0) }}" class="form-control amount-input">
            <span class="input-group-text">%</span>
        </div>
    </div>

    <div class="col-md-12">
        <div class="alert alert-light border d-flex justify-content-between align-items-center mb-0 py-2">
            <span class="text-muted small">
                Subtotal = Amount + (Amount × VAT%) + (Amount × Tax%); Net = Subtotal − (Subtotal × Discount%)
            </span>
            <span class="fw-bold">Net Amount: ৳ <span id="amt_net">0.00</span></span>
        </div>
    </div>
</div>

{{-- ===== Section: Method Details (conditional) ===== --}}
<div id="cardFields" class="via-panel rounded-3 p-3 mb-3 {{ $val('payment_via') == 'card' ? '' : 'd-none' }}">
    <div class="form-section-title mb-3"><i class="bi bi-credit-card"></i> Card Details</div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Card No</label>
            <input type="text" name="card_no" value="{{ $val('card_no') }}" class="form-control"
                placeholder="**** **** **** 1234">
        </div>
        <div class="col-md-6">
            <label class="form-label">Card Type</label>
            <select name="card_type" class="form-select">
                <option value="">-- Select --</option>
                @foreach (['visa', 'master', 'american_express', 'other'] as $c)
                    <option value="{{ $c }}" @selected($val('card_type') == $c)>
                        {{ ucfirst(str_replace('_', ' ', $c)) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div id="chequeFields" class="via-panel rounded-3 p-3 mb-3 {{ $val('payment_via') == 'cheque' ? '' : 'd-none' }}">
    <div class="form-section-title mb-3"><i class="bi bi-receipt"></i> Cheque Details</div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Cheque Name</label>
            <input type="text" name="cheque_name" value="{{ $val('cheque_name') }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Cheque No</label>
            <input type="text" name="cheque_no" value="{{ $val('cheque_no') }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Cheque Date</label>
            <input type="date" name="cheque_date" value="{{ old('cheque_date', $payment->cheque_date ?? '') }}"
                class="form-control">
        </div>
    </div>
</div>

<div id="mfsFields" class="via-panel rounded-3 p-3 mb-3 {{ $val('payment_via') == 'mfs' ? '' : 'd-none' }}">
    <div class="form-section-title mb-3"><i class="bi bi-phone"></i> MFS Details</div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">MFS Type</label>
            <select name="mfs_type" class="form-select">
                <option value="">-- Select --</option>
                @foreach (['bkash', 'nagad', 'rocket', 'other'] as $m)
                    <option value="{{ $m }}" @selected($val('mfs_type') == $m)>{{ ucfirst($m) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">MFS No</label>
            <input type="text" name="mfs_no" value="{{ $val('mfs_no') }}" class="form-control"
                placeholder="01XXXXXXXXX">
        </div>
        <div class="col-md-4">
            <label class="form-label">Transaction ID</label>
            <input type="text" name="mfs_transaction_id" value="{{ $val('mfs_transaction_id') }}"
                class="form-control">
        </div>
    </div>
</div>

{{-- ===== Section: Notes & Files ===== --}}
<div class="form-section-title"><i class="bi bi-paperclip"></i> Notes & Attachments</div>
<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Any note for this payment">{{ $val('notes') }}</textarea>
    </div>

    <div class="col-md-12">
        <label class="form-label">Files</label>
        <input type="file" name="files[]" class="form-control" multiple>
        <div class="form-text">Upload receipt / cheque scan / supporting documents.</div>
        @if (!empty($payment) && $payment->files)
            @php $existing = json_decode($payment->files, true) ?: []; @endphp
            @if (count($existing))
                <div class="file-existing mt-2">
                    @foreach ($existing as $f)
                        <a href="{{ asset('storage/' . $f) }}" target="_blank">
                            <i class="bi bi-file-earmark"></i> {{ basename($f) }}
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const via = document.getElementById('payment_via');
        const card = document.getElementById('cardFields');
        const cheque = document.getElementById('chequeFields');
        const mfs = document.getElementById('mfsFields');
        const toggle = () => {
            card.classList.toggle('d-none', via.value !== 'card');
            cheque.classList.toggle('d-none', via.value !== 'cheque');
            mfs.classList.toggle('d-none', via.value !== 'mfs');
        };
        via.addEventListener('change', toggle);

        const amt = document.getElementById('amt_amount');
        const vat = document.getElementById('amt_vat');
        const tax = document.getElementById('amt_tax');
        const dis = document.getElementById('amt_discount');
        const net = document.getElementById('amt_net');
        const num = el => parseFloat(el.value) || 0;
        const recalc = () => {
            const a = num(amt);
            const sub = a + (a * num(vat) / 100) + (a * num(tax) / 100);
            const n = sub - (sub * num(dis) / 100);
            net.textContent = n.toFixed(2);
        };
        [amt, vat, tax, dis].forEach(el => el.addEventListener('input', recalc));
        recalc();
    });
</script>
