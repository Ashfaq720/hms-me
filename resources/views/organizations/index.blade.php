@extends('backend.layouts.master')
@section('title','Organizations')
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between">
        <h1 class="app-page-title">Organizations</h1>
        @can('organization.manage') <a href="{{ route('organizations.create') }}" class="btn btn-primary">Add Organization</a> @endcan
    </div>
    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    <div class="card mt-3"><table class="table mb-0">
        <thead><tr><th>Code</th><th>Name</th><th>Country</th><th>Currency</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse ($organizations as $o)
                <tr>
                    <td><code>{{ $o->code }}</code></td><td>{{ $o->name }}</td>
                    <td>{{ $o->country }}</td><td>{{ $o->default_currency }}</td>
                    <td>@if($o->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td>
                    <td class="text-end">
                        @can('organization.manage') <a href="{{ route('organizations.edit',$o) }}" class="btn btn-sm btn-outline-primary">Edit</a> @endcan
                    </td>
                </tr>
            @empty <tr><td colspan="6" class="text-center text-muted py-3">No organizations.</td></tr> @endforelse
        </tbody>
    </table></div>
    <div class="mt-2">{{ $organizations->links() }}</div>
</div>
@endsection
