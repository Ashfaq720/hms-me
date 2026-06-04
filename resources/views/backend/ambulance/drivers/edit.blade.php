@extends('backend.layouts.master')

@section('title', 'Edit Driver')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Edit Driver — {{ $driver->name }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('amb.drivers.show', $driver) }}" class="btn btn-light">View Detail</a>
            <a href="{{ route('amb.drivers.index') }}" class="btn btn-light">Back to List</a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('amb.drivers.update', $driver) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $driver->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">National ID (NID) <span class="text-danger">*</span></label>
                        <input type="text" name="nid"
                            class="form-control @error('nid') is-invalid @enderror"
                            value="{{ old('nid', $driver->nid) }}" required>
                        @error('nid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $driver->phone) }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number"
                            class="form-control @error('license_number') is-invalid @enderror"
                            value="{{ old('license_number', $driver->license_number) }}">
                        @error('license_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">License Type</label>
                        <input type="text" name="license_type"
                            class="form-control @error('license_type') is-invalid @enderror"
                            value="{{ old('license_type', $driver->license_type) }}" placeholder="e.g. Professional">
                        @error('license_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">License Expiry</label>
                        <input type="date" name="license_expiry"
                            class="form-control @error('license_expiry') is-invalid @enderror"
                            value="{{ old('license_expiry', $driver->license_expiry?->format('Y-m-d')) }}">
                        @error('license_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($driver->isLicenseExpired())
                            <div class="form-text text-danger"><i class="bi bi-exclamation-circle"></i> Currently expired — driver cannot be assigned.</div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Shift</label>
                        <select name="shift" class="form-select @error('shift') is-invalid @enderror">
                            <option value="">Not assigned</option>
                            <option value="MORNING" @selected(old('shift', $driver->shift) == 'MORNING')>Morning</option>
                            <option value="EVENING" @selected(old('shift', $driver->shift) == 'EVENING')>Evening</option>
                            <option value="NIGHT"   @selected(old('shift', $driver->shift) == 'NIGHT')>Night</option>
                        </select>
                        @error('shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Availability <span class="text-danger">*</span></label>
                        <select name="availability" class="form-select @error('availability') is-invalid @enderror" required>
                            <option value="AVAILABLE" @selected(old('availability', $driver->availability) == 'AVAILABLE')>Available</option>
                            <option value="ASSIGNED"  @selected(old('availability', $driver->availability) == 'ASSIGNED')>Assigned</option>
                            <option value="OFF_DUTY"  @selected(old('availability', $driver->availability) == 'OFF_DUTY')>Off Duty</option>
                        </select>
                        @error('availability')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="ACTIVE"    @selected(old('status', $driver->status) == 'ACTIVE')>Active</option>
                            <option value="SUSPENDED" @selected(old('status', $driver->status) == 'SUSPENDED')>Suspended</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Driver</button>
                    <a href="{{ route('amb.drivers.show', $driver) }}" class="btn btn-light ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
