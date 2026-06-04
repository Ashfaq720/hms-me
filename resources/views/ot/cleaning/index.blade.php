@extends('backend.layouts.master')
@section('title','Cleaning & Sterilization')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Cleaning &amp; Sterilization</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-3"><div class="card-header"><strong>Start Cleaning Cycle</strong></div>
        <div class="card-body row g-3">
            @foreach($rooms as $room)
                <div class="col-md-3">
                    <div class="border rounded p-2 text-center">
                        <strong>{{ $room->name }}</strong>
                        <div><span class="badge bg-{{ $room->status === 'available' ? 'success' : 'warning' }}">{{ $room->status }}</span></div>
                        <form action="{{ route('ot.cleaning.start', $room->id) }}" method="POST" class="mt-2">@csrf
                            <select name="cleaning_type" class="form-select form-select-sm mb-1">
                                <option value="routine">Routine</option><option value="terminal">Terminal</option>
                                <option value="emergency">Emergency</option>
                            </select>
                            <button class="btn btn-sm btn-warning w-100">Start</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card"><div class="card-header"><strong>Cleaning Log</strong></div>
        <div class="table-responsive"><table class="table mb-0">
            <thead class="table-light"><tr><th>Room</th><th>Type</th><th>Started</th><th>Completed</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ optional($log->room)->name }}</td>
                        <td>{{ $log->cleaning_type }}</td>
                        <td>{{ $log->started_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $log->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>{{ $log->is_complete ? 'Complete' : 'In progress' }}</td>
                        <td class="text-end">
                            @if(! $log->is_complete)
                                <button type="button" class="btn btn-sm btn-success"
                                        data-bs-toggle="modal" data-bs-target="#cleaningCompleteModal-{{ $log->id }}">
                                    Mark Complete
                                </button>
                            @endif
                        </td>
                    </tr>

                    @if(! $log->is_complete)
                        {{-- Per-log completion modal: captures cleaning checklist before marking done. --}}
                        <div class="modal fade" id="cleaningCompleteModal-{{ $log->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <form action="{{ route('ot.cleaning.complete', $log->id) }}" method="POST">@csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Complete Cleaning — {{ optional($log->room)->name }}
                                                <span class="badge bg-light text-dark border ms-1">{{ ucfirst($log->cleaning_type) }}</span>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info py-2 small mb-3">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Confirm each step performed during this cleaning cycle. All items must be ticked before completion.
                                            </div>
                                            <div class="row g-2">
                                                @php
                                                    $items = [
                                                        'surfaces_disinfected' => 'Surfaces disinfected (table, lights, walls)',
                                                        'floor_mopped'         => 'Floor mopped with hospital-grade disinfectant',
                                                        'instruments_removed'  => 'Used instruments removed for CSSD',
                                                        'linen_changed'        => 'Linen / drapes changed',
                                                        'biohazard_disposed'   => 'Biohazard / sharps disposed per protocol',
                                                        'air_change_completed' => 'Air change / fumigation completed',
                                                        'consumables_restocked'=> 'Consumables restocked for next case',
                                                        'equipment_checked'    => 'Equipment checked & reset',
                                                    ];
                                                @endphp
                                                @foreach($items as $key => $label)
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-check">
                                                            <input type="hidden" name="checklist[{{ $key }}]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="checklist[{{ $key }}]" value="1"
                                                                   id="ck-{{ $log->id }}-{{ $key }}" required>
                                                            <label class="form-check-label" for="ck-{{ $log->id }}-{{ $key }}">
                                                                {{ $label }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label small mb-1">Remarks (optional)</label>
                                                <textarea name="remarks" rows="2" class="form-control" placeholder="Any deviations, broken items, or notes for the next shift"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check-circle me-1"></i> Confirm & Complete
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No cleaning logs.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
</div>
@endsection
