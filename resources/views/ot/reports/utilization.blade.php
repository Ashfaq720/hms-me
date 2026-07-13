@extends('backend.layouts.master')
@section('title','OT Utilization')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">OT Room Utilization</h1>
    @include('ot.reports._filter')
    <div class="card"><div class="table-responsive"><table class="table mb-0">
        <thead class="table-light"><tr><th>Room</th><th>Cases</th><th>Scheduled (hr)</th></tr></thead>
        <tbody>
            @forelse($utilization as $u)
                <tr>
                    <td>{{ optional($u->room)->name ?? 'Unknown' }}</td>
                    <td>{{ $u->case_count }}</td>
                    <td>{{ round(($u->scheduled_minutes ?? 0) / 60, 1) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
</div>
@endsection
