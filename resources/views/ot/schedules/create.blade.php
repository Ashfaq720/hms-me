@extends('backend.layouts.master')

@section('title', 'Schedule Surgery')

@section('content')
<div class="container-fluid">
    <div class="app-page-head d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title mb-0">Schedule Surgery</h1>
        <a href="{{ route('ot.schedules.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('ot.schedules.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Surgery Request *</label>
                        <select name="surgery_request_id" class="form-select" required>
                            <option value="">— select —</option>
                            @foreach($availableRequests as $r)
                                <option value="{{ $r->id }}" @selected(($surgeryRequest?->id ?? old('surgery_request_id')) == $r->id)>
                                    {{ $r->request_no }} — {{ optional($r->patient)->patient_name }}
                                    ({{ optional($r->surgeryType)->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">OT Room *</label>
                        <select name="ot_room_id" class="form-select" required>
                            <option value="">— select —</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('ot_room_id') == $room->id)>{{ $room->name }} ({{ $room->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Start *</label>
                        <input type="datetime-local" name="scheduled_start" class="form-control" required value="{{ old('scheduled_start') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End *</label>
                        <input type="datetime-local" name="scheduled_end" class="form-control" required value="{{ old('scheduled_end') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cleaning Buffer (min)</label>
                        <input type="number" name="buffer_minutes" class="form-control" min="0" max="240" value="{{ old('buffer_minutes', 30) }}">
                        <small class="form-text text-muted">Auto-blocks room for cleaning</small>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="emergency_fast_track" value="0">
                            <input class="form-check-input" type="checkbox" name="emergency_fast_track" value="1" id="emTrack" @checked(old('emergency_fast_track'))>
                            <label class="form-check-label" for="emTrack">Emergency Fast-Track (bypass conflict check)</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">OT Team</h6>
                        <div id="team-rows">
                            <div class="row g-2 mb-2">
                                <div class="col-md-3">
                                    <select name="team[0][role]" class="form-select team-role">
                                        <option value="primary_surgeon">Primary Surgeon</option>
                                        <option value="assistant_surgeon">Assistant Surgeon</option>
                                        <option value="anesthetist">Anesthetist</option>
                                        <option value="scrub_nurse">Scrub Nurse</option>
                                        <option value="circulating_nurse">Circulating Nurse</option>
                                        <option value="technician">Technician</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="team[0][staff_id]" class="form-select">
                                        <option value="">— select —</option>
                                        @foreach($doctors as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="team[0][staff_type]" class="form-control" placeholder="doctor" value="doctor">
                                </div>
                                <div class="col-md-4">
                                    <select name="team[0][specialization]" class="form-select team-spec">
                                        <option value="">Specialization (for technician)</option>
                                        @foreach(\App\Models\Ot\OtSurgeryTeam::SPECIALIZATIONS as $s)
                                            <option value="{{ $s }}">{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">More team members can be added on the schedule detail page after creation. Specialization is most relevant for Technician role.</small>
                    </div>

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Equipment</h6>
                        <select name="equipment_ids[]" class="form-select" multiple size="5">
                            @foreach($equipments as $e)<option value="{{ $e->id }}">{{ $e->name }} ({{ $e->code }})</option>@endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Schedule Surgery</button>
            </div>
        </div>
    </form>
</div>
@endsection
