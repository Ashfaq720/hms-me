@extends('backend.layouts.master')
@section('title','Cancellations')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Cancellations</h1>
    @include('ot.reports._filter')
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Schedule</th><th>Patient</th><th>Room</th><th>Date</th><th>Reason</th></tr></thead>
        <tbody>
            @forelse($cancellations as $c)
                <tr>
                    <td>{{ $c->schedule_no }}</td>
                    <td>{{ optional($c->surgeryRequest?->patient)->patient_name }}</td>
                    <td>{{ optional($c->room)->name }}</td>
                    <td>{{ $c->scheduled_start?->format('Y-m-d') }}</td>
                    <td>{{ $c->cancellation_reason }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No cancellations.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
</div>
@endsection
