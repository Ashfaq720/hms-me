<div class="px-1">

    {{-- Header Info --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#F8F9FF;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#EEF2FF;">
                        <i class="bi bi-receipt text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $dispense->dispense_no }}</div>
                        <div class="text-muted small">{{ $dispense->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted py-1" style="width:40%;">OPD Case No</td>
                        <td class="fw-medium py-1">
                            {{ $dispense->opdPatient->case_id ?? '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Prescription</td>
                        <td class="fw-medium py-1">
                            {{ $dispense->prescription->prescription_no ?? '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Dispense Date</td>
                        <td class="fw-medium py-1">{{ $dispense->created_at->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Pharmacist</td>
                        <td class="fw-medium py-1">{{ $dispense->pharmacist->name ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#F8FFF8;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#E8F5E9;">
                        <i class="bi bi-person-circle" style="color:#388E3C;"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $dispense->patient->patient_name ?? '—' }}</div>
                        <div class="text-muted small">MRN: {{ $dispense->patient->mrn ?? '—' }}</div>
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted py-1" style="width:40%;">Gender</td>
                        <td class="fw-medium py-1">{{ ucfirst($dispense->patient->gender ?? '—') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Contact</td>
                        <td class="fw-medium py-1">{{ $dispense->patient->mobileno ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Payment Status</td>
                        <td class="py-1">
                            @if($dispense->payment_status === 'paid')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Paid</span>
                            @elseif($dispense->payment_status === 'unpaid')
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Unpaid</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">{{ ucfirst($dispense->payment_status) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Dispense Status</td>
                        <td class="py-1">
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
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Dispensed Medicines --}}
    <h6 class="fw-semibold mb-3">
        <i class="bi bi-capsule me-1 text-primary"></i> Dispensed Medicines
        <span class="badge bg-primary bg-opacity-10 text-primary ms-1">{{ $dispense->drug_count }}</span>
    </h6>

    @if($dispense->items->isNotEmpty())
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle border rounded-3 overflow-hidden mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-2 small fw-semibold text-muted">#</th>
                        <th class="py-2 small fw-semibold text-muted">MEDICINE</th>
                        <th class="py-2 small fw-semibold text-muted">DOSAGE</th>
                        <th class="py-2 small fw-semibold text-muted text-center">QTY</th>
                        <th class="py-2 small fw-semibold text-muted text-end">UNIT PRICE</th>
                        <th class="py-2 small fw-semibold text-muted text-end">SUBTOTAL</th>
                        <th class="py-2 small fw-semibold text-muted">STORE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dispense->items as $i => $item)
                        <tr>
                            <td class="py-2 text-muted small">{{ $i + 1 }}</td>
                            <td class="py-2 fw-medium small">{{ $item->medicine->medicine_name ?? '—' }}</td>
                            <td class="py-2 small text-muted">{{ $item->dosage ?: '—' }}</td>
                            <td class="py-2 text-center fw-medium">{{ $item->qty_required }}</td>
                            <td class="py-2 text-end small">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2 text-end fw-medium">{{ number_format($item->unit_price * $item->qty_required, 2) }}</td>
                            <td class="py-2 small text-muted">{{ $item->store }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="py-2 text-end fw-semibold small">Total Amount</td>
                        <td class="py-2 text-end fw-bold text-primary">{{ number_format($dispense->total_amount, 2) }} TK</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="text-center py-4 text-muted border rounded-3 mb-4">
            <i class="bi bi-capsule fs-2 d-block mb-2 opacity-25"></i>
            <small>No medicine items recorded for this dispense.</small>
        </div>
    @endif

    {{-- Note --}}
    @if($dispense->note)
        <div class="alert alert-light border small mb-4">
            <i class="bi bi-chat-left-text me-1 text-muted"></i>
            <strong>Note:</strong> {{ $dispense->note }}
        </div>
    @endif

    {{-- Footer --}}
    <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>

</div>
