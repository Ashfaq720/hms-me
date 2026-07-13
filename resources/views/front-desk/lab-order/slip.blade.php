@extends('backend.layouts.master')

@section('title', 'Lab Order Slip')

@section('content')
<div class="container-fluid">

    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between no-print">
        <h1 class="app-page-title">Lab Order Slip</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('front_desk.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-house me-1"></i> Front Desk
            </a>
            <button class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3 no-print">{{ session('success') }}</div>
    @endif

    @if($orders->isEmpty())
        <div class="alert alert-warning mt-4">No lab orders found for this case.</div>
    @else

    {{-- Print area: one slip per order --}}
    @foreach($orders as $order)
    <div class="slip-card card mt-4 mx-auto" style="max-width:720px;">
        <div class="card-body p-4">

            {{-- Header --}}
            <div class="d-flex align-items-start justify-content-between border-bottom pb-3 mb-3">
                <div>
                    <div class="fw-bold fs-5">
                        {{ config('app.name', 'Hospital Management System') }}
                    </div>
                    <div class="text-muted small">Lab Order Slip</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold fs-6">{{ $order->order_number }}</div>
                    <div class="small text-muted">{{ $order->datetime?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A') }}</div>
                    <span class="badge {{ $order->type === 'pathology' ? 'bg-danger' : 'bg-primary' }} mt-1">
                        {{ strtoupper($order->type) }}
                    </span>
                </div>
            </div>

            {{-- Patient Info --}}
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="small text-muted">Patient Name</div>
                    <div class="fw-semibold">{{ optional($order->patient)->patient_name ?? '-' }}</div>
                </div>
                <div class="col-6">
                    <div class="small text-muted">MRN</div>
                    <div class="fw-semibold">{{ optional($order->patient)->mrn ?? '-' }}</div>
                </div>
                <div class="col-6">
                    <div class="small text-muted">Contact</div>
                    <div>{{ optional($order->patient)->mobileno ?? '-' }}</div>
                </div>
                <div class="col-6">
                    <div class="small text-muted">Case No</div>
                    <div>{{ $order->case_id }}</div>
                </div>
                @if($order->doctor)
                <div class="col-6">
                    <div class="small text-muted">Referring Doctor</div>
                    <div>{{ $order->doctor->name }}</div>
                </div>
                @endif
                @if($order->priority)
                <div class="col-6">
                    <div class="small text-muted">Priority</div>
                    <div>
                        @php
                            $pClass = match(strtolower($order->priority ?? '')) {
                                'urgent' => 'bg-danger',
                                'stat'   => 'bg-warning text-dark',
                                default  => 'bg-success',
                            };
                        @endphp
                        <span class="badge {{ $pClass }}">{{ $order->priority }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Tests Table --}}
            <table class="table table-bordered table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Test Name</th>
                        <th>Category</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->requests as $j => $req)
                    <tr>
                        <td>{{ $j + 1 }}</td>
                        <td class="fw-semibold">{{ optional($req->labInvestigation)->name ?? '-' }}</td>
                        <td>{{ optional($req->labInvestigationCategory)->name ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $req->status ?? 'Pending' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted">No tests added</td></tr>
                    @endforelse
                </tbody>
            </table>

            @if($order->remarks)
            <div class="mt-3 small text-muted">
                <strong>Remarks:</strong> {{ $order->remarks }}
            </div>
            @endif

            {{-- Footer --}}
            <div class="d-flex justify-content-between align-items-end mt-4 pt-3 border-top">
                <div class="small text-muted">
                    Generated by: {{ optional($order->generatedBy)->name ?? 'Front Desk' }}<br>
                    Date: {{ now()->format('d M Y, h:i A') }}
                </div>
                <div class="text-end small">
                    <div class="border-top border-dark mt-4 pt-1" style="width:160px;">
                        Authorized Signature
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Page break between slips when printing --}}
    @if(!$loop->last)
        <div class="slip-break"></div>
    @endif

    @endforeach
    @endif

</div>
@endsection

@push('styles')
<style>
    @media print {
        .no-print, .app-page-head, nav, aside, .sidebar, header, footer { display: none !important; }
        body { background: #fff !important; }
        .slip-card { box-shadow: none !important; border: 1px solid #ccc !important; max-width: 100% !important; }
        .slip-break { page-break-after: always; }
    }
</style>
@endpush
