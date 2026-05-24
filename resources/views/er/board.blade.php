@extends('backend.layouts.master')
@section('title', 'ER Patient Tracking Board')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0"><i class="bi bi-kanban"></i> ER Patient Tracking Board</h4>
            <small class="text-muted">Real-time patient movement · last 48 hours</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('er.dashboard') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="{{ route('front_desk.er_registration') }}" class="btn btn-sm btn-danger"><i class="bi bi-plus-lg"></i> New Registration</a>
        </div>
    </div>

    @php $statusColours = [
        'WAITING' => 'warning',
        'UNDER_ASSESSMENT' => 'info',
        'IN_TREATMENT' => 'primary',
        'OBSERVATION' => 'info',
        'TRANSFERRED' => 'success',
        'DISCHARGED' => 'success',
        'EXPIRED' => 'dark',
    ]; @endphp

    <div class="kanban-board d-flex gap-2 overflow-auto pb-3">
        @foreach ($statuses as $s)
            @php $colour = $statusColours[$s] ?? 'secondary'; $rows = $grouped[$s] ?? collect(); @endphp
            <div class="kanban-col flex-shrink-0" style="width:280px;">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-{{ $colour }} bg-opacity-10 text-{{ $colour }} d-flex justify-content-between align-items-center">
                        <strong>{{ str_replace('_', ' ', $s) }}</strong>
                        <span class="badge bg-{{ $colour }}">{{ $rows->count() }}</span>
                    </div>
                    <div class="card-body p-2" style="min-height:200px; max-height:70vh; overflow-y:auto;">
                        @forelse ($rows as $p)
                            @php $tri = $p->latestTriage; $triColour = $tri ? \App\Models\Er\ErTriage::levelColour($tri->triage_level) : 'secondary'; @endphp
                            <a href="{{ route('er.show', $p->id) }}" class="text-decoration-none">
                                <div class="card mb-2 border-start border-3 border-{{ $triColour }} shadow-sm">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="small text-dark">{{ optional($p->patient)->patient_name ?? 'Unknown' }}</strong>
                                            @if ($tri)<span class="badge bg-{{ $triColour }} small">{{ $tri->triage_level }}</span>@endif
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-hash"></i> {{ optional($p->patient)->mrn ?? 'ER#'.$p->id }}<br>
                                            <i class="bi bi-clock"></i> {{ $p->arrival_time?->diffForHumans() }}
                                            @if ($p->doctor)<br><i class="bi bi-person-badge"></i> {{ $p->doctor->name }}@endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center text-muted small py-4">No patients</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('styles')
<style>
    .kanban-board { scroll-snap-type: x mandatory; }
    .kanban-col { scroll-snap-align: start; }
</style>
@endpush
@endsection
