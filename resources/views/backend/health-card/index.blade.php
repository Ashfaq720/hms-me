@extends('backend.layouts.master')

@section('title', 'Health Card Management')

@php
    $palette = ['#0D9488', '#6366F1', '#EC4899', '#F97316', '#3B82F6', '#8B5CF6', '#0EA5E9', '#14B8A6', '#F59E0B', '#10B981'];
@endphp

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header --}}
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title mb-1">Health Card Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Health Card</li>
                    <li class="breadcrumb-item active">Card Management</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            {{-- Print Card button hidden per request (kept for future use)
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Card
            </button>
            --}}
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueCardModal">
                <i class="fas fa-plus me-1"></i> Issue New Card
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mt-2">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 52px; height: 52px; background: var(--primary-bg, #EEF2FF);">
                        <i class="fas fa-id-card" style="font-size: 22px; color: var(--primary, #4361EE);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Total Cards</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 52px; height: 52px; background: var(--success-bg, #D1FAE5);">
                        <i class="fas fa-check-circle" style="font-size: 22px; color: var(--success, #10B981);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['active']) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Active Cards</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 52px; height: 52px; background: var(--danger-bg, #FEE2E2);">
                        <i class="fas fa-ban" style="font-size: 22px; color: var(--danger, #EF4444);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['inactive']) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Inactive / Deceased</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 52px; height: 52px; background: #E0F2FE;">
                        <i class="fas fa-calendar-day" style="font-size: 22px; color: #0284C7;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['issued_today']) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Issued Today</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body py-3">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('health-card.index') }}">
                <div class="col-lg-3 col-md-4">
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Search by card no, name, MRN, mobile...">
                </div>
                <div class="col-lg-2 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        <option value="deceased" @selected($status === 'deceased')>Deceased</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <input type="date" name="from" value="{{ $from }}" class="form-control" placeholder="From Date">
                </div>
                <div class="col-lg-2 col-md-3">
                    <input type="date" name="to" value="{{ $to }}" class="form-control" placeholder="To Date">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('health-card.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-semibold">All Health Cards</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('health-card.print', request()->only(['q', 'status', 'from', 'to'])) }}"
                   target="_blank"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-print me-1"></i> Print
                </a>
                <a href="{{ route('health-card.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt me-1"></i> Refresh</a>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background: #F8FAFC;">
                            <th class="ps-3" style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Card No</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Patient</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Mobile</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">MRN</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Issue Date</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Expiry Date</th>
                            <th class="pe-3 text-center" style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            @php
                                $words    = array_filter(explode(' ', (string) $patient->patient_name));
                                $initials = strtoupper(
                                    substr($words[array_key_first($words)] ?? '', 0, 1) .
                                    substr($words[array_key_last($words)]  ?? '', 0, 1)
                                );
                                $bgColor = $palette[abs(crc32((string) $patient->patient_name)) % count($palette)];

                                $issueDate  = $patient->created_at;
                                $expiryDate = $issueDate ? $issueDate->copy()->addYears(2) : null;

                                $cardNo = $patient->health_card_no
                                    ?: 'HC-' . ($issueDate ? $issueDate->format('Y') : date('Y'))
                                        . '-' . str_pad((string) $patient->id, 5, '0', STR_PAD_LEFT);
                                $mrnNo = $patient->mrn
                                    ?: 'MRN-' . str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT);

                                if ($patient->is_dead) {
                                    $statusLabel = 'Deceased';
                                    $statusDot   = '#0F172A';
                                } elseif ($patient->is_active) {
                                    $statusLabel = 'Active';
                                    $statusDot   = '#10B981';
                                } else {
                                    $statusLabel = 'Inactive';
                                    $statusDot   = '#64748B';
                                }
                            @endphp
                            <tr>
                                <td class="ps-3 fw-semibold" style="color: var(--primary, #4361EE);">{{ $cardNo }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($patient->image)
                                            <img src="{{ asset('storage/' . $patient->image) }}" alt="{{ $patient->patient_name }}"
                                                 class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                                 style="width: 36px; height: 36px; background: {{ $bgColor }}; font-size: 13px;">
                                                {{ $initials ?: 'P' }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold" style="font-size: 14px;">{{ $patient->patient_name }}</div>
                                            <small class="text-muted">{{ $mrnNo }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $patient->mobileno ?: '—' }}</td>
                                <td><span class="text-muted">{{ $mrnNo }}</span></td>
                                <td>
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background: {{ $statusDot }};"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>{{ $issueDate ? $issueDate->format('d M Y') : '—' }}</td>
                                <td>{{ $expiryDate ? $expiryDate->format('d M Y') : '—' }}</td>
                                <td class="pe-3 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-sm btn-info text-white" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('health-card.show', $patient->id) }}" class="btn btn-sm btn-success" title="Health Card" target="_blank"><i class="fas fa-id-card"></i></a>
                                        <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                                    No patients found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                <small class="text-muted">
                    Showing {{ $patients->firstItem() ?? 0 }} to {{ $patients->lastItem() ?? 0 }}
                    of {{ number_format($patients->total()) }} entries
                </small>
                <div>{{ $patients->links() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Issue New Card Modal --}}
<div class="modal fade" id="issueCardModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Issue New Health Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Patient <span class="text-danger">*</span></label>
                        <select class="form-select" id="issueCardPatient">
                            <option selected disabled value="">Select Patient...</option>
                            @foreach ($allPatients as $p)
                                <option value="{{ $p->id }}"
                                        data-mrn="{{ $p->mrn }}"
                                        data-card="{{ $p->health_card_no }}">
                                    {{ $p->patient_name }} — {{ $p->mrn }}{{ $p->health_card_no ? ' / ' . $p->health_card_no : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Card Type <span class="text-danger">*</span></label>
                        <select class="form-select">
                            <option selected disabled>Select Card Type...</option>
                            <option>Basic</option>
                            <option>Silver</option>
                            <option>Gold</option>
                            <option>Corporate</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" value="{{ date('Y-m-d', strtotime('+2 years')) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary"><i class="fas fa-save me-1"></i> Issue Card</button>
            </div>
        </div>
    </div>
</div>
@endsection
