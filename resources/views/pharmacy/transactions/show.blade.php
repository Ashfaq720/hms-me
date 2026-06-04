@extends('backend.layouts.master')

@section('title', 'Transaction — ' . $transaction->transaction_no)

@section('content')
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">{{ $transaction->transaction_no }}</h4>
            <span class="badge {{ $transaction->type_badge_class }} me-2">{{ $transaction->type_label }}</span>
            @php $stClass = match($transaction->status) { 'completed' => 'success', 'approved' => 'info', 'pending' => 'warning', default => 'secondary' }; @endphp
            <span class="badge bg-{{ $stClass }}-subtle text-{{ $stClass }}">{{ ucfirst($transaction->status) }}</span>
        </div>
        <div class="d-flex gap-2">
            @if($transaction->status === 'pending')
                <form action="{{ route('admin.pharmacy.transactions.approve', $transaction->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"
                            onclick="return confirm('Approve this transaction?')">
                        <i class="bi bi-check-circle me-1"></i> Approve
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.pharmacy.returns.create') }}?txn={{ $transaction->transaction_no }}"
               class="btn btn-outline-warning btn-sm">
                <i class="bi bi-arrow-return-left me-1"></i> Process Return
            </a>
            <a href="{{ route('admin.pharmacy.transactions') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left: Details --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Transaction Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted small">Txn No</td><td class="fw-semibold small">{{ $transaction->transaction_no }}</td></tr>
                        <tr><td class="text-muted small">Type</td><td><span class="badge {{ $transaction->type_badge_class }}">{{ $transaction->type_label }}</span></td></tr>
                        <tr><td class="text-muted small">Date</td><td class="small">{{ $transaction->created_at->format('d M Y, h:i A') }}</td></tr>
                        <tr><td class="text-muted small">Pharmacist</td><td class="small">{{ $transaction->pharmacist->name ?? '—' }}</td></tr>
                        <tr><td class="text-muted small">Status</td>
                            <td><span class="badge bg-{{ $stClass }}-subtle text-{{ $stClass }}">{{ ucfirst($transaction->status) }}</span></td>
                        </tr>
                        @if($transaction->payment_status)
                            @php $payClass = match($transaction->payment_status) { 'paid' => 'success', 'partial' => 'warning', default => 'danger' }; @endphp
                            <tr><td class="text-muted small">Payment</td>
                                <td><span class="badge bg-{{ $payClass }}-subtle text-{{ $payClass }}">{{ ucfirst($transaction->payment_status) }}</span></td>
                            </tr>
                        @endif
                        @if($transaction->payment_method)
                            <tr><td class="text-muted small">Pay Method</td><td class="small">{{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}</td></tr>
                        @endif
                    </table>

                    {{-- Type-specific details --}}
                    @if($transaction->transaction_type === 'opd')
                        <hr class="my-2">
                        <p class="text-muted small mb-1 fw-semibold">OPD Details</p>
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted small">Patient</td><td class="small">{{ $transaction->patient->patient_name ?? '—' }}</td></tr>
                            <tr><td class="text-muted small">OPD Case</td><td class="small">{{ $transaction->opdPatient->case_id ?? '—' }}</td></tr>
                            @if($transaction->prescription)
                                <tr><td class="text-muted small">Prescription</td><td class="small">{{ $transaction->prescription->prescription_no }}</td></tr>
                            @endif
                        </table>
                    @elseif($transaction->transaction_type === 'ipd')
                        <hr class="my-2">
                        <p class="text-muted small mb-1 fw-semibold">Ipd Details</p>
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted small">Patient</td><td class="small">{{ $transaction->patient->patient_name ?? '—' }}</td></tr>
                            <tr><td class="text-muted small">Ipd No</td><td class="small">{{ $transaction->ipdPatient->ipd_no ?? '—' }}</td></tr>
                            <tr><td class="text-muted small">Ward / Bed</td><td class="small">{{ $transaction->ward_bed ?? '—' }}</td></tr>
                            <tr><td class="text-muted small">Req. No</td><td class="small">{{ $transaction->requisition_no ?? '—' }}</td></tr>
                            <tr><td class="text-muted small">Req. Source</td><td class="small">{{ $transaction->request_source ?? '—' }}</td></tr>
                        </table>
                    @elseif($transaction->transaction_type === 'otc')
                        <hr class="my-2">
                        <p class="text-muted small mb-1 fw-semibold">Customer Details</p>
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted small">Name</td><td class="small">{{ $transaction->customer_name ?? 'Walk-in' }}</td></tr>
                            <tr><td class="text-muted small">Phone</td><td class="small">{{ $transaction->customer_phone ?? '—' }}</td></tr>
                        </table>
                    @endif

                    <hr class="my-2">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted small">Total Amount</td><td class="fw-bold small text-primary">{{ number_format($transaction->total_amount, 2) }} TK</td></tr>
                        @if($transaction->discount_amount > 0)
                            <tr><td class="text-muted small">Discount</td><td class="small text-danger">- {{ number_format($transaction->discount_amount, 2) }} TK</td></tr>
                        @endif
                        @if($transaction->paid_amount > 0)
                            <tr><td class="text-muted small">Paid</td><td class="small text-success">{{ number_format($transaction->paid_amount, 2) }} TK</td></tr>
                        @endif
                    </table>

                    @if($transaction->note)
                        <hr class="my-2">
                        <p class="text-muted small mb-1">Note</p>
                        <p class="small mb-0">{{ $transaction->note }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Items --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Medicine Items ({{ $transaction->items->count() }})</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small py-2">Drug</th>
                                @if($transaction->transaction_type === 'ipd')
                                    <th class="small py-2">Duration</th>
                                @else
                                    <th class="small py-2">Dosage</th>
                                @endif
                                <th class="small py-2 text-center">Qty</th>
                                <th class="small py-2">Store</th>
                                <th class="small py-2 text-end">Unit Price</th>
                                <th class="small py-2 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->items as $item)
                            <tr>
                                <td class="small fw-medium">{{ $item->medicine->medicine_name ?? '—' }}</td>
                                <td class="small text-muted">
                                    {{ $transaction->transaction_type === 'ipd' ? ($item->duration ?? '—') : ($item->dosage ?? '—') }}
                                </td>
                                <td class="small text-center">{{ $item->qty_required }}</td>
                                <td class="small text-muted">{{ $item->store ?? '—' }}</td>
                                <td class="small text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="small text-end fw-medium">{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end fw-semibold small py-2">Total:</td>
                                <td class="text-end fw-bold text-primary py-2">{{ number_format($transaction->total_amount, 2) }} TK</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Returns --}}
            @if($transaction->returns->count() > 0)
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold text-warning">Returns ({{ $transaction->returns->count() }})</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small py-2">Return No</th>
                                <th class="small py-2">Date</th>
                                <th class="small py-2">Reason</th>
                                <th class="small py-2 text-end">Amount</th>
                                <th class="small py-2">Status</th>
                                <th class="small py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->returns as $rtn)
                            <tr>
                                <td class="small fw-semibold">{{ $rtn->return_no }}</td>
                                <td class="small text-muted">{{ $rtn->created_at->format('d/m/Y') }}</td>
                                <td class="small">{{ $rtn->reason ?? '—' }}</td>
                                <td class="small text-end">{{ number_format($rtn->total_amount, 2) }}</td>
                                <td>
                                    @php $rc = match($rtn->status) { 'completed' => 'success', 'approved' => 'info', default => 'warning' }; @endphp
                                    <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">{{ ucfirst($rtn->status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.pharmacy.returns.show', $rtn->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
