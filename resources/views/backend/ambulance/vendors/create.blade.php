@extends('backend.layouts.master')

@section('title', 'Add Vendor')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Add Ambulance Vendor</h1>
        <a href="{{ route('amb.vendors.index') }}" class="btn btn-light">Back to List</a>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('amb.vendors.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Vendor Code <span class="text-danger">*</span></label>
                        <input type="text" name="vendor_code" class="form-control @error('vendor_code') is-invalid @enderror"
                            value="{{ old('vendor_code') }}" placeholder="VEN-001" required>
                        @error('vendor_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror"
                            value="{{ old('vendor_name') }}" required>
                        @error('vendor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Ambulance Type <span class="text-danger">*</span></label>
                        <select name="ambulance_type" class="form-select @error('ambulance_type') is-invalid @enderror" required>
                            @foreach(['BASIC','EMERGENCY','ALS','ICU','NEONATAL','MIXED'] as $type)
                                <option value="{{ $type }}" @selected(old('ambulance_type') == $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('ambulance_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Rate Contract Type <span class="text-danger">*</span></label>
                        <select name="rate_contract_type" class="form-select @error('rate_contract_type') is-invalid @enderror" required>
                            <option value="PER_KM" @selected(old('rate_contract_type') == 'PER_KM')>Per KM</option>
                            <option value="FIXED" @selected(old('rate_contract_type') == 'FIXED')>Fixed</option>
                            <option value="PACKAGE" @selected(old('rate_contract_type') == 'PACKAGE')>Package</option>
                        </select>
                        @error('rate_contract_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Base Rate (BDT)</label>
                        <input type="number" step="0.01" name="base_rate" class="form-control" value="{{ old('base_rate') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">SLA Response (minutes) <span class="text-danger">*</span></label>
                        <input type="number" name="sla_response_minutes" class="form-control @error('sla_response_minutes') is-invalid @enderror"
                            value="{{ old('sla_response_minutes', 20) }}" required>
                        @error('sla_response_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                {{ old('is_active', '1') ? 'checked' : '' }} id="is_active">
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Vendor</button>
                    <a href="{{ route('amb.vendors.index') }}" class="btn btn-light ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
