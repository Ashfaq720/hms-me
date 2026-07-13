<div class="px-1">

    {{-- Header cards --}}
    <div class="row g-3 mb-4">

        {{-- Issue info --}}
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#F8F9FF;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#EEF2FF;">
                        <i class="bi bi-clipboard2-data text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $issue->issue_no }}</div>
                        <div class="text-muted small">{{ $issue->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                    <div class="ms-auto">
                        @switch($issue->status)
                            @case('approved')  <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Approved</span> @break
                            @case('pending')   <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Pending</span> @break
                            @case('returned')  <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Returned</span> @break
                            @case('resumed')   <span class="badge rounded-pill" style="background:#FFF8E1;color:#F9A825;">Resumed</span> @break
                            @default           <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">{{ ucfirst($issue->status) }}</span>
                        @endswitch
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted py-1" style="width:42%;">Ipd No</td>
                        <td class="fw-medium py-1 text-primary">{{ $issue->ipdPatient->ipd_no ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Requisition No</td>
                        <td class="fw-medium py-1">{{ $issue->requisition_no ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Ward / Bed</td>
                        <td class="fw-medium py-1">{{ $issue->ward_bed ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Request Source</td>
                        <td class="fw-medium py-1">{{ $issue->request_source ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Issued By</td>
                        <td class="fw-medium py-1">{{ $issue->issuedBy->name ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Patient info --}}
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#F8FFF8;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#E8F5E9;">
                        <i class="bi bi-person-circle" style="color:#388E3C;"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $issue->patient->patient_name ?? '—' }}</div>
                        <div class="text-muted small">MRN: {{ $issue->patient->mrn ?? '—' }}</div>
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted py-1" style="width:42%;">Gender</td>
                        <td class="fw-medium py-1">{{ ucfirst($issue->patient->gender ?? '—') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Contact</td>
                        <td class="fw-medium py-1">{{ $issue->patient->mobileno ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Drug Count</td>
                        <td class="fw-medium py-1">{{ $issue->drug_count }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Total Amount</td>
                        <td class="fw-bold py-1 text-primary">{{ number_format($issue->total_amount, 2) }} TK</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Medicine items --}}
    <h6 class="fw-semibold mb-3">
        <i class="bi bi-capsule me-1 text-primary"></i> Issued Medicines
        <span class="badge bg-primary bg-opacity-10 text-primary ms-1">{{ $issue->drug_count }}</span>
    </h6>

    @if($issue->items->isNotEmpty())
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle border rounded-3 overflow-hidden mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-2 small fw-semibold text-muted">#</th>
                        <th class="py-2 small fw-semibold text-muted">MEDICINE</th>
                        <th class="py-2 small fw-semibold text-muted">DURATION</th>
                        <th class="py-2 small fw-semibold text-muted text-center">QTY REQ.</th>
                        <th class="py-2 small fw-semibold text-muted text-center">AVAIL. QTY</th>
                        <th class="py-2 small fw-semibold text-muted">STORE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($issue->items as $i => $item)
                        <tr>
                            <td class="py-2 text-muted small">{{ $i + 1 }}</td>
                            <td class="py-2 fw-medium small">{{ $item->medicine->medicine_name ?? '—' }}</td>
                            <td class="py-2 small text-muted">{{ $item->duration ?: '—' }}</td>
                            <td class="py-2 text-center fw-medium">{{ $item->qty_required }}</td>
                            <td class="py-2 text-center">
                                @if($item->available_qty <= 0)
                                    <span class="text-danger fw-medium">{{ $item->available_qty }}</span>
                                @elseif($item->available_qty <= 10)
                                    <span class="text-warning fw-medium">{{ $item->available_qty }}</span>
                                @else
                                    <span class="text-success fw-medium">{{ $item->available_qty }}</span>
                                @endif
                            </td>
                            <td class="py-2 small text-muted">{{ $item->store }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4 text-muted border rounded-3 mb-4">
            <i class="bi bi-capsule fs-2 d-block mb-2 opacity-25"></i>
            <small>No medicine items recorded.</small>
        </div>
    @endif

    {{-- Note --}}
    @if($issue->note)
        <div class="alert alert-light border small mb-4">
            <i class="bi bi-chat-left-text me-1 text-muted"></i>
            <strong>Note:</strong> {{ $issue->note }}
        </div>
    @endif

    {{-- Footer actions --}}
    <div class="d-flex justify-content-between align-items-center border-top pt-3">
        <div class="d-flex gap-2">
            @if($issue->status === 'pending')
                <form method="POST" action="{{ route('admin.pharmacy.ipd-issue.approve', $issue->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"
                            onclick="return confirm('Approve issue {{ $issue->issue_no }}?')">
                        <i class="bi bi-check-circle me-1"></i> Approve
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.pharmacy.ipd-issue.print-single', $issue->id) }}"
               target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Print
            </a>
        </div>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
    </div>

</div>
