@extends('backend.layouts.master')

@section('title', 'Schedule ' . $schedule->schedule_no)

@section('content')
<div class="container-fluid">
    <div class="app-page-head d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0">
                {{ $schedule->schedule_no }}
                <span class="badge {{ $schedule->status_badge_class }}">{{ $schedule->status }}</span>
                @if($schedule->emergency_fast_track)<span class="badge bg-danger">EMERGENCY</span>@endif
            </h1>
            <div class="text-muted">
                {{ optional($schedule->surgeryRequest?->patient)->patient_name }}
                · {{ optional($schedule->surgeryRequest?->surgeryType)->name }}
                · {{ optional($schedule->room)->name }}
                · {{ $schedule->scheduled_start?->format('Y-m-d H:i') }} → {{ $schedule->scheduled_end?->format('H:i') }}
                @if($schedule->buffer_minutes > 0)
                    <span class="badge bg-light text-dark border ms-1" title="Room blocked for cleaning {{ $schedule->buffer_minutes }} min after surgery end">
                        +{{ $schedule->buffer_minutes }} min cleaning buffer
                    </span>
                @endif
            </div>
        </div>
        <a href="{{ route('ot.schedules.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    {{-- Status Timeline --}}
    <div class="card mb-3">
        <div class="card-header"><strong>Status Timeline</strong></div>
        <div class="card-body">
            @php
                $flow = ['Scheduled','Pre-Op Pending','Ready for OT','Transfer Started','Patient Received in OT','Anesthesia Started','Surgery Running','Surgery Completed','In Recovery','Transferred Back','Closed'];
                $currentIdx = array_search($schedule->status, $flow);
            @endphp
            <div class="d-flex flex-wrap gap-1">
                @foreach($flow as $idx => $step)
                    @php
                        $active = $currentIdx !== false && $idx <= $currentIdx;
                        $current = $step === $schedule->status;
                    @endphp
                    <span class="badge {{ $current ? 'bg-primary' : ($active ? 'bg-success' : 'bg-light text-dark border') }} px-2 py-2">
                        {{ $idx + 1 }}. {{ $step }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick action buttons --}}
    <div class="card mb-3">
        <div class="card-header"><strong>Workflow Actions</strong></div>
        <div class="card-body d-flex flex-wrap gap-2">
            <a href="{{ route('ot.pre-op.show', $schedule->id) }}" class="btn btn-outline-info"><i class="bi bi-check2-square"></i> Pre-Op</a>
            <a href="{{ route('ot.teams.show', $schedule->id) }}" class="btn btn-outline-secondary"><i class="bi bi-people"></i> Team</a>

            @if($schedule->status === 'Ready for OT')
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transferModal"><i class="bi bi-arrow-right"></i> Transfer to OT</button>
            @endif

            @if($schedule->status === 'Patient Received in OT')
                <form method="POST" action="{{ route('ot.anesthesia.start', $schedule->id) }}">@csrf
                    <button class="btn btn-warning"><i class="bi bi-droplet"></i> Start Anesthesia</button>
                </form>
            @endif

            @if($schedule->status === 'Anesthesia Started')
                <form method="POST" action="{{ route('ot.intra-op.start', $schedule->id) }}">@csrf
                    <button class="btn btn-warning"><i class="bi bi-activity"></i> Start Surgery</button>
                </form>
            @endif

            @if($schedule->status === 'Surgery Running')
                <form method="POST" action="{{ route('ot.intra-op.complete', $schedule->id) }}">@csrf
                    <button class="btn btn-success"><i class="bi bi-check2-circle"></i> Complete Surgery</button>
                </form>
            @endif

            @if($schedule->status === 'Surgery Completed')
                <form method="POST" action="{{ route('ot.pacu.admit', $schedule->id) }}" class="d-flex gap-1">@csrf
                    <input type="text" name="bed_no" placeholder="PACU bed" class="form-control form-control-sm" style="width:120px">
                    <button class="btn btn-success"><i class="bi bi-bandaid"></i> Admit to PACU</button>
                </form>
            @endif

            <a href="{{ route('ot.anesthesia.show', $schedule->id) }}" class="btn btn-outline-warning"><i class="bi bi-droplet"></i> Anesthesia</a>
            <a href="{{ route('ot.intra-op.show', $schedule->id) }}" class="btn btn-outline-warning"><i class="bi bi-activity"></i> Intra-Op</a>
            <a href="{{ route('ot.consumables.show', $schedule->id) }}" class="btn btn-outline-secondary"><i class="bi bi-box-seam"></i> Consumables</a>
            <a href="{{ route('ot.post-op.show', $schedule->id) }}" class="btn btn-outline-secondary"><i class="bi bi-journal-medical"></i> Post-Op</a>
            <a href="{{ route('ot.pacu.show', $schedule->id) }}" class="btn btn-outline-success"><i class="bi bi-bandaid"></i> PACU</a>
            <a href="{{ route('ot.billing.show', $schedule->id) }}" class="btn btn-outline-info"><i class="bi bi-receipt"></i> Billing</a>

            @if(! in_array($schedule->status, ['Cancelled','Closed']))
                <button class="btn btn-outline-danger ms-auto" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel</button>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><strong>Team Members</strong></div>
                <ul class="list-group list-group-flush">
                    @forelse($schedule->teamMembers as $m)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ ucwords(str_replace('_',' ',$m->role)) }}:</strong>
                                    Staff #{{ $m->staff_id }} ({{ $m->staff_type }})
                                    @if($m->specialization)
                                        <span class="badge bg-info ms-1">{{ $m->specialization }}</span>
                                    @endif
                                </div>
                                <div>
                                    @if($m->is_primary)<span class="badge bg-primary">Primary</span>@endif
                                    @if($m->released_at)<span class="badge bg-secondary">Released</span>@endif
                                </div>
                            </div>
                            @if($m->released_reason)<small class="text-muted d-block">{{ $m->released_reason }}</small>@endif
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">No team members assigned.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><strong>Equipment Reserved</strong></div>
                <ul class="list-group list-group-flush">
                    @forelse($schedule->equipments as $e)
                        <li class="list-group-item">{{ optional($e->equipment)->name }} ({{ optional($e->equipment)->code }})</li>
                    @empty
                        <li class="list-group-item text-muted text-center">No equipment reserved.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('ot.transfers.initiate', $schedule->id) }}" method="POST">@csrf
            <div class="modal-header"><h5 class="modal-title">Transfer Patient to OT</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="direction" value="to_ot">
                <div class="mb-2"><label class="form-label">From</label><input name="from_location" class="form-control" placeholder="Ward / Bed"></div>
                <div class="mb-2"><label class="form-label">To</label><input name="to_location" class="form-control" value="{{ optional($schedule->room)->name }}" readonly></div>
                <div class="mb-2"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary">Initiate Transfer</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('ot.schedules.cancel', $schedule->id) }}" method="POST">@csrf
            <div class="modal-header"><h5 class="modal-title">Cancel Schedule</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><label class="form-label">Reason *</label><textarea name="reason" class="form-control" required></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-danger">Cancel Surgery</button></div>
        </form>
    </div>
</div>
@endsection
