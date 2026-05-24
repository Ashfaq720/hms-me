<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Patient Portal') · HMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --pp-primary: #0d6efd;
            --pp-primary-soft: rgba(13,110,253,.08);
            --pp-bg: #f4f7fb;
            --pp-card-radius: 16px;
            --pp-shadow: 0 4px 24px rgba(20, 30, 50, 0.06);
        }
        body {
            background: var(--pp-bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #1f2937;
        }
        .pp-nav {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d6efd 100%);
            color: #fff;
            box-shadow: 0 2px 14px rgba(0,0,0,.1);
        }
        .pp-nav .navbar-brand { color: #fff; font-weight: 700; letter-spacing: -0.01em; }
        .pp-nav .nav-link { color: rgba(255,255,255,.85); font-weight: 500; padding: .55rem 1rem; border-radius: 8px; transition: all .15s ease; }
        .pp-nav .nav-link:hover { background: rgba(255,255,255,.12); color: #fff; }
        .pp-nav .nav-link.active { background: rgba(255,255,255,.2); color: #fff; font-weight: 600; }
        .pp-nav .pp-user-chip { background: rgba(255,255,255,.15); border-radius: 100px; padding: .35rem .85rem .35rem .35rem; }
        .pp-nav .pp-user-chip img,
        .pp-nav .pp-user-chip .avatar {
            width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: .5rem;
            background: rgba(255,255,255,.25); display: inline-flex; align-items: center; justify-content: center;
        }
        .portal-card {
            border: 0;
            border-radius: var(--pp-card-radius);
            background: #fff;
            box-shadow: var(--pp-shadow);
        }
        .portal-card .card-header {
            background: transparent;
            border-bottom: 1px solid #eef1f7;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        .kpi-tile {
            border: 0;
            border-radius: var(--pp-card-radius);
            padding: 1.1rem 1.25rem;
            background: #fff;
            box-shadow: var(--pp-shadow);
            position: relative;
            overflow: hidden;
        }
        .kpi-tile::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 4px;
            background: var(--accent, var(--pp-primary));
            border-top-right-radius: var(--pp-card-radius);
            border-bottom-right-radius: var(--pp-card-radius);
        }
        .kpi-tile .label { color: #6b7280; font-size: .78rem; font-weight: 500; }
        .kpi-tile .value { font-size: 1.6rem; font-weight: 700; line-height: 1.1; margin: .25rem 0 .15rem; color: #111827; }
        .kpi-tile .sub { color: #9ca3af; font-size: .72rem; }
        .kpi-tile .icon {
            position: absolute; top: 1.1rem; right: 1rem; width: 36px; height: 36px;
            border-radius: 10px;
            background: var(--accent-bg, var(--pp-primary-soft));
            color: var(--accent, var(--pp-primary));
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .pp-table thead th {
            background: #f9fafc; color: #6b7280; font-weight: 500; font-size: .75rem;
            text-transform: uppercase; letter-spacing: .03em; border-bottom-color: #eef1f7;
        }
        .pp-table tbody tr:hover { background: #fafbff; }
        .pp-table td, .pp-table th { padding: .7rem 1rem; vertical-align: middle; }
        .pp-badge {
            display: inline-flex; align-items: center; gap: .25rem;
            padding: .2rem .55rem; border-radius: 6px;
            font-size: .7rem; font-weight: 600; letter-spacing: .02em;
        }
        .pp-badge.success { background: rgba(16,185,129,.1); color: #047857; }
        .pp-badge.warning { background: rgba(245,158,11,.12); color: #92400e; }
        .pp-badge.danger  { background: rgba(239,68,68,.12);  color: #991b1b; }
        .pp-badge.info    { background: rgba(59,130,246,.12); color: #1e40af; }
        .pp-badge.secondary { background: rgba(107,114,128,.12); color: #374151; }
        .pp-hero {
            background: linear-gradient(135deg, #dbeafe 0%, #fef3c7 100%);
            border-radius: var(--pp-card-radius);
            padding: 1.5rem 1.75rem;
            box-shadow: var(--pp-shadow);
        }
        .pp-hero h3 { font-weight: 700; color: #1e293b; margin-bottom: .25rem; }
        .quick-action {
            display: flex; align-items: center; gap: .75rem;
            padding: 1rem; border-radius: 12px;
            background: #fff; box-shadow: var(--pp-shadow);
            color: #1f2937; text-decoration: none;
            transition: transform .12s ease, box-shadow .15s ease;
        }
        .quick-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(20,30,50,.1);
            color: #1f2937;
        }
        .quick-action .icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
        footer.pp-footer { background: transparent; color: #6b7280; font-size: .8rem; padding: 1.5rem 0; }
        .bg-opacity-15 { --bs-bg-opacity: 0.15 !important; }
        @media (max-width: 768px) {
            .kpi-tile .value { font-size: 1.3rem; }
            .pp-hero { padding: 1.25rem; }
        }
    </style>
</head>
<body>
    @auth('patient')
    <nav class="navbar navbar-expand-lg pp-nav sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('portal.dashboard') }}">
                <i class="bi bi-heart-pulse-fill"></i>
                <span>HMS Patient Portal</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}" href="{{ route('portal.dashboard') }}">
                            <i class="bi bi-grid-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal.bills') ? 'active' : '' }}" href="{{ route('portal.bills') }}">
                            <i class="bi bi-receipt"></i> Bills
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal.prescriptions') ? 'active' : '' }}" href="{{ route('portal.prescriptions') }}">
                            <i class="bi bi-prescription2"></i> Prescriptions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal.profile') ? 'active' : '' }}" href="{{ route('portal.profile') }}">
                            <i class="bi bi-person-circle"></i> Profile
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <span class="pp-user-chip d-inline-flex align-items-center">
                        @php $u = auth('patient')->user(); @endphp
                        @if ($u->image)
                            <img src="{{ str_starts_with($u->image, 'http') ? $u->image : asset('storage/'.$u->image) }}" alt="me">
                        @else
                            <span class="avatar"><i class="bi bi-person-fill"></i></span>
                        @endif
                        <small class="fw-semibold">{{ \Illuminate\Support\Str::limit($u->patient_name, 18) }}</small>
                    </span>
                    <form action="{{ route('portal.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-light"><i class="bi bi-box-arrow-right"></i> Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="container py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="pp-footer text-center">
        © {{ date('Y') }} HMS Patient Portal · For medical emergencies, dial 999
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
