@extends('backend.layouts.master')
@section('title','Consumables Report')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Consumables Usage</h1>
    @include('ot.reports._filter')
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Date</th><th>Schedule</th><th>Item</th><th>Type</th><th>Qty</th><th>Amount</th></tr></thead>
        <tbody>
            @forelse($usages as $u)
                <tr>
                    <td>{{ $u->created_at?->format('Y-m-d') }}</td>
                    <td>{{ optional($u->schedule)->schedule_no }}</td>
                    <td>{{ $u->item_name }}</td>
                    <td>{{ $u->type }}</td>
                    <td>{{ $u->quantity }} {{ $u->unit }}</td>
                    <td>{{ number_format($u->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No usage data.</td></tr>
            @endforelse
        </tbody>
        @if($usages->count() > 0)
            <tfoot><tr><th colspan="5" class="text-end">Total</th><th>{{ number_format($usages->sum('amount'), 2) }}</th></tr></tfoot>
        @endif
    </table></div></div>
</div>
@endsection
