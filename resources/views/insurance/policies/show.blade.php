@extends('backend.layouts.master')
@section('title', 'Policy ' . $policy->policy_no)
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-shield-check"></i> {{ $policy->policy_no }}</h4>
        <a href="{{ route('insurance.policies.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Payer:</strong> {{ optional($policy->payer)->name }}</div>
                <div class="col-md-4"><strong>Patient:</strong> {{ optional($policy->patient)->patient_name }} ({{ optional($policy->patient)->mrn }})</div>
                <div class="col-md-4"><strong>Plan:</strong> {{ $policy->plan_name }}</div>
                <div class="col-md-3"><strong>Valid From:</strong> {{ $policy->valid_from?->toDateString() }}</div>
                <div class="col-md-3"><strong>Valid To:</strong> {{ $policy->valid_to?->toDateString() }}</div>
                <div class="col-md-3"><strong>Coverage:</strong> ৳ {{ number_format((float) $policy->coverage_limit, 2) }}</div>
                <div class="col-md-3"><strong>Copay:</strong> {{ $policy->copay_percent }}%</div>
                <div class="col-md-3"><strong>Deductible:</strong> ৳ {{ number_format((float) $policy->deductible, 2) }}</div>
                <div class="col-md-3"><strong>Subscriber:</strong> {{ $policy->subscriber_name ?? '—' }}</div>
                <div class="col-md-3"><strong>Relationship:</strong> {{ $policy->relationship ?? '—' }}</div>
                <div class="col-md-3"><strong>Status:</strong> <span class="badge bg-{{ $policy->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($policy->status) }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
