@extends('backend.layouts.auth')

@section('title', setting('company_name') . ' Login')

@section('content')
    <div class="page-layout">
        <div class="auth-wrapper min-vh-100 d-flex align-items-stretch position-relative"
            style="
                background:
                    radial-gradient(1200px 600px at 10% 10%, rgba(255,255,255,.18), transparent 55%),
                    radial-gradient(900px 500px at 90% 20%, rgba(255,255,255,.12), transparent 60%),
                    linear-gradient(135deg, rgba(8, 145, 178, .90), rgba(30, 64, 175, .90));
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            ">

            <div class="w-100" style="backdrop-filter: blur(4px);">
                <div class="container-fluid px-3 px-sm-4 px-lg-5 py-4 py-lg-5 min-vh-100 d-flex align-items-center">
                    <div class="row w-100 g-4 align-items-center justify-content-center">

                        {{-- LEFT: show from lg only (keep as you had) --}}
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="pe-0 pe-lg-4 pe-xl-5 text-white">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <div class="rounded-4 d-flex align-items-center justify-content-center"
                                        style="
                                            width:60px;height:60px;
                                            background: rgba(255,255,255,.14);
                                            border: 1px solid rgba(255,255,255,.22);
                                            box-shadow: 0 10px 30px rgba(0,0,0,.12);
                                        ">
                                        <i class="fa-solid fa-hospital fa-xl"></i>
                                    </div>
                                    <div>
                                        <h2 class="mb-1 fw-bold">{{ setting('company_name') }} Admin Portal</h2>
                                        <div class="opacity-75">Hospital Management System</div>
                                    </div>
                                </div>

                                <p class="mb-4 fs-5" style="max-width: 560px; opacity:.92;">
                                    Manage Front Desk, OPD, Ipd, Appointments, Pharmacy, Billing & Reports —
                                    with secure, role-based access.
                                </p>

                                <div class="row g-3" style="max-width: 620px;">
                                    <div class="col-12 col-md-6">
                                        <div class="p-3 rounded-4"
                                            style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);">
                                            <div class="fw-semibold mb-1">
                                                <i class="fa-solid fa-user-doctor me-2"></i>Clinical-ready
                                            </div>
                                            <div class="small opacity-75">OPD/Ipd workflow, prescriptions, diagnostics.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="p-3 rounded-4"
                                            style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);">
                                            <div class="fw-semibold mb-1">
                                                <i class="fa-solid fa-bolt me-2"></i>Faster operations
                                            </div>
                                            <div class="small opacity-75">Streamlined login + quick dashboard access.</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="p-3 rounded-4"
                                            style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);">
                                            <div class="fw-semibold mb-1">
                                                <i class="fa-solid fa-file-shield me-2"></i>Data privacy
                                            </div>
                                            <div class="small opacity-75">Designed for sensitive patient information.</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="p-3 rounded-4"
                                            style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);">
                                            <div class="fw-semibold mb-1">
                                                <i class="fa-solid fa-headset me-2"></i>Support
                                            </div>
                                            <div class="small opacity-75">Need help? Contact IT Admin.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 small opacity-75">
                                    <i class="fa-solid fa-circle-info me-2 text-warning"></i>
                                    Tip: Use your official hospital email or staff ID credentials.
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT: Login --}}
                        <div class="col-12 col-md-10 col-lg-6 col-xl-5">
                            {{-- Bootstrap responsive width instead of max-width --}}
                            <div class="mx-auto w-100">

                                <div class="card border-0 rounded-4 overflow-hidden shadow-lg">

                                    {{-- Header --}}
                                    <div class="p-4 p-sm-5 text-center bg-light">
                                        <div class="d-flex justify-content-center">
                                            <a href="#" aria-label="{{ setting('company_name') }} logo" class="text-decoration-none">
                                                <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name') }} logo"
                                                    height="56">
                                            </a>
                                        </div>

                                        <div class="mt-2">
                                            <h4 class="mb-1 fw-bold">Welcome back</h4>
                                            <p class="mb-0 text-muted">Sign in to access your {{ setting('company_name') }} dashboard.</p>
                                        </div>
                                    </div>

                                    {{-- Body --}}
                                    <div class="card-body p-4 p-sm-5">

                                        @if (session('success'))
                                            <div class="alert alert-success alert-dismissible fade show rounded-3"
                                                role="alert">
                                                <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        @endif

                                        @if ($errors->any())
                                            <div class="alert alert-danger alert-dismissible fade show rounded-3"
                                                role="alert">
                                                <div class="fw-semibold mb-2">
                                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Login Failed
                                                </div>
                                                @foreach ($errors->all() as $error)
                                                    <div class="small">{{ $error }}</div>
                                                @endforeach
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        @endif

                                        <form action="{{ route('login') }}" method="POST" id="hmsLoginForm" novalidate>
                                            @csrf

                                            {{-- Email --}}
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold" for="loginEmail">Email</label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-transparent">
                                                        <i class="fa-solid fa-user"></i>
                                                    </span>
                                                    <input type="text"
                                                        class="form-control @error('email') is-invalid @enderror"
                                                        id="loginEmail" name="email"
                                                        placeholder="e.g. doctor@hospital.com" value="{{ old('email') }}"
                                                        autocomplete="username" required>
                                                </div>
                                                @error('email')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Password --}}
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold" for="loginPassword">Password</label>

                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-transparent">
                                                        <i class="fa-solid fa-key"></i>
                                                    </span>

                                                    <input type="password"
                                                        class="form-control @error('password') is-invalid @enderror"
                                                        id="loginPassword" name="password"
                                                        placeholder="Enter your password" autocomplete="current-password"
                                                        required>

                                                    <button class="btn btn-outline-secondary" type="button"
                                                        id="togglePassword" aria-label="Show password">
                                                        <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                                                    </button>
                                                </div>

                                                @error('password')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror

                                                <div class="d-none mt-2 small text-warning" id="capsHint">
                                                    <i class="fa-solid fa-keyboard me-1"></i> Caps Lock is ON
                                                </div>
                                            </div>

                                            {{-- Remember --}}
                                            <div
                                                class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="rememberMe"
                                                        name="remember">
                                                    <label class="form-check-label" for="rememberMe">Keep me signed
                                                        in</label>
                                                </div>
                                            </div>

                                            {{-- Super Admin quick-fill Start : For Testing purposes only --}}
                                                <button type="button"
                                                    class="btn btn-secondary btn-lg w-100 rounded-3 mb-2"
                                                    id="superAdminBtn">
                                                    <span class="me-2"><i class="fa-solid fa-user-shield"></i></span>
                                                    <span>Super Admin</span>
                                                </button>
                                            {{-- Super Admin quick-fill End--}

                                            {{-- Submit --}}
                                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3"
                                                id="loginBtn">
                                                <span class="me-2"><i class="fa-solid fa-right-to-bracket"></i></span>
                                                <span id="loginBtnText">Login</span>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Footer --}}
                                    <div class="card-footer bg-transparent border-0 px-4 px-sm-5 pb-4 pb-sm-5">
                                        <div
                                            class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 small text-muted">
                                            <div>
                                                <i class="fa-solid fa-shield-heart me-1 text-success"></i>
                                                Patient data protected
                                            </div>
                                            <div class="text-warning">
                                                <i class="fa-regular fa-clock me-1"></i>
                                                {{ now()->format('d M Y, h:i A') }}
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="text-center mt-3 small text-white opacity-75">
                                    © {{ date('Y') }} {{ setting('company_name') }} • Powered by CraftCode
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                const pwd = document.getElementById('loginPassword');
                const toggleBtn = document.getElementById('togglePassword');
                const toggleIcon = document.getElementById('togglePasswordIcon');
                const capsHint = document.getElementById('capsHint');

                const form = document.getElementById('hmsLoginForm');
                const loginBtn = document.getElementById('loginBtn');
                const loginBtnText = document.getElementById('loginBtnText');

                if (toggleBtn && pwd) {
                    toggleBtn.addEventListener('click', () => {
                        const isPassword = pwd.getAttribute('type') === 'password';
                        pwd.setAttribute('type', isPassword ? 'text' : 'password');
                        toggleIcon.classList.toggle('fa-eye', !isPassword);
                        toggleIcon.classList.toggle('fa-eye-slash', isPassword);
                        toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                        pwd.focus();
                    });

                    pwd.addEventListener('keyup', (e) => {
                        if (typeof e.getModifierState === 'function') {
                            const caps = e.getModifierState('CapsLock');
                            capsHint.classList.toggle('d-none', !caps);
                        }
                    });
                }

                if (form) {
                    form.addEventListener('submit', () => {
                        loginBtn.disabled = true;
                        loginBtn.classList.add('disabled');
                        loginBtnText.textContent = 'Signing in...';
                    });
                }

                // Super Admin Quick-Fill Start: For demo/testing purposes, this button fills in preset credentials. Remove in production!
                const superAdminBtn = document.getElementById('superAdminBtn');
                const emailInput = document.getElementById('loginEmail');
                if (superAdminBtn && emailInput && pwd) {
                    superAdminBtn.addEventListener('click', () => {
                        emailInput.value = 'admin@example.com';
                        pwd.value = '12345678';
                        superAdminBtn.classList.add('d-none');
                    });
                }
                // Super Admin Quick-Fill End
            })();
        </script>
    @endpush
@endsection
