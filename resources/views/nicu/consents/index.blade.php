@extends('backend.layouts.master')
@section('title', 'NICU Consents')
@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="bi bi-file-earmark-check"></i> Parent Consents</h4>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Baby</th><th>Type</th><th>Guardian</th><th>Relation</th><th>Phone</th><th>Signed</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($consents as $c)
                    <tr>
                        <td>{{ optional(optional($c->admission)->patient)->patient_name ?? '—' }}</td>
                        <td>{{ $c->consent_type }}</td>
                        <td>{{ $c->guardian_name }}</td>
                        <td>{{ $c->guardian_relation }}</td>
                        <td>{{ $c->guardian_phone }}</td>
                        <td>{{ $c->signed_at?->format('Y-m-d H:i') }}</td>
                        <td><span class="badge bg-{{ $c->status === 'valid' ? 'success' : 'secondary' }}">{{ ucfirst($c->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No consents captured</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($consents, 'links'))<div class="p-3">{{ $consents->links() }}</div>@endif
    </div>
</div>
@endsection
