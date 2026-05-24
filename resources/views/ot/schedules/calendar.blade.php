@extends('backend.layouts.master')

@section('title', 'OT Calendar')

@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between align-items-center">
        <h1 class="app-page-title mb-0">OT Calendar — {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}</h1>
        <form method="GET" class="d-flex gap-2 align-items-end">
            <a href="{{ route('ot.schedules.calendar', ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}" class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
            <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
            <a href="{{ route('ot.schedules.calendar', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}" class="btn btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
            <a href="{{ route('ot.schedules.create') }}" class="btn btn-primary">+ New</a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" style="min-width: 1200px;">
            <thead>
                <tr>
                    <th style="width:90px">Time</th>
                    @foreach($rooms as $room)
                        <th class="text-center">{{ $room->name }}<br><small class="text-muted">{{ $room->code }}</small></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for($hour = 6; $hour < 24; $hour++)
                    <tr style="height: 60px">
                        <td class="align-top text-muted small">{{ sprintf('%02d:00', $hour) }}</td>
                        @foreach($rooms as $room)
                            <td class="position-relative">
                                @foreach($schedules as $s)
                                    @if($s->ot_room_id === $room->id
                                        && $s->scheduled_start->hour === $hour
                                        && $s->scheduled_start->isSameDay(\Carbon\Carbon::parse($date)))
                                        @php
                                            $durationMin = $s->scheduled_end->diffInMinutes($s->scheduled_start);
                                            $height = max(40, $durationMin);
                                        @endphp
                                        <a href="{{ route('ot.schedules.show', $s->id) }}"
                                           class="d-block text-decoration-none p-1 rounded text-white small"
                                           style="background: var(--bs-primary); height:{{ $height }}px; overflow:hidden;"
                                           title="{{ $s->schedule_no }}">
                                            <strong>{{ $s->scheduled_start->format('H:i') }}</strong>
                                            {{ optional($s->surgeryRequest?->patient)->patient_name }}
                                            <div class="small opacity-75">{{ optional($s->surgeryRequest?->surgeryType)->name }}</div>
                                        </a>
                                    @endif
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
@endsection
