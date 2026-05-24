@extends('backend.layouts.master')
@section('title','Equipment')
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between"><h1 class="app-page-title">OT Equipment</h1>
        <a href="{{ route('ot.setup.equipments.create') }}" class="btn btn-primary">+ Add</a></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Category</th><th>Room</th><th>Status</th><th>Active</th><th></th></tr></thead>
        <tbody>
            @forelse($equipments as $e)
                <tr>
                    <td>{{ $e->code }}</td><td>{{ $e->name }}</td><td>{{ $e->category }}</td>
                    <td>{{ optional($e->room)->name }}</td>
                    <td><span class="badge bg-{{ $e->status === 'available' ? 'success' : 'warning' }}">{{ $e->status }}</span></td>
                    <td>{{ $e->is_active ? 'Yes' : 'No' }}</td>
                    <td class="text-end">
                        <a href="{{ route('ot.setup.equipments.edit', $e->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        <form action="{{ route('ot.setup.equipments.destroy', $e->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">×</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No equipment.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $equipments->links() }}</div>
</div>
@endsection
