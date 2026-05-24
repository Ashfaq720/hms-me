@extends('backend.layouts.master')
@section('title','Pending Package Approvals')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0"><i class="bi bi-shield-exclamation me-2 text-warning"></i>Pending Package Approvals</h1>
            <small class="text-muted">Packages awaiting consultant / billing approval. Approve, cancel, or open the patient profile.</small>
        </div>
        <a href="{{ route('package-management.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    @if($assignments->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                <h5 class="mb-1">No pending approvals</h5>
                <p class="text-muted">Every package assignment has been reviewed.</p>
                <a href="{{ route('package-assignments.index') }}" class="btn btn-outline-primary mt-2">
                    <i class="bi bi-clipboard-check"></i> View all assignments
                </a>
            </div>
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center mb-3 py-2">
            <i class="bi bi-shield-exclamation me-2 fs-5"></i>
            <div><strong>{{ $assignments->total() }}</strong> package(s) awaiting approval.</div>
        </div>

        <div class="row g-3">
            @foreach($assignments as $a)
                @php
                    $pkg     = $a->package;
                    $patient = optional($a->ipdAdmission)->patient;
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 border-warning">
                        <div class="card-header bg-warning-subtle py-2 d-flex justify-content-between align-items-center">
                            <strong>{{ optional($pkg)->code }}</strong>
                            @if(optional($pkg)->approval_role)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-person-badge"></i> {{ $pkg->approval_role }}
                                </span>
                            @endif
                        </div>
                        <div class="card-body">
                            <h6 class="mb-1">{{ optional($pkg)->name }}</h6>
                            <div class="small text-muted mb-2">
                                <span class="badge bg-info">{{ optional($pkg)->package_type }}</span>
                            </div>

                            <dl class="row mb-2 small">
                                <dt class="col-5">Patient</dt>
                                <dd class="col-7">
                                    @if($patient && $a->ipd_admission_id)
                                        <a href="{{ route('ipd-patients.ipd-patients.show', $a->ipd_admission_id) }}#packages">
                                            {{ $patient->patient_name }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                                <dt class="col-5">Agreed Price</dt>
                                <dd class="col-7"><strong>৳{{ number_format($a->effectivePrice(), 2) }}</strong>
                                    @if($a->price_override !== null)
                                        <span class="badge bg-warning text-dark ms-1">Override</span>
                                    @endif
                                </dd>
                                <dt class="col-5">Applied</dt>
                                <dd class="col-7">
                                    {{ optional($a->applied_at)->diffForHumans() }}
                                    @if($a->appliedBy)<div class="text-muted">by {{ $a->appliedBy->name }}</div>@endif
                                </dd>
                                @if($a->remarks)
                                    <dt class="col-5">Remarks</dt>
                                    <dd class="col-7 small">{{ $a->remarks }}</dd>
                                @endif
                            </dl>
                        </div>
                        <div class="card-footer bg-white d-flex gap-2 justify-content-between">
                            @can('ipd_packages_approve')
                                <form action="{{ route('setup.ipd-patient-packages.approve', $a->id) }}" method="POST" class="d-inline">@csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-lg me-1"></i> Approve
                                    </button>
                                </form>
                            @endcan
                            @can('ipd_packages_apply')
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $a->id }}">
                                    <i class="bi bi-x-lg me-1"></i> Cancel
                                </button>
                            @endcan
                            @if($patient && $a->ipd_admission_id)
                                <a href="{{ route('ipd-patients.ipd-patients.show', $a->ipd_admission_id) }}#packages"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    @can('ipd_packages_apply')
                        <div class="modal fade" id="cancelModal-{{ $a->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('setup.ipd-patient-packages.cancel', $a->id) }}" method="POST">@csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cancel Pending Package</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small mb-2">Reject and cancel <strong>{{ optional($pkg)->code }} — {{ optional($pkg)->name }}</strong> for
                                                <strong>{{ optional($patient)->patient_name ?? 'patient' }}</strong>?</p>
                                            <label class="form-label">Reason *</label>
                                            <textarea name="cancellation_reason" rows="3" class="form-control" required maxlength="500"></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep Pending</button>
                                            <button type="submit" class="btn btn-danger">Cancel Package</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan
                </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $assignments->links() }}</div>
    @endif
</div>
@endsection
