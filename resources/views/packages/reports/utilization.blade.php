@extends('backend.layouts.master')
@section('title', 'Package Utilization Report')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-graph-up"></i> Package Utilization (Allowed vs Consumed vs Extras)</h4>
        <a href="{{ route('packages.reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Reports</a>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light"><tr><th>Package</th><th>Service</th><th class="text-center">Allowed</th><th class="text-center">Consumed</th><th class="text-center">Extras (chargeable)</th><th>Util %</th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                @php $pct = $r->allowed > 0 ? min(100, ($r->consumed / $r->allowed) * 100) : 0; @endphp
                <tr>
                    <td><code>{{ $r->code }}</code> <small>{{ $r->name }}</small></td>
                    <td>{{ $r->description }}</td>
                    <td class="text-center">{{ rtrim(rtrim(number_format($r->allowed, 2), '0'), '.') }}</td>
                    <td class="text-center">{{ rtrim(rtrim(number_format($r->consumed, 2), '0'), '.') }}</td>
                    <td class="text-center">@if ($r->extras > 0)<span class="badge bg-warning text-dark">{{ rtrim(rtrim(number_format($r->extras, 2), '0'), '.') }}</span>@else — @endif</td>
                    <td>
                        <div class="progress" style="height:14px;">
                            <div class="progress-bar bg-{{ $pct > 80 ? 'danger' : ($pct > 50 ? 'warning' : 'success') }}" style="width:{{ $pct }}%">{{ number_format($pct, 0) }}%</div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No utilization data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
