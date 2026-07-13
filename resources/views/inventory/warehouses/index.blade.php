@extends('backend.layouts.master')
@section('title', 'Warehouses')
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between">
        <h1 class="app-page-title">Warehouses</h1>
        @can('inventory.warehouse.manage')
            <a href="{{ route('inventory.warehouses.create') }}" class="btn btn-primary">Add Warehouse</a>
        @endcan
    </div>
    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    <div class="card mt-3"><table class="table mb-0">
        <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Location</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse ($warehouses as $w)
                <tr>
                    <td><code>{{ $w->code }}</code></td><td>{{ $w->name }}</td><td>{{ $w->type }}</td>
                    <td>{{ $w->location }}</td>
                    <td>@if($w->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td>
                    <td class="text-end">
                        @can('inventory.warehouse.manage')
                            <a href="{{ route('inventory.warehouses.edit', $w) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        @endcan
                    </td>
                </tr>
            @empty <tr><td colspan="6" class="text-center text-muted py-3">No warehouses.</td></tr> @endforelse
        </tbody>
    </table></div>
    <div class="mt-2">{{ $warehouses->links() }}</div>
</div>
@endsection
