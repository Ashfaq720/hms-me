@extends('backend.layouts.master')
@section('title', 'Bill ' . $bill->bill_no)
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="app-page-title">{{ $bill->bill_no }}</h1>
            <p class="text-muted small mb-0">
                {{ strtoupper($bill->bill_type) }} bill ·
                {{ optional($bill->patient)->patient_name }} (MRN {{ optional($bill->patient)->mrn }}) ·
                {{ optional($bill->bill_date)->toDateString() }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('billing.bills.print', $bill) }}" class="btn btn-outline-primary" target="_blank">Print</a>
            @if (in_array($bill->status, ['final','paid','partially_paid']))
                @can('insurance.claim.submit')
                    <form method="POST" action="{{ route('insurance.claims.build', $bill) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-outline-success" title="Build an insurance claim from this bill">Build Claim</button>
                    </form>
                @endcan
            @endif
            <a href="{{ route('billing.bills.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger mt-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row mt-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <strong>Items</strong>
                    <div>
                        @if (! $bill->isFinal())
                            @can('billing.bill.finalize')
                                <form method="POST" action="{{ route('billing.bills.finalize',$bill) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-primary">Finalize bill</button>
                                </form>
                            @endcan
                        @endif
                        @if ($bill->status !== 'cancelled')
                            @can('billing.bill.cancel')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel</button>
                            @endcan
                        @endif
                    </div>
                </div>
                <table class="table mb-0">
                    <thead><tr><th>Description</th><th>Type</th><th class="text-end">Qty</th><th class="text-end">Unit</th><th class="text-end">Tax</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @forelse ($bill->items as $item)
                            <tr>
                                <td>{{ $item->description }}@if($item->is_package_included) <span class="badge bg-success">Package</span>@endif</td>
                                <td><span class="badge bg-info-soft">{{ $item->item_type }}</span></td>
                                <td class="text-end">{{ number_format((float) $item->quantity, 4) }}</td>
                                <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format((float) $item->tax_amount, 2) }}</td>
                                <td class="text-end">{{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @empty <tr><td colspan="6" class="text-center text-muted py-3">No items.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card mt-3">
                <div class="card-header"><strong>Payments</strong></div>
                <table class="table mb-0">
                    <thead><tr><th>Receipt</th><th>Date</th><th>Method</th><th>Reference</th><th class="text-end">Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($bill->payments as $p)
                            <tr>
                                <td><code>{{ $p->receipt_no }}</code></td>
                                <td>{{ optional($p->payment_date)->toDateString() }}</td>
                                <td>{{ $p->method }}</td>
                                <td>{{ $p->reference_no }}</td>
                                <td class="text-end">{{ number_format((float) $p->amount, 2) }}</td>
                                <td><span class="badge bg-{{ $p->status === 'received' ? 'success' : 'secondary' }}">{{ $p->status }}</span></td>
                            </tr>
                        @empty <tr><td colspan="6" class="text-center text-muted py-3">No payments yet.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Summary</h5>
                    <dl class="row mb-0">
                        <dt class="col-6">Subtotal</dt><dd class="col-6 text-end">{{ number_format((float)$bill->subtotal,2) }}</dd>
                        <dt class="col-6">Discount</dt><dd class="col-6 text-end">{{ number_format((float)$bill->discount_total,2) }}</dd>
                        <dt class="col-6">Tax</dt><dd class="col-6 text-end">{{ number_format((float)$bill->tax_total,2) }}</dd>
                        <dt class="col-6 fw-bold">Grand total</dt><dd class="col-6 text-end fw-bold">{{ number_format((float)$bill->grand_total,2) }}</dd>
                        <dt class="col-6">Paid</dt><dd class="col-6 text-end">{{ number_format((float)$bill->paid_total,2) }}</dd>
                        <dt class="col-6">Refunded</dt><dd class="col-6 text-end">{{ number_format((float)$bill->refund_total,2) }}</dd>
                        <dt class="col-6 fw-bold {{ $bill->balance_due > 0 ? 'text-danger' : '' }}">Balance due</dt>
                        <dd class="col-6 text-end fw-bold {{ $bill->balance_due > 0 ? 'text-danger' : '' }}">{{ number_format((float)$bill->balance_due,2) }}</dd>
                    </dl>
                </div>
            </div>

            @if ($bill->balance_due > 0 && $bill->status !== 'cancelled')
                @can('billing.payment.collect')
                    <div class="card mt-3"><div class="card-body">
                        <h6>Collect Payment</h6>
                        <form method="POST" action="{{ route('billing.bills.payment',$bill) }}">
                            @csrf
                            <div class="mb-2"><label class="form-label">Amount</label>
                                <input type="number" step="0.01" min="0.01" max="{{ $bill->balance_due }}" name="amount" class="form-control" required></div>
                            <div class="mb-2"><label class="form-label">Method</label>
                                <select name="method" class="form-select" required>
                                    @foreach (['cash','card','mfs','cheque','bank_transfer','insurance','corporate','advance','other'] as $m)
                                        <option value="{{ $m }}">{{ ucfirst($m) }}</option>
                                    @endforeach
                                </select></div>
                            <div class="mb-2"><label class="form-label">Reference (txn#)</label>
                                <input type="text" name="reference_no" class="form-control"></div>
                            <button class="btn btn-primary w-100">Collect</button>
                        </form>
                    </div></div>
                @endcan
            @endif

            @if (! $bill->isFinal())
                @can('billing.discount.approve')
                    <div class="card mt-3"><div class="card-body">
                        <h6>Apply Discount / Waiver</h6>
                        <form method="POST" action="{{ route('billing.bills.discount',$bill) }}">
                            @csrf
                            <div class="row g-2 mb-2">
                                <div class="col-6"><select name="kind" class="form-select"><option value="discount">Discount</option><option value="waiver">Waiver</option></select></div>
                                <div class="col-6"><select name="mode" class="form-select"><option value="percent">Percent</option><option value="flat">Flat</option></select></div>
                            </div>
                            <div class="mb-2"><input type="number" step="0.01" min="0" name="value" class="form-control" placeholder="Value" required></div>
                            <div class="mb-2"><input type="text" name="reason" class="form-control" placeholder="Reason" required></div>
                            <button class="btn btn-outline-primary w-100">Apply</button>
                        </form>
                    </div></div>
                @endcan
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('billing.bills.cancel',$bill) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Cancel bill</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p>This will mark the bill as cancelled and create an audit log entry.</p>
                <label class="form-label">Reason</label>
                <textarea name="reason" rows="3" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-danger">Cancel bill</button>
            </div>
        </form>
    </div>
</div>
@endsection
