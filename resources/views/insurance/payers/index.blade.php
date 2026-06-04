@extends('backend.layouts.master')
@section('title','Insurance Payers')
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between">
        <h1 class="app-page-title">Insurance Payers</h1>
        @can('insurance.payer.manage') <a href="{{ route('insurance.payers.create') }}" class="btn btn-primary">Add Payer</a> @endcan
    </div>
    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    <div class="card mt-3"><table class="table mb-0">
        <thead><tr><th>Code</th><th>Name</th><th>Type</th><th class="text-end">Default disc %</th><th>Pre-auth?</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse ($payers as $p)
                <tr>
                    <td><code>{{ $p->code }}</code></td>
                    <td>{{ $p->name }}</td>
                    <td><span class="badge bg-info-soft">{{ $p->type }}</span></td>
                    <td class="text-end">{{ number_format((float) $p->default_discount_percent,2) }}</td>
                    <td>{{ $p->pre_auth_required ? 'Yes' : 'No' }}</td>
                    <td>@if($p->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td>
                    <td class="text-end">
                        @can('insurance.payer.manage') <a href="{{ route('insurance.payers.edit',$p) }}" class="btn btn-sm btn-outline-primary">Edit</a> @endcan
                    </td>
                </tr>
            @empty <tr><td colspan="7" class="text-center text-muted py-3">No payers yet.</td></tr> @endforelse
        </tbody>
    </table></div>
    <div class="mt-2">{{ $payers->links() }}</div>
</div>
@endsection
