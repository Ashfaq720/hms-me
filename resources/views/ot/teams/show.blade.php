@extends('backend.layouts.master')
@section('title','OT Team')
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between">
        <h1 class="app-page-title">Team — {{ $schedule->schedule_no }}</h1>
        <a href="{{ route('ot.schedules.show', $schedule->id) }}" class="btn btn-outline-secondary">Back</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-3">
        <div class="col-md-7"><div class="card">
            <div class="card-header"><strong>Assigned Members</strong></div>
            <div class="table-responsive"><table class="table mb-0">
                <thead class="table-light"><tr><th>Role</th><th>Staff #</th><th>Type</th><th>Primary</th><th></th></tr></thead>
                <tbody>
                    @forelse($schedule->teamMembers as $m)
                        <tr>
                            <td>{{ ucwords(str_replace('_',' ',$m->role)) }}</td>
                            <td>{{ $m->staff_id }}</td>
                            <td>{{ $m->staff_type }}</td>
                            <td>{{ $m->is_primary ? 'Yes' : '' }}</td>
                            <td class="text-end">
                                <form action="{{ route('ot.teams.remove', $m->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No team members.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div></div>

        <div class="col-md-5"><div class="card">
            <div class="card-header"><strong>Assign New Member</strong></div>
            <form action="{{ route('ot.teams.assign', $schedule->id) }}" method="POST" class="card-body">@csrf
                <div class="mb-2"><label class="form-label">Role *</label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $r)<option value="{{ $r }}">{{ ucwords(str_replace('_',' ',$r)) }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-2"><label class="form-label">Staff (Doctor) *</label>
                    <select name="staff_id" class="form-select" required>
                        <option value="">— select —</option>
                        @foreach($doctors as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-2"><label class="form-label">Staff Type</label>
                    <input type="text" name="staff_type" class="form-control" value="doctor">
                </div>
                <div class="mb-2"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
                <button class="btn btn-primary w-100">Assign</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
