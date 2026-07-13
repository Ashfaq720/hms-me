@extends('backend.layouts.master')

@section('title', 'Edit Paramedic')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Edit Paramedic — {{ $paramedic->name }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('amb.paramedics.show', $paramedic) }}" class="btn btn-light">View Detail</a>
            <a href="{{ route('amb.paramedics.index') }}" class="btn btn-light">Back to List</a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('amb.paramedics.update', $paramedic) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $paramedic->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">National ID (NID) <span class="text-danger">*</span></label>
                        <input type="text" name="nid"
                            class="form-control @error('nid') is-invalid @enderror"
                            value="{{ old('nid', $paramedic->nid) }}" required>
                        @error('nid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $paramedic->phone) }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Certification <span class="text-danger">*</span></label>
                        <select name="certification" class="form-select @error('certification') is-invalid @enderror" required>
                            <option value="BLS"  @selected(old('certification', $paramedic->certification) == 'BLS')>Basic Life Support (BLS)</option>
                            <option value="ACLS" @selected(old('certification', $paramedic->certification) == 'ACLS')>Advanced Cardiac Life Support (ACLS)</option>
                        </select>
                        @error('certification')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Skill Level <span class="text-danger">*</span></label>
                        <select name="skill_level" class="form-select @error('skill_level') is-invalid @enderror" required>
                            <option value="BASIC"    @selected(old('skill_level', $paramedic->skill_level) == 'BASIC')>Basic</option>
                            <option value="ADVANCED" @selected(old('skill_level', $paramedic->skill_level) == 'ADVANCED')>Advanced</option>
                        </select>
                        @error('skill_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Certification Expiry</label>
                        <input type="date" name="cert_expiry"
                            class="form-control @error('cert_expiry') is-invalid @enderror"
                            value="{{ old('cert_expiry', $paramedic->cert_expiry?->format('Y-m-d')) }}">
                        @error('cert_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($paramedic->isCertExpired())
                            <div class="form-text text-danger"><i class="bi bi-exclamation-circle"></i> Currently expired — paramedic cannot be assigned.</div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Shift</label>
                        <select name="shift" class="form-select @error('shift') is-invalid @enderror">
                            <option value="">Not assigned</option>
                            <option value="MORNING" @selected(old('shift', $paramedic->shift) == 'MORNING')>Morning</option>
                            <option value="EVENING" @selected(old('shift', $paramedic->shift) == 'EVENING')>Evening</option>
                            <option value="NIGHT"   @selected(old('shift', $paramedic->shift) == 'NIGHT')>Night</option>
                        </select>
                        @error('shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Availability <span class="text-danger">*</span></label>
                        <select name="availability" class="form-select @error('availability') is-invalid @enderror" required>
                            <option value="AVAILABLE" @selected(old('availability', $paramedic->availability) == 'AVAILABLE')>Available</option>
                            <option value="ASSIGNED"  @selected(old('availability', $paramedic->availability) == 'ASSIGNED')>Assigned</option>
                            <option value="OFF_DUTY"  @selected(old('availability', $paramedic->availability) == 'OFF_DUTY')>Off Duty</option>
                        </select>
                        @error('availability')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="ACTIVE"    @selected(old('status', $paramedic->status) == 'ACTIVE')>Active</option>
                            <option value="SUSPENDED" @selected(old('status', $paramedic->status) == 'SUSPENDED')>Suspended</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Paramedic</button>
                    <a href="{{ route('amb.paramedics.show', $paramedic) }}" class="btn btn-light ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
