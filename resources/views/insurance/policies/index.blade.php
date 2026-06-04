@extends('backend.layouts.master')
@section('title', 'Insurance Policies')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-shield-check"></i> Insurance Policies</h4>
        <span class="badge bg-primary p-2">{{ $policies->total() }} policies</span>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Policy No</th><th>Payer</th><th>Patient</th><th>Plan</th>
                        <th>Valid From</th><th>Valid To</th>
                        <th class="text-end">Coverage</th><th class="text-end">Copay %</th>
                        <th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($policies as $p)
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td><strong>{{ $p->policy_no }}</strong></td>
                        <td>{{ optional($p->payer)->name ?? '—' }}</td>
                        <td>{{ optional($p->patient)->patient_name ?? '—' }}<br>
                            <small class="text-muted">{{ optional($p->patient)->mrn }}</small></td>
                        <td>{{ $p->plan_name }}</td>
                        <td>{{ $p->valid_from?->toDateString() }}</td>
                        <td>{{ $p->valid_to?->toDateString() }}</td>
                        <td class="text-end">৳ {{ number_format((float) $p->coverage_limit, 2) }}</td>
                        <td class="text-end">{{ $p->copay_percent }}%</td>
                        <td><span class="badge bg-{{ $p->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($p->status) }}</span></td>
                        <td>
                            <a href="{{ route('insurance.policies.show', $p->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">No policies yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $policies->links() }}</div>
    </div>
</div>
@endsection
