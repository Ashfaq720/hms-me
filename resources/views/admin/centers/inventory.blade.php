@extends('backend.layouts.master')
@section('title', 'Inventory Hub')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-boxes text-warning"></i> Inventory Hub</h4>
            <small class="text-muted">Items · Warehouses · Stock Movements · Pharmacy · Medical Consumables</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.centers.master-data') }}" class="btn btn-sm btn-outline-primary">← Master Data</a>
            <a href="{{ route('admin.centers.equipment') }}" class="btn btn-sm btn-outline-info">Equipment Center</a>
        </div>
    </div>

    <div class="row g-2 mb-3">
        @php $tiles = [
            ['Items',           $stats['items_total'],   'primary',  'box-seam',     'inventory.items.index'],
            ['Warehouses',      $stats['warehouses'],    'info',     'building',     'inventory.warehouses.index'],
            ['Stock Batches',   $stats['batches'],       'success',  'collection',   'inventory.items.index'],
            ['Movements',       $stats['movements'],     'danger',   'arrow-left-right', 'inventory.movements.index'],
            ['Medicines',       $stats['medicines'],     'warning',  'capsule',      'admin.medicines.index'],
            ['Consumables',     $stats['consumables'],   'secondary','bag',          'inventory.items.index'],
            ['Controlled Drugs',$stats['controlled'],    'danger',   'shield-lock',  'admin.pharmacy.controlled-drugs'],
            ['Pharmacy Trx',    $stats['pharma_tx'],     'primary',  'receipt',      null],
        ]; @endphp
        @foreach ($tiles as [$label, $value, $colour, $icon, $route])
            <div class="col-md-3 col-6">
                <a href="{{ $route && \Illuminate\Support\Facades\Route::has($route) ? route($route) : '#' }}"
                   class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100" style="border-left:4px solid var(--bs-{{ $colour }}) !important;">
                        <div class="card-body py-2 px-3">
                            <small class="text-{{ $colour }}"><i class="bi bi-{{ $icon }}"></i> {{ $label }}</small>
                            <h4 class="mb-0 mt-1">{{ $value }}</h4>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-arrow-left-right"></i> Recent Stock Movements</h6>
                    <a href="{{ route('inventory.movements.index') }}" class="btn btn-sm btn-outline-primary">All movements</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light"><tr>
                            <th>When</th><th>Item</th><th class="text-center">Direction</th><th class="text-end">Qty</th><th class="text-end">Balance</th><th>Reason</th>
                        </tr></thead>
                        <tbody>
                        @forelse ($recent as $m)
                            <tr>
                                <td><small>{{ \Carbon\Carbon::parse($m->performed_at ?? $m->created_at)->format('Y-m-d H:i') }}</small></td>
                                <td><strong>{{ $m->item_code ?? '?' }}</strong> · {{ $m->item_name ?? '—' }}</td>
                                <td class="text-center">
                                    @php $col = $m->direction === 'IN' ? 'success' : ($m->direction === 'OUT' ? 'danger' : 'secondary'); @endphp
                                    <span class="badge bg-{{ $col }}">{{ $m->direction }}</span>
                                </td>
                                <td class="text-end">{{ $m->quantity }}</td>
                                <td class="text-end">{{ $m->balance_after }}</td>
                                <td><small>{{ $m->reason }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No movements yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h6 class="mb-0">Movement Breakdown</h6></div>
                <ul class="list-group list-group-flush">
                    @foreach (['IN' => 'success', 'OUT' => 'danger', 'TRANSFER' => 'info', 'ADJUST' => 'warning'] as $dir => $col)
                        <li class="list-group-item d-flex justify-content-between">
                            <span><span class="badge bg-{{ $col }}">{{ $dir }}</span> direction</span>
                            <strong>{{ $byDirection[$dir] ?? 0 }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
