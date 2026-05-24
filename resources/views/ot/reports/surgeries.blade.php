@extends('backend.layouts.master')
@section('title','Surgeries Report')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Surgeries Report</h1>
    @include('ot.reports._filter')
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Date</th><th>Schedule</th><th>Patient</th><th>Procedure</th><th>Room</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($schedules as $s)
                <tr>
                    <td>{{ $s->scheduled_start?->format('Y-m-d H:i') }}</td>
                    <td>{{ $s->schedule_no }}</td>
                    <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                    <td>{{ optional($s->surgeryRequest?->surgeryType)->name }}</td>
                    <td>{{ optional($s->room)->name }}</td>
                    <td><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No surgeries in range.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
</div>
@endsection
