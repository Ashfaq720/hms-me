<div class="px-1">

    @php
        $medicine     = $batch->medicine;
        $now          = \Carbon\Carbon::today();
        $reorderLevel = (int) ($medicine->reorder_level ?? 0);
        $isExpired    = $batch->expiry_date && $batch->expiry_date->isPast();
        $isNearExpiry = $batch->expiry_date && !$isExpired && $batch->expiry_date->diffInDays($now) <= 90;
        $isOutOfStock = $batch->quantity <= 0;
        $isLowStock   = !$isOutOfStock && $reorderLevel > 0 && $batch->quantity <= $reorderLevel;
        $stockValue   = $batch->quantity * $batch->purchase_price;
    @endphp

    {{-- Header cards --}}
    <div class="row g-3 mb-4">

        {{-- Medicine info --}}
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#F8F9FF;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#EEF2FF;">
                        <i class="bi bi-capsule text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $medicine->medicine_name }}</div>
                        <div class="text-muted small">{{ $medicine->category->name ?? '—' }}</div>
                    </div>
                    <div class="ms-auto">
                        @if($isExpired)
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Expired</span>
                        @elseif($isOutOfStock)
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Out of Stock</span>
                        @elseif($isNearExpiry)
                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Near Expiry</span>
                        @elseif($isLowStock)
                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Low Stock</span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Normal</span>
                        @endif
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted py-1" style="width:42%;">Company</td>
                        <td class="fw-medium py-1">{{ $medicine->company->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Category</td>
                        <td class="fw-medium py-1">{{ $medicine->category->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Group</td>
                        <td class="fw-medium py-1">{{ $medicine->medicalGroup->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Unit</td>
                        <td class="fw-medium py-1">{{ $medicine->unit->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Reorder Level</td>
                        <td class="fw-medium py-1">{{ $reorderLevel > 0 ? number_format($reorderLevel) : '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Batch info --}}
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#F8FFF8;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#E8F5E9;">
                        <i class="bi bi-box-seam" style="color:#388E3C;"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark font-monospace">{{ $batch->batch_no }}</div>
                        <div class="text-muted small">{{ $batch->store }}</div>
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted py-1" style="width:42%;">Manufacture Date</td>
                        <td class="fw-medium py-1">{{ $batch->manufacture_date?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Expiry Date</td>
                        <td class="fw-medium py-1 {{ $isExpired || $isNearExpiry ? 'text-danger' : '' }}">
                            {{ $batch->expiry_date?->format('d M Y') ?? '—' }}
                            @if($isExpired)
                                <span class="text-danger" style="font-size:0.7rem;">(Expired)</span>
                            @elseif($isNearExpiry)
                                <span class="text-warning" style="font-size:0.7rem;">({{ $batch->expiry_date->diffInDays($now) }} days left)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Available Qty</td>
                        <td class="fw-bold py-1 {{ $isOutOfStock ? 'text-danger' : ($isLowStock ? 'text-warning' : 'text-success') }}">
                            {{ number_format($batch->quantity) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Purchase Price</td>
                        <td class="fw-medium py-1">{{ number_format($batch->purchase_price, 2) }} TK</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Selling Price</td>
                        <td class="fw-medium py-1">{{ number_format($batch->selling_price, 2) }} TK</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Stock Value</td>
                        <td class="fw-bold py-1 text-primary">{{ number_format($stockValue, 2) }} TK</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="d-flex justify-content-end border-top pt-3">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
    </div>

</div>
