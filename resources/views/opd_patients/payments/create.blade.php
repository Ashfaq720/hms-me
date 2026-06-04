<form action="{{ route('opd-patients.payments.store', $opdPatient->id) }}" method="POST">
    @csrf

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" name="payment_date" class="form-control form-control-sm"
                value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-sm"
                value="{{ old('amount') }}" placeholder="0.00" required>
        </div>

        <div class="col-md-12">
            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
            <select name="payment_via" id="opdPayMode" class="form-select form-select-sm" required>
                <option value="">-- Select Payment Mode --</option>
                @foreach (['cash' => 'Cash', 'card' => 'Card', 'cheque' => 'Cheque', 'bank' => 'Bank Transfer', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'other' => 'Other'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('payment_via') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Card fields --}}
        <div class="col-md-6 opd-pay-extra opd-pay-card d-none">
            <label class="form-label">Card No</label>
            <input type="text" name="card_no" class="form-control form-control-sm" value="{{ old('card_no') }}">
        </div>
        <div class="col-md-6 opd-pay-extra opd-pay-card d-none">
            <label class="form-label">Card Type</label>
            <input type="text" name="card_type" class="form-control form-control-sm" value="{{ old('card_type') }}" placeholder="Visa / Master / Amex">
        </div>

        {{-- Cheque fields --}}
        <div class="col-md-4 opd-pay-extra opd-pay-cheque d-none">
            <label class="form-label">Cheque No</label>
            <input type="text" name="cheque_no" class="form-control form-control-sm" value="{{ old('cheque_no') }}">
        </div>
        <div class="col-md-4 opd-pay-extra opd-pay-cheque d-none">
            <label class="form-label">Cheque Date</label>
            <input type="date" name="cheque_date" class="form-control form-control-sm" value="{{ old('cheque_date') }}">
        </div>
        <div class="col-md-4 opd-pay-extra opd-pay-cheque d-none">
            <label class="form-label">Bank / Cheque Name</label>
            <input type="text" name="cheque_name" class="form-control form-control-sm" value="{{ old('cheque_name') }}">
        </div>

        {{-- Bank Transfer fields --}}
        <div class="col-md-6 opd-pay-extra opd-pay-bank d-none">
            <label class="form-label">Bank Name</label>
            <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ old('bank_name') }}">
        </div>
        <div class="col-md-6 opd-pay-extra opd-pay-bank d-none">
            <label class="form-label">Transaction ID</label>
            <input type="text" name="bank_transaction_id" class="form-control form-control-sm" value="{{ old('bank_transaction_id') }}">
        </div>

        {{-- MFS fields (bKash / Nagad / Rocket) --}}
        <div class="col-md-6 opd-pay-extra opd-pay-mfs d-none">
            <label class="form-label">MFS Number</label>
            <input type="text" name="mfs_no" class="form-control form-control-sm" value="{{ old('mfs_no') }}">
        </div>
        <div class="col-md-6 opd-pay-extra opd-pay-mfs d-none">
            <label class="form-label">Transaction ID</label>
            <input type="text" name="mfs_transaction_id" class="form-control form-control-sm" value="{{ old('mfs_transaction_id') }}">
        </div>

        <div class="col-md-12">
            <label class="form-label">Note</label>
            <textarea name="notes" rows="3" class="form-control form-control-sm" placeholder="Optional note">{{ old('notes') }}</textarea>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Save Payment</button>
        </div>
    </div>
</form>

<script>
(function () {
    const sel = document.getElementById('opdPayMode');
    const groups = {
        card:   document.querySelectorAll('.opd-pay-card'),
        cheque: document.querySelectorAll('.opd-pay-cheque'),
        bank:   document.querySelectorAll('.opd-pay-bank'),
        bkash:  document.querySelectorAll('.opd-pay-mfs'),
        nagad:  document.querySelectorAll('.opd-pay-mfs'),
        rocket: document.querySelectorAll('.opd-pay-mfs'),
    };

    function refresh() {
        document.querySelectorAll('.opd-pay-extra').forEach(el => el.classList.add('d-none'));
        const mode = sel.value;
        if (groups[mode]) groups[mode].forEach(el => el.classList.remove('d-none'));
    }

    sel.addEventListener('change', refresh);
    refresh();
})();
</script>
