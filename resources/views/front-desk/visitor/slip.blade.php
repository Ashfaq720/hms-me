@extends('backend.layouts.master')

@section('title', 'Visitor Pass — ' . $visitor->visit_code)

@section('content')
<div class="container-fluid">

    {{-- Toolbar (hidden on print) --}}
    <div class="d-flex align-items-center justify-content-between gap-2 mb-4 no-print">
        <h1 class="app-page-title mb-0">Visitor Pass</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('front_desk.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-house me-1"></i> Front Desk
            </a>
            <button class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    {{-- Slip card --}}
    <div class="slip-wrap mx-auto" style="max-width:480px;">
        <div class="card border shadow-sm" id="visitorSlip">
            <div class="card-body p-4">

                {{-- Hospital header --}}
                <div class="text-center border-bottom pb-3 mb-3">
                    <div class="fw-bold fs-5">{{ config('app.name', 'Hospital Management System') }}</div>
                    <div class="text-muted small">Visitor Pass</div>
                </div>

                {{-- QR-style visit code badge --}}
                <div class="text-center mb-4">
                    <div class="d-inline-block border border-2 border-warning rounded-3 px-4 py-2">
                        <div class="text-muted small mb-1">Visit Code</div>
                        <div class="fw-bold fs-3 font-monospace text-warning lh-1">{{ $visitor->visit_code }}</div>
                    </div>
                </div>

                {{-- Visitor details --}}
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="small text-muted">Visitor Name</div>
                        <div class="fw-semibold">{{ $visitor->visitor_name }}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted">Contact No.</div>
                        <div class="fw-semibold">{{ $visitor->contact_no }}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted">No. of Visitors</div>
                        <div class="fw-semibold">{{ $visitor->visitor_qty }}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted">Patient Type</div>
                        @php
                            $typeColor = match($visitor->patient_type) {
                                'OPD' => 'primary',
                                'Ipd' => 'success',
                                'ER'  => 'danger',
                                default => 'secondary',
                            };
                            $typeLabel = match($visitor->patient_type) {
                                'Ipd' => 'IPD',
                                default => $visitor->patient_type,
                            };
                        @endphp
                        <span class="badge text-bg-{{ $typeColor }}-subtle border border-{{ $typeColor }}-subtle text-{{ $typeColor }} rounded-pill">
                            {{ $typeLabel }}
                        </span>
                    </div>
                </div>

                {{-- Divider --}}
                <hr class="my-3">

                {{-- Patient details --}}
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="small text-muted">Patient Being Visited</div>
                        <div class="fw-semibold fs-6">{{ $visitor->patient_name ?? '—' }}</div>
                        @if($visitor->patient)
                            <div class="text-muted small font-monospace">MRN: {{ $visitor->patient->mrn ?? '—' }}</div>
                        @endif
                    </div>
                    <div class="col-6">
                        <div class="small text-muted">Department</div>
                        <div class="fw-semibold">{{ $visitor->department?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted">Visit Date</div>
                        <div class="fw-semibold">
                            {{ \Carbon\Carbon::parse($visitor->visit_date)->format('d M Y') }}
                        </div>
                    </div>
                    @if($visitor->visit_time)
                    <div class="col-6">
                        <div class="small text-muted">Visit Time</div>
                        <div class="fw-semibold">
                            {{ \Carbon\Carbon::parse($visitor->visit_time)->format('h:i A') }}
                        </div>
                    </div>
                    @endif
                    @if($visitor->remarks)
                    <div class="col-12">
                        <div class="small text-muted">Remarks</div>
                        <div class="fst-italic small">{{ $visitor->remarks }}</div>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="border-top pt-3 mt-2 d-flex justify-content-between align-items-center">
                    <div class="text-muted" style="font-size:11px;">
                        Issued: {{ $visitor->created_at?->format('d M Y, h:i A') }}
                    </div>
                    <div class="text-muted" style="font-size:11px;">
                        <i class="bi bi-shield-check text-success me-1"></i>Authorised
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<style>
@media print {
    .no-print { display: none !important; }
    .app-page-title { display: none !important; }
    body { background: #fff !important; }
    .card { border: 1px solid #ccc !important; box-shadow: none !important; }
    .slip-wrap { max-width: 100% !important; }
}
</style>
@endsection
