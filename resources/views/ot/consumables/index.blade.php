@extends('backend.layouts.master')
@section('title','Consumables Usage')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Consumables &amp; Instrument Usage</h1>
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>When</th><th>Schedule</th><th>Patient</th><th>Item</th><th>Type</th><th>Qty</th><th>Amount</th><th>Billed</th><th>Stock Out</th></tr></thead>
        <tbody>
            @forelse($usages as $u)
                <tr>
                    <td>{{ $u->used_at?->format('Y-m-d H:i') ?? $u->created_at?->format('Y-m-d') }}</td>
                    <td><a href="{{ route('ot.consumables.show', $u->surgery_schedule_id) }}">{{ optional($u->schedule)->schedule_no }}</a></td>
                    <td>{{ optional($u->schedule?->surgeryRequest?->patient)->patient_name }}</td>
                    <td>{{ $u->item_name }}</td>
                    <td><span class="badge bg-info">{{ $u->type }}</span></td>
                    <td>{{ $u->quantity }} {{ $u->unit }}</td>
                    <td>{{ number_format($u->amount, 2) }}</td>
                    <td>{!! $u->is_billed ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                    <td>{!! $u->inventory_deducted ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-3">No usage entries.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $usages->links() }}</div>
</div>
@endsection
