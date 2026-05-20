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
