@extends('backend.layouts.master')
@section('title','Branches')
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between">
        <h1 class="app-page-title">Branches</h1>
        @can('branch.manage') <a href="{{ route('branches.create') }}" class="btn btn-primary">Add Branch</a> @endcan
    </div>
    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    <div class="card mt-3"><table class="table mb-0">
        <thead><tr><th>Code</th><th>Name</th><th>Org</th><th>Type</th><th>City</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse ($branches as $b)
                <tr>
                    <td><code>{{ $b->code }}</code></td>
                    <td>{{ $b->name }}</td>
                    <td>{{ optional($b->organization)->name }}</td>
                    <td>{{ $b->type }}</td>
                    <td>{{ $b->city }}</td>
                    <td>@if($b->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td>
                    <td class="text-end">
                        @can('branch.manage') <a href="{{ route('branches.edit',$b) }}" class="btn btn-sm btn-outline-primary">Edit</a> @endcan
                    </td>
                </tr>
            @empty <tr><td colspan="7" class="text-center text-muted py-3">No branches.</td></tr> @endforelse
        </tbody>
    </table></div>
    <div class="mt-2">{{ $branches->links() }}</div>
</div>
@endsection
