@extends('backend.layouts.master')

@section('title', 'Add Contract')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Add Contract — {{ $vendor->vendor_name }}</h1>
        <a href="{{ route('amb.vendors.show', $vendor) }}" class="btn btn-light">Back</a>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('amb.vendors.contract.store', $vendor) }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Contract Reference</label>
                        <input type="text" name="contract_ref" class="form-control @error('contract_ref') is-invalid @enderror"
                            value="{{ old('contract_ref') }}" placeholder="CONT-2026-001">
                        @error('contract_ref')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Rate Type <span class="text-danger">*</span></label>
                        <select name="rate_type" class="form-select" required>
                            <option value="PER_KM" @selected(old('rate_type') == 'PER_KM')>Per KM</option>
                            <option value="FIXED" @selected(old('rate_type') == 'FIXED')>Fixed</option>
                            <option value="PACKAGE" @selected(old('rate_type') == 'PACKAGE')>Package</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Rate Amount (BDT) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="rate_amount" class="form-control @error('rate_amount') is-invalid @enderror"
                            value="{{ old('rate_amount') }}" required>
                        @error('rate_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Per KM Rate (BDT)</label>
                        <input type="number" step="0.01" name="per_km_rate" class="form-control" value="{{ old('per_km_rate') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">SLA Response (minutes) <span class="text-danger">*</span></label>
                        <input type="number" name="sla_response_minutes" class="form-control"
                            value="{{ old('sla_response_minutes', $vendor->sla_response_minutes) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Contract Start <span class="text-danger">*</span></label>
                        <input type="date" name="contract_start" class="form-control @error('contract_start') is-invalid @enderror"
                            value="{{ old('contract_start') }}" required>
                        @error('contract_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Contract End <span class="text-danger">*</span></label>
                        <input type="date" name="contract_end" class="form-control @error('contract_end') is-invalid @enderror"
                            value="{{ old('contract_end') }}" required>
                        @error('contract_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Terms / Notes</label>
                        <textarea name="terms" class="form-control" rows="3">{{ old('terms') }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Contract</button>
                    <a href="{{ route('amb.vendors.show', $vendor) }}" class="btn btn-light ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
