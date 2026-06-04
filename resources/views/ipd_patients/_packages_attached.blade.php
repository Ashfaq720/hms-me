@php
    $attached = $iPDPatient->servicePackages()
        ->with([
            'package.bedType', 'package.surgeryType', 'package.items',
            'approver', 'appliedBy', 'cancelledBy',
            'bedAllocation.bed.bedType',
        ])
        ->orderBy('id', 'desc')
        ->get();
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h6 class="mb-0">Service Packages</h6>
        <small class="text-muted">Packages attached during admission. Status drives billing and modification rules.</small>
    </div>
    <a href="{{ route('ipd-patients.ipd-patients.edit', $iPDPatient->id) }}#packageCollapse"
       class="btn btn-outline-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Manage Packages
    </a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@if($attached->isEmpty())
    <div class="card">
        <div class="card-body text-center text-muted py-4">
            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
            No service packages attached to this admission yet.
            <div class="mt-2">
                <a href="{{ route('ipd-patients.ipd-patients.edit', $iPDPatient->id) }}#packageCollapse" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Attach a Package
                </a>
            </div>
        </div>
    </div>
@else
    @foreach($attached as $att)
        @php $pkg = $att->package; @endphp
        <div class="card mb-3 border-{{ $att->status === 'Confirmed' ? 'primary' : ($att->status === 'Completed' ? 'success' : ($att->status === 'Cancelled' ? 'danger' : 'secondary')) }}">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong>{{ optional($pkg)->code }}</strong> — {{ optional($pkg)->name }}
                    <span class="badge bg-info ms-1">{{ optional($pkg)->package_type }}</span>
                    <span class="badge {{ $att->status_badge_class }} ms-1">{{ $att->status }}</span>
                    @if(optional($pkg)->requires_approval)
                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-shield-exclamation"></i> Approval gated</span>
                    @endif
                </div>
                <div class="d-flex gap-1">
                    @can('ipd_packages_approve')
                        @if($att->status === \App\Models\IpdPatientPackage::STATUS_PENDING_APPROVAL)
                            <form action="{{ route('setup.ipd-patient-packages.approve', $att->id) }}" method="POST" class="d-inline">@csrf
                                <button class="btn btn-sm btn-outline-success" title="Approve">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                            </form>
                        @endif
                    @endcan
                    @can('ipd_packages_apply')
                        @if(in_array($att->status, [\App\Models\IpdPatientPackage::STATUS_CONFIRMED, \App\Models\IpdPatientPackage::STATUS_PARTIALLY_USED]))
                            <form action="{{ route('setup.ipd-patient-packages.complete', $att->id) }}" method="POST" class="d-inline">@csrf
                                <button class="btn btn-sm btn-outline-primary" title="Mark Complete">
                                    <i class="bi bi-check2-all"></i> Complete
                                </button>
                            </form>
                        @endif
                        @if($att->status === \App\Models\IpdPatientPackage::STATUS_COMPLETED)
                            <form action="{{ route('setup.ipd-patient-packages.close', $att->id) }}" method="POST" class="d-inline">@csrf
                                <button class="btn btn-sm btn-outline-secondary" title="Close (billing finalized)">
                                    <i class="bi bi-lock"></i> Close
                                </button>
                            </form>
                        @endif
                        @if($att->canBeCancelled())
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#cancelPkgModal-{{ $att->id }}">
                                <i class="bi bi-x-lg"></i> Cancel
                            </button>
                        @endif
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <dl class="row mb-0 small">
                            <dt class="col-sm-4">Department</dt>
                            <dd class="col-sm-8">{{ optional(optional($pkg)->department)->name ?? '—' }}</dd>
                            <dt class="col-sm-4">Bed</dt>
                            <dd class="col-sm-8">
                                @if($att->bedAllocation && $att->bedAllocation->bed)
                                    <strong>{{ $att->bedAllocation->bed->name }}</strong>
                                    @if(optional($att->bedAllocation->bed->bedType)->name)
                                        <span class="badge bg-light text-dark border ms-1">{{ $att->bedAllocation->bed->bedType->name }}</span>
                                    @endif
                                    <span class="badge bg-success ms-1" title="Bed allocated together with this package">
                                        <i class="bi bi-link-45deg"></i> Linked
                                    </span>
                                @elseif(optional($pkg)->bedType)
                                    <span class="text-muted">{{ optional($pkg)->bedType->name }} <small>(package default — no allocation yet)</small></span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </dd>
                            <dt class="col-sm-4">Surgery Type</dt>
                            <dd class="col-sm-8">{{ optional(optional($pkg)->surgeryType)->name ?? '—' }}</dd>
                            <dt class="col-sm-4">Duration</dt>
                            <dd class="col-sm-8">{{ optional($pkg)->duration_days ? optional($pkg)->duration_days . ' day(s)' : '—' }}</dd>
                            <dt class="col-sm-4">Agreed Price</dt>
                            <dd class="col-sm-8"><strong>৳{{ number_format($att->effectivePrice(), 2) }}</strong>
                                @if($att->price_override !== null)
                                    <span class="text-muted small ms-2">(override of ৳{{ number_format((float) $att->agreed_price, 2) }})</span>
                                @endif
                            </dd>
                            <dt class="col-sm-4">Applied</dt>
                            <dd class="col-sm-8">{{ optional($att->applied_at)->format('Y-m-d H:i') }}
                                @if($att->appliedBy) by {{ $att->appliedBy->name ?? '#' . $att->applied_by }}@endif
                            </dd>
                            @if($att->approved_at)
                                <dt class="col-sm-4">Approved</dt>
                                <dd class="col-sm-8">{{ $att->approved_at->format('Y-m-d H:i') }}
                                    @if($att->approver) by {{ $att->approver->name ?? '#' . $att->approved_by }}@endif
                                </dd>
                            @endif
                            @if($att->cancelled_at)
                                <dt class="col-sm-4">Cancelled</dt>
                                <dd class="col-sm-8">{{ $att->cancelled_at->format('Y-m-d H:i') }}
                                    @if($att->cancelledBy) by {{ $att->cancelledBy->name ?? '#' . $att->cancelled_by }}@endif
                                    @if($att->cancellation_reason)
                                        <div class="text-muted small">Reason: {{ $att->cancellation_reason }}</div>
                                    @endif
                                </dd>
                            @endif
                            @if($att->remarks)
                                <dt class="col-sm-4">Remarks</dt>
                                <dd class="col-sm-8">{{ $att->remarks }}</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header py-1"><strong class="small">Included Items ({{ $pkg?->items?->count() ?? 0 }})</strong></div>
                            <div class="card-body p-2" style="max-height: 220px; overflow-y: auto;">
                                @forelse($pkg?->items ?? [] as $item)
                                    <div class="d-flex justify-content-between border-bottom py-1 small">
                                        <span>
                                            <span class="badge bg-light text-dark border">{{ $item->item_category }}</span>
                                            {{ $item->item_name }}
                                        </span>
                                        <span class="text-muted">{{ rtrim(rtrim(number_format($item->included_qty, 2), '0'), '.') }} {{ $item->unit }}</span>
                                    </div>
                                @empty
                                    <div class="text-muted small">No items.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($att->canBeCancelled())
            <div class="modal fade" id="cancelPkgModal-{{ $att->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('setup.ipd-patient-packages.cancel', $att->id) }}" method="POST">@csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cancel Package — {{ optional($pkg)->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning py-2 small">Cancelling a confirmed package may require billing reversal — apply with caution.</div>
                                <label class="form-label">Cancellation reason *</label>
                                <textarea name="cancellation_reason" rows="3" class="form-control" required maxlength="500"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep</button>
                                <button type="submit" class="btn btn-danger">Cancel Package</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
@endif
