@php
    $payments = $iPDPatient->transactions()->latest()->get();
@endphp
<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Patient Payments</h6>
        </div>
        <div>
            <a href="{{ route('ipd-patients.payments.create', $iPDPatient->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Add Payment
            </a>
        </div>
    </div>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>SN</th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Type</th>
                <th>Via</th>
                <th class="text-end">Amount</th>
                <th class="text-end">VAT(%)</th>
                <th class="text-end">Tax(%)</th>
                <th class="text-end">Discount(%)</th>
                <th class="text-end">Net</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $payment->invoice_no }}</td>
                    <td>{{ format_datetime($payment->payment_date) ?? 'N/A' }}</td>
                    <td>{{ ucfirst($payment->type ?? '-') }}</td>
                    <td>{{ ucfirst($payment->payment_via ?? '-') }}</td>
                    @php
                        $vatAmt = ($payment->amount * $payment->vat) / 100;
                        $taxAmt = ($payment->amount * $payment->tax) / 100;
                        $subAmt = $payment->amount + $vatAmt + $taxAmt;
                        $discAmt = ($subAmt * $payment->discount) / 100;
                    @endphp
                    <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                    <td class="text-end">{{ number_format($vatAmt, 2) }}({{ number_format($payment->vat, 2) }}%)</td>
                    <td class="text-end">{{ number_format($taxAmt, 2) }}({{ number_format($payment->tax, 2) }}%)</td>
                    <td class="text-end">{{ number_format($discAmt, 2) }}({{ number_format($payment->discount, 2) }}%)
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($payment->net_amount, 2) }}</td>
                    <td>
                        @php
                            $map = [
                                'successed' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'canceled' => 'secondary',
                            ];
                            $cls = $map[$payment->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $cls }}">{{ ucfirst($payment->status) }}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <button type="button" class="btn btn-sm btn-outline-info" title="View Details"
                                data-bs-toggle="modal" data-bs-target="#paymentDetailsModal{{ $payment->id }}">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="{{ route('ipd-patients.payments.edit', [$iPDPatient->id, $payment->id]) }}"
                                class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form
                                action="{{ route('ipd-patients.payments.destroy', [$iPDPatient->id, $payment->id]) }}"
                                method="POST" onsubmit="return confirm('Delete this payment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">No payments available.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($payments->count())
            <tfoot class="table-light">
                <tr>
                    <td colspan="9" class="text-end fw-bold">Total:</td>
                    <td class="text-end fw-bold">{{ number_format($payments->sum('net_amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    @foreach ($payments as $payment)
        @php
            $files = $payment->files ? (json_decode($payment->files, true) ?: []) : [];
        @endphp
        <div class="modal fade" id="paymentDetailsModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Payment Details — {{ $payment->invoice_no }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th style="width:35%">Invoice No</th>
                                    <td>{{ $payment->invoice_no ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Date</th>
                                    <td>{{ format_datetime($payment->payment_date) ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ ucfirst($payment->type ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <th>Section</th>
                                    <td>{{ ucfirst($payment->section ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Via</th>
                                    <td>{{ ucfirst($payment->payment_via ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                @php
                                    $vatAmtM = ($payment->amount * $payment->vat) / 100;
                                    $taxAmtM = ($payment->amount * $payment->tax) / 100;
                                    $subAmtM = $payment->amount + $vatAmtM + $taxAmtM;
                                    $discAmtM = ($subAmtM * $payment->discount) / 100;
                                @endphp
                                <tr>
                                    <th>VAT</th>
                                    <td>{{ number_format($vatAmtM, 2) }}({{ number_format($payment->vat, 2) }}%) </td>
                                </tr>
                                <tr>
                                    <th>Tax</th>
                                    <td>{{ number_format($taxAmtM, 2) }}({{ number_format($payment->tax, 2) }}%)</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Discount</th>
                                    <td>{{ number_format($discAmtM, 2) }}({{ number_format($payment->discount, 2) }}%)
                                    </td>
                                </tr>
                                <tr>
                                    <th>Net Amount</th>
                                    <td class="fw-semibold">{{ number_format($payment->net_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @php
                                            $map2 = [
                                                'successed' => 'success',
                                                'pending' => 'warning',
                                                'failed' => 'danger',
                                                'canceled' => 'secondary',
                                            ];
                                            $cls2 = $map2[$payment->status] ?? 'secondary';
                                        @endphp
                                        <span
                                            class="badge bg-{{ $cls2 }}">{{ ucfirst($payment->status) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Received By</th>
                                    <td>{{ $payment->received_by ?? '-' }}</td>
                                </tr>
                                @if ($payment->payment_via === 'card')
                                    <tr>
                                        <th>Card No</th>
                                        <td>{{ $payment->card_no ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Card Type</th>
                                        <td>{{ ucfirst($payment->card_type ?? '-') }}</td>
                                    </tr>
                                @elseif ($payment->payment_via === 'cheque')
                                    <tr>
                                        <th>Cheque Name</th>
                                        <td>{{ $payment->cheque_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Cheque No</th>
                                        <td>{{ $payment->cheque_no ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Cheque Date</th>
                                        <td>{{ format_datetime($payment->cheque_date) ?? '-' }}</td>
                                    </tr>
                                @elseif ($payment->payment_via === 'mfs')
                                    <tr>
                                        <th>MFS Type</th>
                                        <td>{{ ucfirst($payment->mfs_type ?? '-') }}</td>
                                    </tr>
                                    <tr>
                                        <th>MFS No</th>
                                        <td>{{ $payment->mfs_no ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>MFS Transaction ID</th>
                                        <td>{{ $payment->mfs_transaction_id ?? '-' }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Notes</th>
                                    <td>{{ $payment->notes ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Attachments</th>
                                    <td>
                                        @if (count($files))
                                            <ul class="mb-0 ps-3">
                                                @foreach ($files as $f)
                                                    <li><a href="{{ asset('storage/' . $f) }}"
                                                            target="_blank">{{ basename($f) }}</a></li>
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ format_datetime($payment->created_at) ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ format_datetime($payment->updated_at) ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
