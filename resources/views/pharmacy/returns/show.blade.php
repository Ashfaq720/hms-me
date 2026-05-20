@extends('backend.layouts.master')

@section('title', 'Return — ' . $return->return_no)

@section('content')
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">{{ $return->return_no }}</h4>
            @php $rc = match($return->status) { 'completed' => 'success', default => 'warning' }; @endphp
            <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">{{ ucfirst($return->status) }}</span>
        </div>
        <div class="d-flex gap-2">
            @if($return->status === 'pending')
                <form action="{{ route('admin.pharmacy.returns.approve', $return->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"
                            onclick="return confirm('Approve return and restore stock?')">
                        <i class="bi bi-check-circle me-1"></i> Approve & Restore Stock
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.pharmacy.returns') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Return Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted small">Return No</td><td class="fw-semibold small">{{ $return->return_no }}</td></tr>
                        <tr><td class="text-muted small">Original Txn</td>
                            <td class="small">
                                <a href="{{ route('admin.pharmacy.transactions.show', $return->transaction_id) }}" class="text-decoration-none">
                                    {{ $return->transaction->transaction_no }}
                                </a>
                            </td>
                        </tr>
                        <tr><td class="text-muted small">Type</td>
                            <td>
                                @php $typeClass = match($return->transaction_type) { 'opd' => 'bg-primary', 'ipd' => 'bg-info text-dark', 'otc' => 'bg-success', default => 'bg-secondary' }; @endphp
                                <span class="badge {{ $typeClass }}">{{ strtoupper($return->transaction_type) }}</span>
                            </td>
                        </tr>
                        <tr><td class="text-muted small">Patient</td><td class="small">{{ $return->patient->patient_name ?? 'Walk-in' }}</td></tr>
                        <tr><td class="text-muted small">Returned By</td><td class="small">{{ $return->returnedBy->name ?? '—' }}</td></tr>
                        <tr><td class="text-muted small">Date</td><td class="small">{{ $return->created_at->format('d M Y, h:i A') }}</td></tr>
                        <tr><td class="text-muted small">Status</td>
                            <td><span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">{{ ucfirst($return->status) }}</span></td>
                        </tr>
                        <tr><td class="text-muted small">Reason</td><td class="small">{{ $return->reason ?? '—' }}</td></tr>
                        <tr><td class="text-muted small">Total</td><td class="fw-bold small text-danger">{{ number_format($return->total_amount, 2) }} TK</td></tr>
                    </table>
                    @if($return->note)
                        <hr class="my-2">
                        <p class="text-muted small mb-1">Note</p>
                        <p class="small mb-0">{{ $return->note }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Return Items ({{ $return->items->count() }})</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small py-2">Drug</th>
                                <th class="small py-2 text-center">Qty Returned</th>
                                <th class="small py-2">Store</th>
                                <th class="small py-2 text-end">Unit Price</th>
                                <th class="small py-2 text-end">Return Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->items as $item)
                            <tr>
                                <td class="small fw-medium">{{ $item->medicine->medicine_name ?? '—' }}</td>
                                <td class="small text-center">{{ $item->qty_returned }}</td>
                                <td class="small text-muted">{{ $item->store ?? '—' }}</td>
                                <td class="small text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="small text-end fw-medium text-danger">{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-semibold small py-2">Total Return:</td>
                                <td class="text-end fw-bold text-danger py-2">{{ number_format($return->total_amount, 2) }} TK</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($return->status === 'pending')
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Stock will be restored to the pharmacy only after this return is <strong>approved</strong>.
            </div>
            @elseif($return->status === 'completed')
            <div class="alert alert-success mt-3">
                <i class="bi bi-check-circle me-2"></i>
                Stock has been restored for all items in this return.
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
