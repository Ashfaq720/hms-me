@extends('backend.layouts.master')
@section('title','OT Billing')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">OT Billing</h1>
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Schedule</th><th>Patient</th><th>Procedure</th><th>Status</th><th>Consumables</th><th></th></tr></thead>
        <tbody>
            @forelse($schedules as $s)
                <tr>
                    <td>{{ $s->schedule_no }}</td>
                    <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                    <td>{{ optional($s->surgeryRequest?->surgeryType)->name ?? '—' }}</td>
                    <td><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                    <td>{{ number_format($s->consumableUsages->sum('amount'), 2) }}</td>
                    <td class="text-end"><a href="{{ route('ot.billing.show', $s->id) }}" class="btn btn-sm btn-outline-primary">Bill</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Nothing to bill.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $schedules->links() }}</div>
</div>
@endsection
