@extends('backend.layouts.master')
@section('title', 'OT Transfers')
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3"><h1 class="app-page-title">Patient Transfers</h1></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    @if($readySchedules->count() > 0)
        <div class="card mb-3">
            <div class="card-header"><strong>Ready for OT — Initiate Transfer</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Schedule</th><th>Patient</th><th>Room</th><th>Time</th><th></th></tr></thead>
                    <tbody>
                        @foreach($readySchedules as $s)
                            <tr>
                                <td>{{ $s->schedule_no }}</td>
                                <td>{{ optional($s->surgeryRequest?->patient)->patient_name }}</td>
                                <td>{{ optional($s->room)->name }}</td>
                                <td>{{ $s->scheduled_start?->format('H:i') }}</td>
                                <td class="text-end">
                                    <form action="{{ route('ot.transfers.initiate', $s->id) }}" method="POST" class="d-inline">@csrf
                                        <input type="hidden" name="direction" value="to_ot">
                                        <button class="btn btn-sm btn-primary">Initiate to OT</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><strong>Recent Transfers</strong></div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light"><tr><th>Schedule</th><th>Patient</th><th>Direction</th><th>Initiated</th><th>Arrived</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($transfers as $t)
                        <tr>
                            <td>{{ optional($t->schedule)->schedule_no }}</td>
                            <td>{{ optional($t->schedule?->surgeryRequest?->patient)->patient_name }}</td>
                            <td><span class="badge bg-info">{{ str_replace('_',' ', strtoupper($t->direction)) }}</span></td>
                            <td>{{ $t->initiated_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $t->arrived_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $t->status }}</span></td>
                            <td class="text-end">
                                @if(! $t->arrived_at)
                                    <form action="{{ route('ot.transfers.arrive', $t->id) }}" method="POST" class="d-inline">@csrf
                                        <button class="btn btn-sm btn-success">Mark Arrived</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No transfers.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $transfers->links() }}</div>
</div>
@endsection
