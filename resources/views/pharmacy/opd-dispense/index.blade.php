@extends('backend.layouts.master')

@section('title', 'OPD Dispense')

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">OPD Dispense</h4>
            <p class="text-muted mb-0 small">Medicines given to outdoor patients based on doctor prescription.</p>
        </div>
        <div class="text-muted small d-none d-md-block">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, M d, Y') }}
            <span class="ms-3"><i class="bi bi-clock me-1"></i> {{ now()->format('h:i A') }}</span>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#E8F5E9;">
                            <i class="bi bi-check-circle" style="color:#388E3C;"></i>
                        </div>
                        <span class="text-muted small">Today's Dispensed</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($todayTotal, 2) }}</h4>
                    <small class="text-muted">Across {{ $todayCount }} Visit{{ $todayCount !== 1 ? 's' : '' }}</small>
                </div>
                <div style="height:3px;background:#388E3C;"></div>
            </div>
        </div>
        <div class="col-xl col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFF3E0;">
                            <i class="bi bi-clock-history" style="color:#F57C00;"></i>
                        </div>
                        <span class="text-muted small">Pending Approval</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-warning">{{ $pendingCount }}</h4>
                    <small class="text-muted">Awaiting Pharmacist</small>
                </div>
                <div style="height:3px;background:#F57C00;"></div>
            </div>
        </div>
        <div class="col-xl col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFEBEE;">
                            <i class="bi bi-exclamation-circle" style="color:#D32F2F;"></i>
                        </div>
                        <span class="text-muted small">Partial Dispense</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-danger">{{ $partialCount }}</h4>
                    <small class="text-muted">Needs Attention</small>
                </div>
                <div style="height:3px;background:#D32F2F;"></div>
            </div>
        </div>
        <div class="col-xl col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#E3F2FD;">
                            <i class="bi bi-arrow-return-left" style="color:#1565C0;"></i>
                        </div>
                        <span class="text-muted small">Returns Today</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $returnCount }}</h4>
                    <small class="text-muted">Total Items</small>
                </div>
                <div style="height:3px;background:#1565C0;"></div>
            </div>
        </div>
        <div class="col-xl col-lg-3 col-sm-6">
            <div class="card border-0 rounded-3 h-100 d-flex align-items-center justify-content-center" style="background:var(--primary);min-height:120px;">
                <a data-size="xl"
                   data-url="{{ route('admin.pharmacy.opd-dispense.create') }}"
                   data-ajax-popup="true"
                   data-title="New OPD Dispense"
                   class="text-white text-decoration-none text-center"
                   id="btnNewDispense"
                   style="cursor:pointer;">
                    <i class="bi bi-plus-circle fs-3 d-block mb-1"></i>
                    <span class="fw-semibold">New OPD Dispense</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.pharmacy.opd-dispense') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">OPD No</label>
                        <input type="text" name="opd_no" class="form-control form-control-sm" placeholder="Search OPD No..." value="{{ request('opd_no') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Patient</label>
                        <input type="text" name="patient_name" class="form-control form-control-sm" placeholder="Search patient name..." value="{{ request('patient_name') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Prescription No</label>
                        <input type="text" name="prescription_no" class="form-control form-control-sm" placeholder="Search Rx No..." value="{{ request('prescription_no') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Pharmacist</label>
                        <select name="pharmacist_id" class="form-select form-select-sm">
                            <option value="">All Pharmacist</option>
                            @foreach($pharmacists as $pharm)
                                <option value="{{ $pharm->id }}" {{ request('pharmacist_id') == $pharm->id ? 'selected' : '' }}>{{ $pharm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-2 mt-1 align-items-end">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Dispense Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Filter</button>
                        <a href="{{ route('admin.pharmacy.opd-dispense') }}" class="btn btn-outline-secondary btn-sm ms-1"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                        <a data-size="xl"
                           data-url="{{ route('admin.pharmacy.opd-dispense.create') }}"
                           data-ajax-popup="true"
                           data-title="Add New OPD Dispense"
                           class="btn btn-dark btn-sm ms-1">Add OPD Dispense</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Dispense List --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="mb-0 fw-semibold">OPD Dispense List (Today: {{ now()->format('d/m/Y') }})</h6>
                    <small class="text-muted">{{ $dispenses->count() }} record{{ $dispenses->count() !== 1 ? 's' : '' }} found</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pharmacy.opd-dispense.export') . '?' . http_build_query(request()->only(['date_from','date_to','opd_no','patient_name','prescription_no','pharmacist_id','status','payment_status'])) }}"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download me-1"></i> Export CSV
                    </a>
                    <a href="{{ route('admin.pharmacy.opd-dispense.print') . '?' . http_build_query(request()->only(['date_from','date_to','opd_no','patient_name','prescription_no','pharmacist_id','status','payment_status'])) }}"
                       target="_blank"
                       class="btn btn-outline-success btn-sm">
                        <i class="bi bi-printer me-1"></i> Print
                    </a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dt_basic">
                <thead class="table-light">
                    <tr>
                        <th class="px-3 py-3 text-muted small fw-semibold">DISPENSE #</th>
                        <th class="py-3 text-muted small fw-semibold">DATE</th>
                        <th class="py-3 text-muted small fw-semibold">OPD NO</th>
                        <th class="py-3 text-muted small fw-semibold">PATIENT</th>
                        <th class="py-3 text-muted small fw-semibold">PRESCRIPTION NO</th>
                        <th class="py-3 text-muted small fw-semibold">DRUG COUNT</th>
                        <th class="py-3 text-muted small fw-semibold">TOTAL AMOUNT (TK)</th>
                        <th class="py-3 text-muted small fw-semibold">PHARMACIST</th>
                        <th class="py-3 text-muted small fw-semibold">STATUS</th>
                        <th class="py-3 text-muted small fw-semibold text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispenses as $dispense)
                        <tr>
                            <td class="px-3 py-3">
                                <span class="text-primary fw-medium">{{ $dispense->dispense_no }}</span>
                            </td>
                            <td class="py-3 small">{{ $dispense->created_at->format('d/m/Y') }}</td>
                            <td class="py-3">
                                <span class="text-primary">OPD-{{ str_pad($dispense->opdPatient->case_id ?? '', 5, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="py-3 fw-medium">{{ $dispense->patient->patient_name ?? '—' }}</td>
                            <td class="py-3">
                                @if($dispense->prescription)
                                    <span class="text-primary">{{ $dispense->prescription->prescription_no }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">{{ $dispense->drug_count }}</td>
                            <td class="py-3 fw-medium">{{ number_format($dispense->total_amount, 2) }}</td>
                            <td class="py-3 small">{{ $dispense->pharmacist->name ?? '—' }}</td>
                            <td class="py-3">
                                @switch($dispense->status)
                                    @case('completed')
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Completed</span>
                                        @break
                                    @case('pending_approval')
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Pending Approval</span>
                                        @break
                                    @case('partial')
                                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill">Partial</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Cancelled</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">{{ ucfirst($dispense->status) }}</span>
                                @endswitch
                            </td>
                            <td class="py-3 text-center">
                                <a data-size="xl"
                                   data-url="{{ route('admin.pharmacy.opd-dispense.show', $dispense->id) }}"
                                   data-ajax-popup="true"
                                   data-title="OPD Dispense — {{ $dispense->dispense_no }}"
                                   class="btn btn-sm btn-light rounded-circle"
                                   style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                   title="View Details">
                                    <i class="bi bi-eye small"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-prescription2 fs-1 d-block mb-2 opacity-25"></i>
                                <p class="mb-1">No dispense records found.</p>
                                <small>Adjust your filters or create a new OPD dispense.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Status Legend --}}
        <div class="card-body border-top py-3">
            <div class="d-flex flex-wrap gap-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px;height:24px;background:#E8F5E9;"><i class="bi bi-check-circle-fill small" style="color:#388E3C;"></i></span>
                    <div>
                        <div class="small fw-medium">Full Dispense</div>
                        <div class="text-muted" style="font-size:0.7rem;">All items dispensed</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px;height:24px;background:#FFF3E0;"><i class="bi bi-clock-fill small" style="color:#F57C00;"></i></span>
                    <div>
                        <div class="small fw-medium">Partial Dispense</div>
                        <div class="text-muted" style="font-size:0.7rem;">Some items pending</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px;height:24px;background:#E3F2FD;"><i class="bi bi-arrow-repeat small" style="color:#1565C0;"></i></span>
                    <div>
                        <div class="small fw-medium">Substitution</div>
                        <div class="text-muted" style="font-size:0.7rem;">Alt. medicine used</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px;height:24px;background:#FFEBEE;"><i class="bi bi-x-circle-fill small" style="color:#D32F2F;"></i></span>
                    <div>
                        <div class="small fw-medium">Return/Cancel</div>
                        <div class="text-muted" style="font-size:0.7rem;">Before finalization</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(session('show_create_modal'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btnNewDispense')?.click();
});
</script>
@endif
@endsection
