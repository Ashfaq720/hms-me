@extends('backend.layouts.master')

@section('title', 'Edit Schedule')

@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between"><h1 class="app-page-title mb-0">Edit Schedule</h1>
        <a href="{{ route('ot.schedules.show', $schedule->id) }}" class="btn btn-outline-secondary">Back</a>
    </div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form action="{{ route('ot.schedules.update', $schedule->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">OT Room</label>
                        <select name="ot_room_id" class="form-select">
                            @foreach($rooms as $r)<option value="{{ $r->id }}" @selected($schedule->ot_room_id == $r->id)>{{ $r->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Start</label>
                        <input type="datetime-local" name="scheduled_start" class="form-control" value="{{ $schedule->scheduled_start?->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-3"><label class="form-label">End</label>
                        <input type="datetime-local" name="scheduled_end" class="form-control" value="{{ $schedule->scheduled_end?->format('Y-m-d\TH:i') }}">
                    </div>
                </div>
            </div>
            <div class="card-footer text-end"><button class="btn btn-primary">Save</button></div>
        </div>
    </form>
</div>
@endsection
