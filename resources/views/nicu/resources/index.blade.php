@extends('backend.layouts.master')
@section('title', 'NICU Resources')
@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="bi bi-box"></i> NICU Resource Allocations</h4>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>#</th><th>Baby</th><th>Resource</th><th>Bed</th><th>From</th><th>To</th><th>Status</th><th>Reason</th></tr>
                </thead>
                <tbody>
                @forelse ($allocations as $a)
                    <tr>
                        <td>{{ $a->id }}</td>
                        <td>{{ optional(optional($a->admission)->patient)->patient_name ?? '—' }}</td>
                        <td><span class="badge bg-info">{{ $a->resource_type }}</span></td>
                        <td>{{ optional($a->bed)->name ?? '—' }}</td>
                        <td>{{ $a->from?->format('Y-m-d H:i') }}</td>
                        <td>{{ $a->to?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td><span class="badge bg-{{ $a->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($a->status) }}</span></td>
                        <td>{{ $a->reason }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No allocations</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($allocations, 'links'))<div class="p-3">{{ $allocations->links() }}</div>@endif
    </div>
</div>
@endsection
