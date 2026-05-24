@extends('backend.layouts.master')
@section('title', 'Package · ' . $package->name)
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-box-seam"></i> {{ $package->name }}</h4>
        <div>
            <a href="{{ route('packages.edit', $package->id) }}" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('packages.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to list
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body">
                    <small class="text-primary">Type / Category</small>
                    <h5 class="mb-0">{{ $package->package_type ?: '—' }}</h5>
                    <small class="text-muted">{{ $package->category ?: '' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body">
                    <small class="text-success">Total Amount</small>
                    <h5 class="mb-0">৳ {{ number_format((float) $package->total_amount, 2) }}</h5>
                    @if ($package->discount > 0)
                        <small class="text-muted">{{ $package->discount }}% discount</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                <div class="card-body">
                    <small class="text-info">Validity</small>
                    <h5 class="mb-0">{{ $package->validity_days ?? '—' }} days</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                <div class="card-body">
                    <small class="text-warning">Enrolments</small>
                    <h5 class="mb-0">{{ $package->enrollments->count() }}</h5>
                    <small class="text-muted">৳ {{ number_format((float) $package->totalRevenue(), 2) }} revenue</small>
                </div>
            </div>
        </div>
    </div>

    @if ($package->description)
        <div class="alert alert-light border mb-3">{{ $package->description }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-list-check text-success"></i> Included Charges / Services
                <small class="text-muted">({{ $package->services->count() }} items)</small>
            </h6>
            @php
                $totalIncluded = $package->services->where('is_included', true)->sum('amount');
                $totalExcluded = $package->services->where('is_included', false)->count();
            @endphp
            <div>
                <span class="badge bg-success bg-opacity-15 text-success">Included: ৳ {{ number_format($totalIncluded, 0) }}</span>
                @if ($totalExcluded > 0)
                    <span class="badge bg-secondary bg-opacity-15 text-secondary">Excluded items: {{ $totalExcluded }}</span>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Charge / Service</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Included?</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($package->services as $ps)
                    @php
                        $name = optional($ps->charge)->charge_name ?? optional($ps->catalog)->name ?? '—';
                        $code = $ps->charge ? 'CHG-' . str_pad($ps->charge->id, 4, '0', STR_PAD_LEFT) : (optional($ps->catalog)->code ?? '—');
                        $type = optional(optional($ps->charge)->chargeType)->name ?? optional($ps->catalog)->service_type ?? '—';
                    @endphp
                    <tr class="{{ ! $ps->is_included ? 'text-muted text-decoration-line-through' : '' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $name }}</strong></td>
                        <td><code class="small">{{ $code }}</code></td>
                        <td><small class="text-muted">{{ $type }}</small></td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($ps->quantity, 2), '0'), '.') }}</td>
                        <td class="text-end">৳ {{ number_format((float) $ps->rate, 0) }}</td>
                        <td class="text-end"><strong>৳ {{ number_format((float) $ps->amount, 0) }}</strong></td>
                        <td class="text-center">
                            @if ($ps->is_included)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                                <i class="bi bi-x-circle text-muted"></i>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-5"></i><br>No services attached yet — <a href="{{ route('packages.edit', $package->id) }}">add them from /admin/charges →</a>
                    </td></tr>
                @endforelse
                </tbody>
                @if ($package->services->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end">Sum of included items</th>
                            <th class="text-end"><strong class="text-success">৳ {{ number_format($totalIncluded, 0) }}</strong></th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if ($package->enrollments->count())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Enrolments ({{ $package->enrollments->count() }})</h6></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>#</th><th>Enrolment No</th><th>Patient</th><th>Status</th><th class="text-end">Agreed</th><th class="text-end">Paid</th><th>Start</th><th>End</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($package->enrollments as $e)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $e->enrollment_no }}</td>
                            <td>{{ optional($e->patient)->patient_name ?? '—' }}</td>
                            <td><span class="badge bg-{{ $e->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($e->status) }}</span></td>
                            <td class="text-end">৳ {{ number_format((float) $e->agreed_price, 2) }}</td>
                            <td class="text-end">৳ {{ number_format((float) $e->paid_amount, 2) }}</td>
                            <td>{{ $e->start_date?->toDateString() }}</td>
                            <td>{{ $e->end_date?->toDateString() ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
