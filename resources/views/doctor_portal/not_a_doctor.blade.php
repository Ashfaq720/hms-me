@extends('backend.layouts.master')
@section('title', 'Doctor Portal')

@section('content')
<div class="container py-5">
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-person-x display-1 text-muted"></i>
            <h4 class="mt-3">Doctor Portal</h4>
            <p class="text-muted">
                Your user account isn't linked to a doctor record. The Doctor Portal is for
                consulting physicians to see their own OPD/IPD patients.
            </p>
            <p class="text-muted small">
                If you ARE a doctor, ask the administrator to set <code>doctors.user_id</code>
                to your user id (or set your email on a doctor record matching your login email).
            </p>
            <a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
