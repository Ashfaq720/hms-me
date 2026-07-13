<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Bed Name</label>
        <p class="fw-semibold mb-2">{{ $allocation->bed->name ?? 'N/A' }}</p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Bed Rent</label>
        <p class="fw-semibold mb-2">&#2547; {{ number_format($allocation->bed->rent ?? 0, 2) }}</p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Bed Type</label>
        <p class="fw-semibold mb-2">{{ $allocation->bed->bedType->name ?? 'N/A' }}</p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Bed Group</label>
        <p class="fw-semibold mb-2">{{ $allocation->bed->bedGroup->name ?? 'N/A' }}</p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0">From Date</label>
        <p class="fw-semibold mb-2">{{ $allocation->from ? format_datetime($allocation->from) : 'N/A' }}</p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0">To Date</label>
        <p class="fw-semibold mb-2">
            @if ($allocation->to)
                {{ format_datetime($allocation->to) }}
            @else
                <span class="badge bg-success">Present</span>
            @endif
        </p>
    </div>

    <div class="col-md-12">
        <label class="form-label text-muted mb-0">Remarks</label>
        <p class="fw-semibold mb-2">{{ $allocation->remarks ?? '-' }}</p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Status</label>
        <p class="fw-semibold mb-2">{{ $allocation->status ?? '-' }}</p>
    </div>
</div>
