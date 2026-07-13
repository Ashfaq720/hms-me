@extends('backend.layouts.master')
@section('title','OT Rooms')
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between align-items-center">
        <h1 class="app-page-title">OT Rooms &amp; Resources</h1>
        <a href="{{ route('ot.rooms.create') }}" class="btn btn-primary">+ Add Room</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive">
        <table class="table mb-0"><thead class="table-light">
            <tr><th>Code</th><th>Name</th><th>Type</th><th>Status</th><th>Emergency</th><th>Equipment</th><th></th></tr>
        </thead><tbody>
            @forelse($rooms as $r)
                <tr>
                    <td>{{ $r->code }}</td><td>{{ $r->name }}</td><td>{{ $r->type ?? '—' }}</td>
                    <td><span class="badge bg-{{ $r->status === 'available' ? 'success' : ($r->status === 'cleaning' ? 'warning' : 'danger') }}">{{ $r->status }}</span></td>
                    <td>{{ $r->is_emergency ? 'Yes' : 'No' }}</td>
                    <td>{{ $r->equipments->count() }}</td>
                    <td class="text-end">
                        <a href="{{ route('ot.rooms.edit', $r->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        <form action="{{ route('ot.rooms.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No rooms.</td></tr>
            @endforelse
        </tbody></table>
    </div></div>
</div>
@endsection
