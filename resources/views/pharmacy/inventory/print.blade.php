<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Report</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial, sans-serif; font-size:12px; color:#222; padding:24px; }
        .header { text-align:center; border-bottom:2px solid #333; padding-bottom:12px; margin-bottom:16px; }
        .header h2 { font-size:18px; font-weight:bold; margin-bottom:4px; }
        .header p  { font-size:11px; color:#555; }
        .filters { background:#f5f5f5; border:1px solid #ddd; border-radius:4px; padding:8px 12px; margin-bottom:16px; font-size:11px; }
        .filters span { margin-right:16px; }
        .summary { display:flex; gap:12px; margin-bottom:16px; }
        .summary-box { flex:1; border:1px solid #ddd; border-radius:4px; padding:8px; text-align:center; }
        .summary-box .val { font-size:16px; font-weight:bold; color:#1a237e; }
        .summary-box .lbl { font-size:10px; color:#666; margin-top:2px; }
        table { width:100%; border-collapse:collapse; margin-bottom:16px; }
        thead th { background:#1a237e; color:#fff; padding:7px 8px; font-size:11px; text-align:left; }
        tbody tr:nth-child(even) { background:#f9f9f9; }
        tbody td { padding:6px 8px; border-bottom:1px solid #eee; font-size:11px; vertical-align:middle; }
        .badge { display:inline-block; padding:2px 7px; border-radius:20px; font-size:10px; font-weight:bold; }
        .badge-normal   { background:#e8f5e9; color:#2e7d32; }
        .badge-warning  { background:#fff8e1; color:#f57c00; }
        .badge-danger   { background:#ffebee; color:#c62828; }
        .badge-out      { background:#f3e5f5; color:#7b1fa2; }
        .footer { text-align:center; font-size:10px; color:#888; border-top:1px solid #ddd; padding-top:10px; margin-top:8px; }
        .no-print { text-align:center; margin-top:20px; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>

    <div class="header">
        <h2>Pharmacy Inventory Report</h2>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    @php $activeFilters = array_filter($filters); @endphp
    @if(count($activeFilters))
        <div class="filters">
            <strong>Filters:</strong>
            @if(!empty($filters['medicine_name'])) <span>Drug: {{ $filters['medicine_name'] }}</span> @endif
            @if(!empty($filters['batch_no']))       <span>Batch: {{ $filters['batch_no'] }}</span> @endif
            @if(!empty($filters['store']))          <span>Store: {{ $filters['store'] }}</span> @endif
            @if(!empty($filters['stock_status']))   <span>Stock: {{ ucfirst($filters['stock_status']) }}</span> @endif
            @if(!empty($filters['expiry_status']))  <span>Expiry: {{ ucfirst($filters['expiry_status']) }}</span> @endif
        </div>
    @endif

    @php
        $now = \Carbon\Carbon::today();
        $totalQty   = $batches->sum('quantity');
        $totalValue = $batches->sum(fn($b) => $b->quantity * $b->purchase_price);
        $expiredCnt = $batches->filter(fn($b) => $b->expiry_date && $b->expiry_date->isPast())->count();
        $normalCnt  = $batches->filter(function($b) use ($now) {
            $isExpired = $b->expiry_date && $b->expiry_date->isPast();
            $isNear    = $b->expiry_date && !$isExpired && $b->expiry_date->diffInDays($now) <= 90;
            return !$isExpired && !$isNear && $b->quantity > 0;
        })->count();
    @endphp

    <div class="summary">
        <div class="summary-box">
            <div class="val">{{ $batches->count() }}</div>
            <div class="lbl">Total Batches</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ number_format($totalQty) }}</div>
            <div class="lbl">Total Units</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ number_format($totalValue, 2) }}</div>
            <div class="lbl">Stock Value (TK)</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ $normalCnt }}</div>
            <div class="lbl">Normal</div>
        </div>
        <div class="summary-box">
            <div class="val">{{ $expiredCnt }}</div>
            <div class="lbl">Expired</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Drug Name</th>
                <th>Category</th>
                <th>Batch No</th>
                <th>Expiry Date</th>
                <th>Store</th>
                <th style="text-align:right;">Qty</th>
                <th style="text-align:right;">Reorder</th>
                <th style="text-align:right;">Unit Cost</th>
                <th style="text-align:right;">Stock Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $i => $batch)
                @php
                    $medicine     = $batch->medicine;
                    $reorderLevel = (int) ($medicine->reorder_level ?? 0);
                    $isExpired    = $batch->expiry_date && $batch->expiry_date->isPast();
                    $isNearExpiry = $batch->expiry_date && !$isExpired && $batch->expiry_date->diffInDays($now) <= 90;
                    $isOutOfStock = $batch->quantity <= 0;
                    $isLowStock   = !$isOutOfStock && $reorderLevel > 0 && $batch->quantity <= $reorderLevel;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $medicine->medicine_name }}</strong></td>
                    <td>{{ $medicine->category->name ?? '—' }}</td>
                    <td class="font-monospace">{{ $batch->batch_no }}</td>
                    <td>{{ $batch->expiry_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $batch->store }}</td>
                    <td style="text-align:right;">{{ number_format($batch->quantity) }}</td>
                    <td style="text-align:right;">{{ $reorderLevel > 0 ? number_format($reorderLevel) : '—' }}</td>
                    <td style="text-align:right;">{{ number_format($batch->purchase_price, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($batch->quantity * $batch->purchase_price, 2) }}</td>
                    <td>
                        @if($isExpired)
                            <span class="badge badge-danger">Expired</span>
                        @elseif($isOutOfStock)
                            <span class="badge badge-out">Out of Stock</span>
                        @elseif($isNearExpiry)
                            <span class="badge badge-warning">Near Expiry</span>
                        @elseif($isLowStock)
                            <span class="badge badge-warning">Low Stock</span>
                        @else
                            <span class="badge badge-normal">Normal</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center;padding:16px;color:#999;">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Hospital Management System &mdash; Pharmacy Inventory Report &mdash; {{ now()->format('d M Y') }}</div>

    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 24px;background:#1a237e;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">Print</button>
        <button onclick="window.close()" style="padding:8px 24px;background:#eee;color:#333;border:none;border-radius:4px;cursor:pointer;font-size:13px;margin-left:8px;">Close</button>
    </div>

    <script>window.addEventListener('load', function() { window.print(); });</script>
</body>
</html>
