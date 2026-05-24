@extends('backend.layouts.master')

@section('title', 'Vendor Detail')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">{{ $vendor->vendor_name }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('amb.vendors.contract.create', $vendor) }}" class="btn btn-success">
                <i class="fi fi-rr-plus me-1"></i> Add Contract
            </a>
            <a href="{{ route('amb.vendors.edit', $vendor) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('amb.vendors.index') }}" class="btn btn-light">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="row mt-4 g-4">
        {{-- Vendor Info --}}
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header"><strong>Vendor Information</strong></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>Code</th><td>{{ $vendor->vendor_code }}</td></tr>
                        <tr><th>Contact Person</th><td>{{ $vendor->contact_person ?? '—' }}</td></tr>
                        <tr><th>Phone</th><td>{{ $vendor->phone ?? '—' }}</td></tr>
                        <tr><th>Email</th><td>{{ $vendor->email ?? '—' }}</td></tr>
                        <tr><th>Ambulance Type</th><td>{{ $vendor->ambulance_type }}</td></tr>
                        <tr><th>Rate Type</th><td>{{ $vendor->rate_contract_type }}</td></tr>
                        <tr><th>Base Rate</th><td>{{ $vendor->base_rate ? number_format($vendor->base_rate, 2) . ' BDT' : '—' }}</td></tr>
                        <tr><th>SLA Response</th><td>{{ $vendor->sla_response_minutes }} min</td></tr>
                        <tr><th>Performance Score</th><td>{{ $vendor->performance_score ?? '—' }} / 5</td></tr>
                        <tr><th>Status</th>
                            <td>
                                @if($vendor->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @if($vendor->notes)
                        <tr><th>Notes</th><td>{{ $vendor->notes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Contracts --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><strong>Rate Contracts</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ref</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>SLA</th>
                                <th>Period</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendor->contracts as $contract)
                            <tr>
                                <td>{{ $contract->contract_ref ?? '—' }}</td>
                                <td>{{ $contract->rate_type }}</td>
                                <td>{{ number_format($contract->rate_amount, 2) }}</td>
                                <td>{{ $contract->sla_response_minutes }} min</td>
                                <td>{{ $contract->contract_start->format('d M Y') }} – {{ $contract->contract_end->format('d M Y') }}</td>
                                <td>
                                    @if($contract->status === 'ACTIVE')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($contract->status === 'EXPIRED')
                                        <span class="badge bg-secondary">Expired</span>
                                    @else
                                        <span class="badge bg-danger">Terminated</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No contracts yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
