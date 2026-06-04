@extends('backend.layouts.master')
@section('title','Consumables / Implants')
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between"><h1 class="app-page-title">Consumables / Implants / Instruments</h1>
        <a href="{{ route('ot.setup.consumables.create') }}" class="btn btn-primary">+ Add</a></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Name</th><th>Code</th><th>Type</th><th>Unit</th><th>Rate</th><th>Implant</th><th>Active</th><th></th></tr></thead>
        <tbody>
            @forelse($items as $i)
                <tr>
                    <td>{{ $i->name }}</td><td>{{ $i->code }}</td><td>{{ $i->type }}</td><td>{{ $i->unit }}</td>
                    <td>{{ number_format($i->rate, 2) }}</td><td>{{ $i->is_implant ? 'Yes' : '—' }}</td><td>{{ $i->is_active ? 'Yes' : 'No' }}</td>
                    <td class="text-end">
                        <a href="{{ route('ot.setup.consumables.edit', $i->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        <form action="{{ route('ot.setup.consumables.destroy', $i->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">×</button></form>
                    </td>
                </tr>
            @empty<tr><td colspan="8" class="text-center text-muted py-3">None defined.</td></tr>@endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection
