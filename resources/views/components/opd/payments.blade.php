@props(['opdPatient'])

@php($payments = $opdPatient->transactions)

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-cash-coin me-2 text-success"></i>Payment History
            <span class="badge bg-success-subtle text-success ms-2">{{ $payments->count() }}</span>
        </h6>
        <a href="javascript:void(0);" class="btn btn-sm btn-primary"
            data-url="{{ route('opd-patients.payments.create', $opdPatient->id) }}" data-ajax-popup="true"
            data-title="Add Payment" data-size="md">
            <i class="bi bi-plus"></i> Add Payment
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 custom-table">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th class="text-end">Amount</th>
                        <th>Payment Mode</th>
                        <th>Notes</th>
                        <th>Received By</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $payment->invoice_no ?? '-' }}</td>
                            <td>{{ $payment->payment_date ? \Illuminate\Support\Carbon::parse($payment->payment_date)->format('d M Y') : '-' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($payment->net_amount, 2) }}</td>
                            <td class="text-capitalize">{{ $payment->payment_via ?? '-' }}</td>
                            <td>{{ $payment->notes ?? '-' }}</td>
                            <td>{{ optional(\App\Models\User::find($payment->received_by))->name ?? '-' }}</td>
                            <td class="text-end">
                                <form action="{{ route('opd-patients.payments.destroy', [$opdPatient->id, $payment->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this payment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">No payments recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($payments->count())
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total Paid:</td>
                            <td class="text-end fw-bold">{{ number_format($payments->sum('net_amount'), 2) }}</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
