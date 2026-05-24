@extends('backend.layouts.master')
@section('title', 'Add Payment - Ipd')

@section('content')
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="card shadow-sm border-0 mb-3 payment-header">
            <div class="card-body py-3">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-white">Add Payment</h4>
                            <div class="header-meta">
                                <span class="meta-item"><i class="bi bi-person"></i>
                                    <strong>{{ $ipdPatient->patient->patient_name ?? 'N/A' }}</strong></span>
                                <span class="meta-item"><i class="bi bi-hash"></i>
                                    <strong>{{ $ipdPatient->patient->mrn ?? '-' }}</strong></span>
                                <span class="meta-item"><i class="bi bi-hospital"></i>
                                    <strong>{{ $ipdPatient->ipd_no ?? '-' }}</strong></span>
                                <span class="meta-item"><i class="bi bi-folder"></i> Case
                                    <strong>{{ $ipdPatient->case_id ?? '-' }}</strong></span>
                                @if ($ipdPatient->doctor)
                                    <span class="meta-item"><i class="bi bi-person-badge"></i>
                                        <strong>{{ $ipdPatient->doctor->name }}</strong></span>
                                @endif
                                @if ($ipdPatient->department)
                                    <span class="meta-item"><i class="bi bi-building"></i>
                                        <strong>{{ $ipdPatient->department->name }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}"
                        class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Patient
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Outstanding summary so the cashier knows what to collect ── --}}
        @if (!empty($totals))
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                        <div class="card-body py-2">
                            <small class="text-primary">Total Charges (Auto)</small>
                            <h5 class="mb-0">৳ {{ number_format($totals['encounter_charges'] + $totals['legacy_charges'], 2) }}</h5>
                            <small class="text-muted">Bills grand: ৳ {{ number_format($totals['bill_grand_total'], 2) }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                        <div class="card-body py-2">
                            <small class="text-success">Paid So Far</small>
                            <h5 class="mb-0">৳ {{ number_format($totals['bill_paid_total'] + $totals['legacy_paid'], 2) }}</h5>
                            <small class="text-muted">Bill ৳ {{ number_format($totals['bill_paid_total'], 2) }} · Legacy ৳ {{ number_format($totals['legacy_paid'], 2) }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm {{ $totals['bill_balance_due'] > 0.01 ? 'bg-danger bg-opacity-10' : 'bg-light' }} h-100">
                        <div class="card-body py-2">
                            <small class="text-{{ $totals['bill_balance_due'] > 0.01 ? 'danger' : 'muted' }}">Bill Balance Due</small>
                            <h5 class="mb-0">৳ {{ number_format($totals['bill_balance_due'], 2) }}</h5>
                            @if ($bills->count())
                                <small class="text-muted">{{ $bills->count() }} bill(s) on encounter</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm {{ $totals['package_outstanding'] > 0.01 ? 'bg-warning bg-opacity-10' : 'bg-light' }} h-100">
                        <div class="card-body py-2">
                            <small class="text-warning">Package Outstanding</small>
                            <h5 class="mb-0">৳ {{ number_format($totals['package_outstanding'], 2) }}</h5>
                            <small class="text-muted">{{ $packageEnrollments->count() }} active enrolment(s)</small>
                        </div>
                    </div>
                </div>
                @if ($totals['suggested_amount'] > 0.01)
                    <div class="col-12">
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-0 py-2">
                            <div>
                                <i class="bi bi-info-circle"></i>
                                <strong>Suggested amount to collect: ৳ {{ number_format($totals['suggested_amount'], 2) }}</strong>
                                <small class="ms-2">(Bill due ৳ {{ number_format($totals['bill_balance_due'], 2) }} + Package outstanding ৳ {{ number_format($totals['package_outstanding'], 2) }})</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="(function(){var el=document.querySelector('input[name=amount]');if(el){el.value={{ $totals['suggested_amount'] }};el.dispatchEvent(new Event('input'));el.dispatchEvent(new Event('change'));}})();">
                                <i class="bi bi-arrow-right"></i> Use this amount
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Per-bill breakdown so cashier can see WHY they're collecting --}}
            @if ($bills->count())
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0"><i class="bi bi-receipt"></i> Bills on this encounter</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr><th>Bill</th><th>Status</th><th class="text-end">Grand</th><th class="text-end">Paid</th><th class="text-end">Due</th><th class="text-center">Items</th><th class="text-center">Payments</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($bills as $b)
                                    <tr>
                                        <td><strong>{{ $b->bill_no }}</strong></td>
                                        <td><span class="badge bg-{{ $b->status === 'paid' || $b->status === 'final' ? 'success' : ($b->status === 'partially_paid' ? 'warning text-dark' : 'secondary') }}">{{ ucfirst($b->status) }}</span></td>
                                        <td class="text-end">৳ {{ number_format($b->grand_total, 2) }}</td>
                                        <td class="text-end text-success">৳ {{ number_format($b->paid_total, 2) }}</td>
                                        <td class="text-end {{ $b->balance_due > 0.01 ? 'text-danger' : '' }}">৳ {{ number_format($b->balance_due, 2) }}</td>
                                        <td class="text-center">{{ $b->items->count() }}</td>
                                        <td class="text-center">{{ $b->payments->count() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

        <form action="{{ route('ipd-patients.payments.store', $ipdPatient->id) }}" method="POST"
            enctype="multipart/form-data" id="paymentForm">
            @csrf

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-cash-coin text-primary"></i> Payment Details
                    </h6>
                    <p class="text-muted small mb-0">Fill in the payment information below</p>
                </div>
                <div class="card-body">
                    @include('ipd_patients.payments._fields')
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2 pb-3">
                    <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}"
                        class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save"></i> Save Payment
                    </button>
                </div>
            </div>
        </form>
    </div>

    @include('ipd_patients.payments._styles')
@endsection
