<div class="px-1">

    @php
        $isExpired = $entry->expiration_date && $entry->expiration_date->isPast();
    @endphp

    {{-- Header cards --}}
    <div class="row g-3 mb-4">

        {{-- Entry Info --}}
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#F8F9FF;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#EEF2FF;">
                        <i class="bi bi-shield-exclamation text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark font-monospace">{{ $entry->entry_no }}</div>
                        <div class="text-muted small">{{ $entry->entry_date->format('d M Y, h:i A') }}</div>
                    </div>
                    <div class="ms-auto">
                        @switch($entry->inventory_status)
                            @case('available')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Available</span>
                                @break
                            @case('low_stock')
                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Low Stock</span>
                                @break
                            @case('out_of_stock')
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Out of Stock</span>
                                @break
                        @endswitch
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted py-1" style="width:42%;">Doctor Name</td>
                        <td class="fw-medium py-1">{{ $entry->doctor_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">DEA Number</td>
                        <td class="fw-medium py-1 font-monospace">{{ $entry->dea_number ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Generic Name</td>
                        <td class="fw-medium py-1">{{ $entry->generic_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Medicine</td>
                        <td class="fw-medium py-1">{{ $entry->medicine->medicine_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Schedule</td>
                        <td class="fw-medium py-1 text-primary">{{ $entry->schedule }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Added By</td>
                        <td class="fw-medium py-1">{{ $entry->createdBy->name ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Batch / Quantity Info --}}
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#F8FFF8;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#E8F5E9;">
                        <i class="bi bi-box-seam" style="color:#388E3C;"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark font-monospace">{{ $entry->lot_number }}</div>
                        <div class="text-muted small">Lot Number</div>
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted py-1" style="width:42%;">NDC Code</td>
                        <td class="fw-medium py-1 font-monospace">{{ $entry->ndc_code ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Expiration Date</td>
                        <td class="fw-medium py-1 {{ $isExpired ? 'text-danger' : '' }}">
                            {{ $entry->expiration_date?->format('m/d/Y') ?? '—' }}
                            @if($isExpired) <span class="text-danger" style="font-size:0.7rem;">(Expired)</span> @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Action</td>
                        <td class="py-1">
                            @if($entry->action_type === 'received')
                                <span class="text-success fw-medium">Received</span>
                            @else
                                <span class="text-danger fw-medium">Removed</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Quantity</td>
                        <td class="fw-bold py-1 text-primary">{{ $entry->quantity }} {{ $entry->unit }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($entry->notes)
        <div class="alert alert-light border small mb-4">
            <i class="bi bi-chat-left-text me-1 text-muted"></i>
            <strong>Notes:</strong> {{ $entry->notes }}
        </div>
    @endif

    {{-- Footer --}}
    <div class="d-flex justify-content-end border-top pt-3">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
    </div>

</div>
