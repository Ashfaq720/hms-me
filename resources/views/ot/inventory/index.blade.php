@extends('backend.layouts.master')
@section('title','OT Inventory')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">OT Inventory — Pending Deductions</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>When</th><th>Schedule</th><th>Item</th><th>Type</th><th>Qty</th><th></th></tr></thead>
        <tbody>
            @forelse($usages as $u)
                <tr>
                    <td>{{ $u->used_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ optional($u->schedule)->schedule_no }}</td>
                    <td>{{ $u->item_name }}</td>
                    <td>{{ $u->type }}</td>
                    <td>{{ $u->quantity }} {{ $u->unit }}</td>
                    <td class="text-end">
                        <form action="{{ route('ot.inventory.deduct', $u->id) }}" method="POST" class="d-inline">@csrf
                            <button class="btn btn-sm btn-success">Mark Deducted</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">All deductions up to date.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $usages->links() }}</div>
</div>
@endsection
