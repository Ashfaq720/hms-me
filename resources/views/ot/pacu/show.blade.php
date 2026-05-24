@extends('backend.layouts.master')
@section('title','PACU — ' . $schedule->schedule_no)
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between">
        <h1 class="app-page-title">PACU — {{ $schedule->schedule_no }}</h1>
        <a href="{{ route('ot.schedules.show', $schedule->id) }}" class="btn btn-outline-secondary">Back</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3"><div class="card-header"><strong>Record</strong></div>
                <div class="card-body">
                    @if(! $record->exists)
                        <form action="{{ route('ot.pacu.admit', $schedule->id) }}" method="POST" class="row g-2 align-items-end">@csrf
                            <div class="col-md-7">
                                <label class="form-label small mb-1">PACU Bed <span class="text-muted">(from bed master)</span></label>
                                @if(($beds ?? collect())->count() > 0)
                                    <select name="bed_no" class="form-select" required>
                                        <option value="">— Select PACU bed —</option>
                                        @foreach($beds as $b)
                                            <option value="{{ $b->name }}">
                                                {{ $b->name }}
                                                @if($b->bedGroup) · {{ $b->bedGroup->name }}@endif
                                                @if($b->bedType) ({{ $b->bedType->name }})@endif
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input name="bed_no" placeholder="Bed no" class="form-control" required>
                                @endif
                            </div>
                            <div class="col-md-5">
                                <button class="btn btn-success w-100"><i class="bi bi-box-arrow-in-down"></i> Admit to PACU</button>
                            </div>
                        </form>
                    @else
                        <dl class="row mb-0">
                            <dt class="col-4">Bed</dt><dd class="col-8">{{ $record->bed_no ?? '—' }}</dd>
                            <dt class="col-4">Admitted</dt><dd class="col-8">{{ $record->admitted_at?->format('Y-m-d H:i') }}</dd>
                            <dt class="col-4">Discharged</dt><dd class="col-8">{{ $record->discharged_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                            <dt class="col-4">Last Aldrete</dt><dd class="col-8">{{ $record->aldrete_score ?? '—' }}/10</dd>
                            <dt class="col-4">Destination</dt><dd class="col-8">{{ $record->discharge_destination ?? '—' }}</dd>
                        </dl>
                    @endif
                </div>
            </div>

            @if($record->exists)
                <div class="card"><div class="card-header"><strong>Vitals Log</strong></div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead class="table-light"><tr><th>Time</th><th>BP</th><th>Pulse</th><th>SpO2</th><th>Temp</th><th>Pain</th><th>Aldrete</th></tr></thead>
                            <tbody>
                                @php
                                    $vlog = $record->vitals_log;
                                    if (is_string($vlog)) { $vlog = json_decode($vlog, true) ?: []; }
                                    $vlog = is_array($vlog) ? $vlog : [];
                                @endphp
                                @forelse($vlog as $v)
                                    <tr>
                                        <td>{{ $v['time'] ?? $v['t'] ?? '' }}</td>
                                        <td>{{ $v['bp'] ?? '' }}</td>
                                        <td>{{ $v['pulse'] ?? $v['hr'] ?? '' }}</td>
                                        <td>{{ $v['spo2'] ?? '' }}</td>
                                        <td>{{ $v['temp'] ?? '' }}</td>
                                        <td>{{ $v['pain_score'] ?? $v['pain'] ?? '' }}</td>
                                        <td>{{ $v['aldrete_score'] ?? '' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No vitals recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        @if($record->exists)
            <div class="col-lg-5">
                <div class="card mb-3"><div class="card-header"><strong>Add Vitals</strong></div>
                    <form action="{{ route('ot.pacu.vitals', $record->id) }}" method="POST" class="card-body">@csrf
                        <div class="row g-2">
                            <div class="col-6"><input name="bp" class="form-control" placeholder="BP (e.g. 120/80)"></div>
                            <div class="col-6"><input name="pulse" class="form-control" placeholder="Pulse"></div>
                            <div class="col-6"><input name="spo2" class="form-control" placeholder="SpO2 %"></div>
                            <div class="col-6"><input name="temp" class="form-control" placeholder="Temp °C"></div>
                            <div class="col-6"><input name="pain_score" class="form-control" placeholder="Pain 0-10"></div>
                            <div class="col-6"><input name="aldrete_score" class="form-control" placeholder="Aldrete 0-10"></div>
                            <div class="col-12"><textarea name="notes" class="form-control" rows="2" placeholder="Notes"></textarea></div>
                        </div>
                        <button class="btn btn-primary w-100 mt-2">Record Vitals</button>
                    </form>
                </div>

                @if(! $record->discharged_at)
                    <div class="card"><div class="card-header"><strong>Discharge from PACU</strong></div>
                        <form action="{{ route('ot.pacu.discharge', $record->id) }}" method="POST" class="card-body">@csrf
                            <div class="mb-2"><label class="form-label">Destination *</label>
                                <select name="discharge_destination" class="form-select" required>
                                    @foreach(['IPD','ICU','CCU','Ward','Home'] as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach
                                </select>
                            </div>
                            <div class="mb-2"><label class="form-label">Aldrete Score</label><input name="aldrete_score" type="number" min="0" max="10" class="form-control"></div>
                            <button class="btn btn-success w-100">Discharge</button>
                        </form>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
