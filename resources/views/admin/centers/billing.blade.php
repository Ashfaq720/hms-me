@extends('backend.layouts.master')
@section('title', 'Billing Center')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-cash-stack text-success"></i> Billing Center</h4>
            <small class="text-muted">All bill types · unified bills · payments · claims</small>
        </div>
        <a href="{{ route('admin.centers.master-data') }}" class="btn btn-sm btn-outline-primary">← Master Data</a>
    </div>

    <div class="row g-2 mb-3">
        @php $tiles = [
            ['Total Bills',  $stats['bills_total'], 'primary',  'receipt',    'billing.bills.index'],
            ['Paid',         $stats['paid'],        'success',  'check-circle','billing.bills.index'],
            ['Partial',      $stats['partial'],     'warning',  'hourglass-split','billing.bills.index'],
            ['Draft',        $stats['draft'],       'secondary','pencil',     'billing.bills.index'],
            ['Payments',     $stats['payments'],    'info',     'credit-card','billing.bills.index'],
            ['Claims',       $stats['claims'],      'danger',   'shield-check','insurance.claims.index'],
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

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card border-0 shadow-sm bg-info bg-opacity-10 p-3"><small class="text-info">Grand Total Billed</small><h4 class="mb-0">৳ {{ number_format($stats['grand_total'], 2) }}</h4></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm bg-success bg-opacity-10 p-3"><small class="text-success">Collected</small><h4 class="mb-0">৳ {{ number_format($stats['paid_total'], 2) }}</h4></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm bg-danger bg-opacity-10 p-3"><small class="text-danger">Outstanding</small><h4 class="mb-0">৳ {{ number_format($stats['outstanding'], 2) }}</h4></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">Revenue by Bill Type</h6></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light"><tr><th>Type</th><th class="text-center">Bills</th><th class="text-end">Grand ৳</th><th class="text-end">Paid ৳</th><th class="text-end">Outstanding ৳</th><th>Drill-through</th></tr></thead>
                <tbody>
                @php $linkMap = ['er' => 'emergency', 'lab' => 'pathology', 'radiology' => 'radiology', 'ambulance' => 'utility', 'other' => 'utility']; @endphp
                @forelse ($byType as $r)
                    <tr>
                        <td><span class="badge bg-info bg-opacity-15 text-info">{{ strtoupper($r->bill_type ?: '—') }}</span></td>
                        <td class="text-center">{{ $r->n }}</td>
                        <td class="text-end">{{ number_format((float) $r->gross, 2) }}</td>
                        <td class="text-end text-success">{{ number_format((float) $r->paid, 2) }}</td>
                        <td class="text-end text-danger">{{ number_format((float) $r->gross - (float) $r->paid, 2) }}</td>
                        <td>
                            @if ($r->bill_type === 'ipd' && \Illuminate\Support\Facades\Route::has('billing.ipd-billing.index'))
                                <a href="{{ route('billing.ipd-billing.index') }}" class="badge bg-primary bg-opacity-15 text-primary text-decoration-none">IPD billing →</a>
                            @elseif ($r->bill_type === 'opd' && \Illuminate\Support\Facades\Route::has('billing.opd-billing.index'))
                                <a href="{{ route('billing.opd-billing.index') }}" class="badge bg-primary bg-opacity-15 text-primary text-decoration-none">OPD billing →</a>
                            @elseif (isset($linkMap[$r->bill_type]))
                                <a href="{{ route('billing.category.index', $linkMap[$r->bill_type]) }}" class="badge bg-primary bg-opacity-15 text-primary text-decoration-none">{{ ucfirst($linkMap[$r->bill_type]) }} →</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No bills yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
