@props(['opdPatient'])

@php($charges = $opdPatient->charges)

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-receipt-cutoff me-2 text-warning"></i>Patient Charges
            <span class="badge bg-warning-subtle text-warning ms-2">{{ $charges->count() }}</span>
        </h6>
        <a href="javascript:void(0);" class="btn btn-sm btn-primary"
            data-url="{{ route('opd-patients.charges.create', $opdPatient->id) }}" data-ajax-popup="true"
            data-title="Add Charge" data-size="xl">
            <i class="bi bi-plus"></i> Add Charge
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 custom-table">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Charge Item</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Tax</th>
                        <th class="text-end">Net Amount</th>
                        <th class="text-center">Paid</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($charges as $charge)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $charge->date ? format_datetime($charge->date) : '-' }}</td>
                            <td>
                                @if ($charge->charge_module === 'radiology')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Radiology</span>
                                @elseif ($charge->charge_module === 'pathology')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Pathology</span>
                                @elseif ($charge->charge_module === 'ipd')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">IPD</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">OPD</span>
                                @endif
                            </td>
                            <td>{{ $charge->charge_item ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($charge->unit_price, 2) }}</td>
                            <td class="text-center">{{ $charge->quantity }}</td>
                            <td class="text-end">{{ number_format($charge->amount, 2) }}</td>
                            <td class="text-end">{{ number_format($charge->tax, 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($charge->net_amount, 2) }}</td>
                            <td class="text-center">
                                @if ($charge->is_paid)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary"
                                        data-url="{{ route('opd-patients.charges.edit', [$opdPatient->id, $charge->id]) }}"
                                        data-ajax-popup="true" data-title="Edit Charge" data-size="lg" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('opd-patients.charges.destroy', [$opdPatient->id, $charge->id]) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this charge?')">
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
                            <td colspan="11" class="text-center text-muted py-3">No charges available.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($charges->count())
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="8" class="text-end fw-bold">Total:</td>
                            <td class="text-end fw-bold">{{ number_format($charges->sum('net_amount'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
