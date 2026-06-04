@extends('portal.layout')
@section('title', 'My Profile')

@section('content')
<h4 class="mb-3"><i class="bi bi-person-circle"></i> My Profile</h4>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card portal-card">
            <div class="card-header bg-white"><strong>Profile Details</strong></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th width="40%">Patient Name</th><td>{{ $patient->patient_name }}</td></tr>
                    <tr><th>MRN</th><td><code>{{ $patient->mrn ?? '—' }}</code></td></tr>
                    <tr><th>Health Card</th><td><code>{{ $patient->health_card_no ?? '—' }}</code></td></tr>
                    <tr><th>Mobile</th><td>{{ $patient->mobileno ?? '—' }}</td></tr>
                    <tr><th>Email</th><td>{{ $patient->email ?? '—' }}</td></tr>
                    <tr><th>Date of Birth</th><td>{{ $patient->dob ?? '—' }}</td></tr>
                    <tr><th>Gender</th><td>{{ $patient->gender ?? '—' }}</td></tr>
                    <tr><th>Blood Group</th><td><strong>{{ $patient->blood_group ?? '—' }}</strong></td></tr>
                    <tr><th>Address</th><td>{{ $patient->address ?? '—' }}</td></tr>
                    <tr><th>Allergies</th><td class="{{ $patient->known_allergies ? 'text-danger' : '' }}">{{ $patient->known_allergies ?: 'None reported' }}</td></tr>
                </table>
                <small class="text-muted">To update personal details, please visit the reception desk.</small>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card portal-card">
            <div class="card-header bg-white"><strong>Change Password</strong></div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <form action="{{ route('portal.profile.password') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password (min 6 chars)</label>
                        <input type="password" name="new_password" class="form-control" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" minlength="6" required>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-shield-lock"></i> Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
