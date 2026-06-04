@extends('backend.layouts.master')
@section('title','Surgery Types')
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between"><h1 class="app-page-title">Surgery Types</h1>
        <a href="{{ route('ot.setup.surgery-types.create') }}" class="btn btn-primary">+ Add</a></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Name</th><th>Category</th><th>Duration</th><th>Total Charge</th><th>Active</th><th></th></tr></thead>
        <tbody>
            @forelse($items as $i)
                <tr>
                    <td>{{ $i->name }}</td>
                    <td>{{ optional($i->category)->name ?? '—' }}</td>
                    <td>{{ $i->standard_duration_minutes }} min</td>
                    <td>{{ number_format($i->ot_room_charge + $i->surgeon_charge + $i->anesthesia_charge + $i->recovery_charge, 2) }}</td>
                    <td>{{ $i->is_active ? 'Yes' : 'No' }}</td>
                    <td class="text-end">
                        <a href="{{ route('ot.setup.surgery-types.edit', $i->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        <form action="{{ route('ot.setup.surgery-types.destroy', $i->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">×</button></form>
                    </td>
                </tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-3">None defined.</td></tr>@endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection
