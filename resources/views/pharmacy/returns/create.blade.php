@extends('backend.layouts.master')

@section('title', 'Process Medicine Return')

@section('content')
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.pharmacy.returns') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold text-dark">Process Medicine Return</h4>
            <p class="text-muted mb-0 small">Search a transaction by number to begin.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Transaction lookup --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <label class="form-label fw-semibold small">Search Transaction No.</label>
            <div class="input-group" style="max-width:400px;">
                <input type="text" id="txnSearchInput" class="form-control"
                       placeholder="e.g. OPDT-00001"
                       value="{{ request('txn') }}">
                <button type="button" class="btn btn-primary" id="txnSearchBtn">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
            <div id="txnSearchError" class="text-danger small mt-1 d-none"></div>
        </div>
    </div>

    {{-- Return form (populated by JS or pre-loaded if $transaction is set) --}}
    <div id="returnFormWrapper" class="{{ $transaction ? '' : 'd-none' }}">
        <form action="{{ route('admin.pharmacy.returns.store') }}" method="POST" id="returnForm">
            @csrf
            <input type="hidden" name="transaction_id" id="returnTxnId" value="{{ $transaction?->id }}">

            {{-- Transaction summary --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4" id="txnSummaryCard">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Txn No</span>
                            <span class="fw-semibold" id="summaryTxnNo">{{ $transaction?->transaction_no }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Type</span>
                            <span id="summaryType">{{ $transaction ? strtoupper($transaction->transaction_type) : '' }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Patient / Customer</span>
                            <span id="summaryPatient">
                                @if($transaction)
                                    @if($transaction->transaction_type === 'otc') {{ $transaction->customer_name ?? 'Walk-in' }}
                                    @else {{ $transaction->patient->patient_name ?? '—' }}
                                    @endif
                                @endif
                            </span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Original Total</span>
                            <span class="fw-bold text-primary" id="summaryTotal">
                                {{ $transaction ? number_format($transaction->total_amount, 2) . ' TK' : '' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Return reason --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Return Reason <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g. Wrong medicine, Patient refused..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Note</label>
                            <input type="text" name="note" class="form-control" placeholder="Additional notes...">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Select Items to Return</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small py-2">Include</th>
                                <th class="small py-2">Drug</th>
                                <th class="small py-2 text-center">Dispensed</th>
                                <th class="small py-2 text-center">Max Returnable</th>
                                <th class="small py-2" style="min-width:120px;">Qty to Return</th>
                                <th class="small py-2 text-end">Unit Price</th>
                                <th class="small py-2 text-end">Return Amount</th>
                                <th class="small py-2">Store</th>
                            </tr>
                        </thead>
                        <tbody id="returnItemsBody">
                            @if($transaction)
                                @foreach($transaction->items as $item)
                                @php
                                    $alreadyReturned = \App\Models\Pharmacy\PharmacyReturnItem::where('transaction_item_id', $item->id)->sum('qty_returned');
                                    $maxReturnable = $item->qty_required - $alreadyReturned;
                                @endphp
                                @if($maxReturnable > 0)
                                <tr>
                                    <td>
                                        <input type="hidden" name="items[{{ $loop->index }}][transaction_item_id]" value="{{ $item->id }}" class="item-txn-id">
                                        <input type="checkbox" class="form-check-input item-include-cb" checked>
                                    </td>
                                    <td class="small fw-medium">{{ $item->medicine->medicine_name }}</td>
                                    <td class="small text-center">{{ $item->qty_required }}</td>
                                    <td class="small text-center text-muted">{{ $maxReturnable }}</td>
                                    <td>
                                        <input type="number" name="items[{{ $loop->index }}][qty_returned]"
                                               class="form-control form-control-sm qty-return-input"
                                               value="{{ $maxReturnable }}"
                                               min="1" max="{{ $maxReturnable }}" required>
                                    </td>
                                    <td class="small text-end" data-price="{{ $item->unit_price }}">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="small text-end fw-medium return-amount">{{ number_format($maxReturnable * $item->unit_price, 2) }}</td>
                                    <td class="small text-muted">{{ $item->store }}</td>
                                </tr>
                                @endif
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="6" class="text-end fw-semibold small py-2">Total Return Amount:</td>
                                <td class="text-end fw-bold text-danger py-2" id="returnGrandTotal">
                                    @if($transaction)
                                        @php $total = $transaction->items->sum(fn($i) => $i->unit_price * ($i->qty_required - \App\Models\Pharmacy\PharmacyReturnItem::where('transaction_item_id', $i->id)->sum('qty_returned'))); @endphp
                                        {{ number_format($total, 2) }} TK
                                    @else 0.00 TK @endif
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.pharmacy.returns') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-arrow-return-left me-1"></i> Submit Return
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
const txnItemsUrl = "{{ route('admin.pharmacy.returns.transaction-items') }}";

document.getElementById('txnSearchBtn').addEventListener('click', function () {
    const no     = document.getElementById('txnSearchInput').value.trim();
    const errEl  = document.getElementById('txnSearchError');
    if (!no) { errEl.textContent = 'Please enter a transaction number.'; errEl.classList.remove('d-none'); return; }

    errEl.classList.add('d-none');
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(`${txnItemsUrl}?transaction_no=${encodeURIComponent(no)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                errEl.textContent = data.error;
                errEl.classList.remove('d-none');
                document.getElementById('returnFormWrapper').classList.add('d-none');
            } else {
                populateReturnForm(data);
                document.getElementById('returnFormWrapper').classList.remove('d-none');
            }
        })
        .catch(() => { errEl.textContent = 'Search failed. Try again.'; errEl.classList.remove('d-none'); })
        .finally(() => { this.disabled = false; this.innerHTML = '<i class="bi bi-search me-1"></i> Search'; });
});

function populateReturnForm(data) {
    document.getElementById('returnTxnId').value = data.transaction_id;
    document.getElementById('summaryTxnNo').textContent  = data.transaction_no;
    document.getElementById('summaryType').textContent   = data.transaction_type.toUpperCase();
    document.getElementById('summaryPatient').textContent = data.patient_name;

    const tbody = document.getElementById('returnItemsBody');
    tbody.innerHTML = '';

    data.items.forEach((item, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <input type="hidden" name="items[${idx}][transaction_item_id]" value="${item.id}" class="item-txn-id">
                <input type="checkbox" class="form-check-input item-include-cb" checked>
            </td>
            <td class="small fw-medium">${item.medicine_name}</td>
            <td class="small text-center">${item.qty_required}</td>
            <td class="small text-center text-muted">${item.max_returnable}</td>
            <td>
                <input type="number" name="items[${idx}][qty_returned]"
                       class="form-control form-control-sm qty-return-input"
                       value="${item.max_returnable}" min="1" max="${item.max_returnable}" required>
            </td>
            <td class="small text-end" data-price="${item.unit_price}">${item.unit_price.toFixed(2)}</td>
            <td class="small text-end fw-medium return-amount">${(item.max_returnable * item.unit_price).toFixed(2)}</td>
            <td class="small text-muted">${item.store ?? ''}</td>`;
        tbody.appendChild(tr);
    });

    recalcReturnTotal();
}

function recalcReturnTotal() {
    let total = 0;
    document.querySelectorAll('#returnItemsBody tr').forEach(tr => {
        const cb = tr.querySelector('.item-include-cb');
        if (cb && !cb.checked) return;
        const price = parseFloat(tr.querySelector('[data-price]')?.dataset.price || 0);
        const qty   = parseInt(tr.querySelector('.qty-return-input')?.value || 0);
        const amtEl = tr.querySelector('.return-amount');
        const amt   = price * qty;
        if (amtEl) amtEl.textContent = amt.toFixed(2);
        total += amt;
    });
    document.getElementById('returnGrandTotal').textContent = total.toFixed(2) + ' TK';
}

document.getElementById('returnItemsBody').addEventListener('input', function (e) {
    if (e.target.classList.contains('qty-return-input')) recalcReturnTotal();
});
document.getElementById('returnItemsBody').addEventListener('change', function (e) {
    if (e.target.classList.contains('item-include-cb')) {
        const tr      = e.target.closest('tr');
        const txnIdEl = tr.querySelector('.item-txn-id');
        const qtyEl   = tr.querySelector('.qty-return-input');
        if (!e.target.checked) {
            txnIdEl.disabled = true;
            qtyEl.disabled   = true;
            qtyEl.removeAttribute('required');
        } else {
            txnIdEl.disabled = false;
            qtyEl.disabled   = false;
            qtyEl.required   = true;
        }
        recalcReturnTotal();
    }
});
</script>
@endpush
