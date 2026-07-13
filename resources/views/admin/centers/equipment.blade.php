@extends('backend.layouts.master')
@section('title', 'Equipment Center')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-plug text-info"></i> Equipment Center</h4>
            <small class="text-muted">Unified view of all medical equipment — OT · ICU · NICU · CCU · Ambulance</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.centers.master-data') }}" class="btn btn-sm btn-outline-primary">← Master Data</a>
            <a href="{{ route('admin.centers.inventory') }}" class="btn btn-sm btn-outline-warning">Inventory →</a>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="row g-2 mb-3">
        @php $tiles = [
            ['OT Equipment',         $stats['ot_equipments'],      'primary',  'scissors',     'ot.setup.equipments.index'],
            ['ICU/CCU/NICU Equipment', $stats['icu_equipment'],    'danger',   'heart-pulse',  null],
            ['Ambulance Equipment',  $stats['amb_equipment'],      'warning',  'truck',        null],
            ['OT Consumables',       $stats['ot_consumables'],     'info',     'box-fill',     'ot.setup.consumables.index'],
            ['Inventory Assets',     $stats['inventory_assets'],   'success',  'tools',        'inventory.items.index'],
            ['Inventory Consumables',$stats['inventory_consumes'], 'secondary','box-seam',     'inventory.items.index'],
        ]; @endphp
        @foreach ($tiles as [$label, $value, $colour, $icon, $route])
            <div class="col-md-2 col-6">
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
        {{-- OT Equipment table --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-scissors text-primary"></i> OT Equipment ({{ count($otEquipment) }})</h6>
                    @if (\Illuminate\Support\Facades\Route::has('ot.setup.equipments.index'))
                        <a href="{{ route('ot.setup.equipments.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Category</th><th>Room</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse ($otEquipment as $e)
                            <tr>
                                <td><code>{{ $e->code }}</code></td>
                                <td>{{ $e->name }}</td>
                                <td><span class="badge bg-info bg-opacity-15 text-info">{{ $e->category }}</span></td>
                                <td><small>{{ $e->room_name ?? '—' }}</small></td>
                                <td><span class="badge bg-{{ $e->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($e->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No OT equipment yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ICU Equipment table --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-heart-pulse text-danger"></i> ICU / CCU / NICU Equipment ({{ count($icuEquipment) }})</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Type</th><th>Unit</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse ($icuEquipment as $e)
                            <tr>
                                <td><code>{{ $e->equipment_code }}</code></td>
                                <td>{{ $e->equipment_name }}</td>
                                <td><span class="badge bg-light text-muted">{{ $e->equipment_type }}</span></td>
                                <td>
                                    @php $col = ['ICU' => 'danger', 'CCU' => 'warning', 'NICU' => 'info'][$e->icu_type] ?? 'secondary'; @endphp
                                    <span class="badge bg-{{ $col }}">{{ $e->icu_type }}</span>
                                </td>
                                <td><span class="badge bg-{{ $e->status === 'Available' ? 'success' : 'secondary' }}">{{ $e->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No ICU equipment yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Breakdowns --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">OT Equipment by Category</h6></div>
                <ul class="list-group list-group-flush">
                    @forelse ($byCategory as $c)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $c->category ?: '— Uncategorised —' }}</span>
                            <span class="badge bg-primary">{{ $c->n }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">No data</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">ICU Equipment by Unit</h6></div>
                <ul class="list-group list-group-flush">
                    @forelse ($byIcuType as $r)
                        <li class="list-group-item d-flex justify-content-between">
                            <span><i class="bi bi-heart-pulse"></i> {{ $r->icu_type ?: '—' }}</span>
                            <span class="badge bg-danger">{{ $r->n }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">No data</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
