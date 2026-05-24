@extends('portal.layout')
@section('title', 'Login')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height:75vh;">
    <div class="col-md-5 col-lg-4">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle shadow"
                 style="width:80px; height:80px; background:linear-gradient(135deg,#1e3a8a 0%,#0d6efd 100%);">
                <i class="bi bi-heart-pulse-fill text-white" style="font-size:36px;"></i>
            </div>
            <h3 class="mt-3 mb-1 fw-bold">Patient Portal</h3>
            <p class="text-muted small mb-0">Sign in to see your bills, prescriptions &amp; visit history.</p>
        </div>

        <div class="portal-card">
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('portal.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            <i class="bi bi-card-text me-1 text-primary"></i> MRN, Mobile, or Email
                        </label>
                        <input type="text" name="identifier" value="{{ old('identifier') }}"
                               class="form-control form-control-lg"
                               placeholder="MRN-000001 or 01XXXXXXXXX" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            <i class="bi bi-shield-lock me-1 text-primary"></i> Password
                        </label>
                        <input type="password" name="password" class="form-control form-control-lg" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1">
                            <label for="remember" class="form-check-label small text-muted">Remember me</label>
                        </div>
                        <small class="text-muted">Forgot? Ask reception</small>
                    </div>

                    <button class="btn btn-primary btn-lg w-100 fw-semibold">
                        <i class="bi bi-box-arrow-in-right"></i> Sign in
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-muted small mt-4 mb-0">
            <i class="bi bi-info-circle"></i> First time? Ask reception to set your portal password at registration.
        </p>
    </div>
</div>
@endsection
