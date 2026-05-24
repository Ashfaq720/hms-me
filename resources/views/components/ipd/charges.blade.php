{{-- KPI tiles: Bill summary --}}
<div class="row g-2 mb-3">
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-2 px-3">
                <small class="text-warning"><i class="bi bi-receipt"></i> Total Charges</small>
                <h5 class="mb-0">৳ {{ number_format($chargeTotal, 0) }}</h5>
                <small class="text-muted">{{ $charges->count() }} entries</small>
            </div>
        </div>
    </div>
    @if ($packageCoveredTotal > 0)
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <small class="text-success"><i class="bi bi-box-seam"></i> Package-covered</small>
                <h5 class="mb-0">৳ {{ number_format($packageCoveredTotal, 0) }}</h5>
                <small class="text-muted">{{ $packageEnrollments->count() }} active pkg</small>
            </div>
        </div>
    </div>
    @endif
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-2 px-3">
                <small class="text-primary"><i class="bi bi-cash-stack"></i> Bill Grand</small>
                <h5 class="mb-0">৳ {{ number_format($totalBillGrand, 0) }}</h5>
                <small class="text-muted">{{ $bills->count() }} bill(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <small class="text-success"><i class="bi bi-check-circle"></i> Paid</small>
                <h5 class="mb-0">৳ {{ number_format($totalBillPaid, 0) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm bg-{{ $totalBillDue > 0 ? 'danger' : 'success' }} bg-opacity-10">
            <div class="card-body py-2 px-3">
                <small class="text-{{ $totalBillDue > 0 ? 'danger' : 'success' }}"><i class="bi bi-currency-exchange"></i> Balance Due</small>
                <h5 class="mb-0">৳ {{ number_format($totalBillDue, 0) }}</h5>
            </div>
        </div>
    </div>
</div>

{{-- Assembled bills --}}
@if ($bills->isNotEmpty())
<div class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Assembled Bills</h6>
        <span class="badge bg-primary">{{ $bills->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Bill No</th><th>Date</th><th>Type</th>
                    <th class="text-end">Grand</th><th class="text-end">Paid</th><th class="text-end">Due</th>
                    <th>Status</th><th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bills as $b)
                    @php $cls = ['paid'=>'success','final'=>'success','partially_paid'=>'warning text-dark','draft'=>'secondary','cancelled'=>'danger'][$b->status] ?? 'secondary'; @endphp
                    <tr>
                        <td><strong>{{ $b->bill_no }}</strong></td>
                        <td><small>{{ \Carbon\Carbon::parse($b->bill_date)->format('Y-m-d H:i') }}</small></td>
                        <td><span class="badge bg-primary bg-opacity-15 text-primary">{{ strtoupper($b->bill_type) }}</span></td>
                        <td class="text-end">৳ {{ number_format($b->grand_total, 0) }}</td>
                        <td class="text-end text-success">৳ {{ number_format($b->paid_total, 0) }}</td>
                        <td class="text-end {{ $b->balance_due > 0.01 ? 'text-danger fw-bold' : '' }}">৳ {{ number_format($b->balance_due, 0) }}</td>
                        <td><span class="badge bg-{{ $cls }}">{{ ucfirst(str_replace('_', ' ', $b->status)) }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('billing.bills.show', $b->id) }}" class="btn btn-sm btn-outline-primary" title="View Bill"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('billing.category.pdf', $b->id) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h6 class="mb-0">Patient Charges (merged: legacy + auto-posted)</h6>
            <small class="text-muted">
                {{ $charges->count() }} entries · Total ৳ {{ number_format($chargeTotal, 2) }}
                @if ($packageCoveredTotal > 0)
                    · <span class="badge bg-success bg-opacity-15 text-success">Package-covered ৳ {{ number_format($packageCoveredTotal, 2) }}</span>
                @endif
            </small>
        </div>

        <div class="d-flex gap-2">
            @if ($packageEnrollments->count())
                <span class="badge bg-primary bg-opacity-10 text-primary p-2">
                    <i class="bi bi-box-seam"></i>
                    {{ $packageEnrollments->count() }} active package(s)
                </span>
            @endif
            <a href="{{ route('ipd-patients.charges.create', $iPDPatient->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Add Charge
            </a>
        </div>
    </div>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>SN</th>
                <th scope="col">Date</th>
                <th scope="col">Charge Item</th>
                <th scope="col">Unit Price</th>
                <th scope="col">Qty</th>
                <th scope="col">Amount</th>
                <th scope="col">Vat</th>
                <th scope="col">Tax</th>
                <th scope="col">Net Amount</th>
                <th scope="col">Source</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($charges as $charge)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ format_datetime($charge->date) ?? 'N/A' }}</td>
                    <td>
                        @if ($charge->charge_id)
                            {{ $charge->charge->charge_name ?? 'N/A' }}
                        @else
                            {{ $charge->charge_item ?? 'N/A' }}
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($charge->unit_price, 2) }}</td>
                    <td class="text-center">{{ $charge->quantity }}</td>
                    <td class="text-end">{{ number_format($charge->amount, 2) }}</td>
                    <td class="text-end">{{ number_format($charge->vat, 2) }}</td>
                    <td class="text-end">{{ number_format($charge->tax, 2) }}</td>
                    <td class="text-end fw-semibold">
                        {{ number_format($charge->net_amount, 2) }}
                        @if (!empty($charge->is_package_included))
                            <span class="badge bg-success bg-opacity-15 text-success ms-1" title="Covered by an active package">PKG</span>
                        @endif
                    </td>

                    <td class="text-center">
                        @if (!empty($charge->is_auto))
                            <span class="badge bg-info bg-opacity-15 text-info" title="{{ $charge->source ?? '' }}">
                                <i class="bi bi-cpu"></i> Auto
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-15 text-warning">
                                <i class="bi bi-pencil"></i> Manual
                            </span>
                        @endif
                    </td>

                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            @if (!empty($charge->is_auto))
                                <span class="text-muted small" title="Auto-posted by observer — adjust via the source module"><i class="bi bi-lock"></i> Auto</span>
                            @else
                                @if ($charge->charge_id)
                                    <a href="{{ route('ipd-patients.charges.edit', [$iPDPatient->id, $charge->id]) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                <form action="{{ route('ipd-patients.charges.destroy', [$iPDPatient->id, $charge->id]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this charge?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">No charges available.</td>
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
                @if ($packageCoveredTotal > 0)
                    <tr>
                        <td colspan="8" class="text-end text-success small">Package-included subtotal (deducted from final bill):</td>
                        <td class="text-end fw-semibold text-success">- {{ number_format($packageCoveredTotal, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="8" class="text-end fw-bold">Net Billable:</td>
                        <td class="text-end fw-bold">{{ number_format(max($chargeTotal - $packageCoveredTotal, 0), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                @endif
            </tfoot>
        @endif
    </table>
</div>
