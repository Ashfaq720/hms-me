@props(['opdPatient'])

@php
    $charges = $opdPatient->charges;

    // Encounter-layer auto-posted charges (modern flow)
    $encounterPostings = collect();
    $bills = collect();
    if ($opdPatient->encounter_id) {
        $encounterPostings = \App\Models\ServiceCharge\ServiceChargePosting::with('catalog')
            ->where('encounter_id', $opdPatient->encounter_id)
            ->where('status', 'posted')
            ->latest('id')
            ->get();
        $bills = \App\Models\Billing\Bill::where('encounter_id', $opdPatient->encounter_id)
            ->latest('id')
            ->get();
    }

    $totalLegacy        = (float) $charges->sum('net_amount');
    $totalEncounterPost = (float) $encounterPostings->sum('net_amount');
    $totalBillGrand     = (float) $bills->sum('grand_total');
    $totalBillPaid      = (float) $bills->sum('paid_total');
    $totalBillDue       = (float) $bills->sum('balance_due');
@endphp

{{-- Summary strip: 4 KPI tiles --}}
<div class="row g-2 mb-2">
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-2 px-3">
                <small class="text-warning"><i class="bi bi-receipt"></i> Legacy Charges</small>
                <h5 class="mb-0">৳ {{ number_format($totalLegacy, 0) }}</h5>
                <small class="text-muted">{{ $charges->count() }} entries</small>
            </div>
        </div>
    </div>
    <div class="col-md col-6">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
            <div class="card-body py-2 px-3">
                <small class="text-info"><i class="bi bi-plus-circle"></i> Auto-posted</small>
                <h5 class="mb-0">৳ {{ number_format($totalEncounterPost, 0) }}</h5>
                <small class="text-muted">{{ $encounterPostings->count() }} postings</small>
            </div>
        </div>
    </div>
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
        <div class="card border-0 shadow-sm bg-{{ $totalBillDue > 0 ? 'danger' : 'success' }} bg-opacity-10">
            <div class="card-body py-2 px-3">
                <small class="text-{{ $totalBillDue > 0 ? 'danger' : 'success' }}"><i class="bi bi-currency-exchange"></i> Balance Due</small>
                <h5 class="mb-0">৳ {{ number_format($totalBillDue, 0) }}</h5>
                <small class="text-muted">paid: ৳{{ number_format($totalBillPaid, 0) }}</small>
            </div>
        </div>
    </div>
</div>

{{-- Bills (modern encounter-layer billing) --}}
@if ($bills->isNotEmpty())
<div class="card border-0 shadow-sm rounded-3 mb-2">
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
                        <td class="text-end">
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

{{-- Encounter-layer postings --}}
@if ($encounterPostings->isNotEmpty())
<div class="card border-0 shadow-sm rounded-3 mb-2">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-info"></i>Auto-Posted Charges (encounter layer)</h6>
        <span class="badge bg-info">{{ $encounterPostings->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Service</th><th>Trigger</th>
                    <th class="text-center">Qty</th><th class="text-end">Unit</th><th class="text-end">Net</th>
                    <th>Posted</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($encounterPostings as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ optional($p->catalog)->name ?? $p->reason }}</strong>
                            @if(optional($p->catalog)->code)<br><small class="text-muted">{{ $p->catalog->code }}</small>@endif
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $p->trigger_event }}</span></td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($p->quantity, 2), '0'), '.') }}</td>
                        <td class="text-end">৳ {{ number_format($p->unit_price, 0) }}</td>
                        <td class="text-end fw-semibold">৳ {{ number_format($p->net_amount, 0) }}</td>
                        <td><small>{{ \Carbon\Carbon::parse($p->created_at)->diffForHumans() }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Legacy patient_charges (kept for backward-compat) --}}
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-receipt-cutoff me-2 text-warning"></i>Manual / Legacy Charges
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
