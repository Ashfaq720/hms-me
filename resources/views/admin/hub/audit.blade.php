@extends('backend.layouts.master')
@section('title', 'Audit Log Viewer')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-eye text-warning"></i> Audit Log Viewer</h4>
            <small class="text-muted">Every change in the system, captured by Spatie ActivityLog</small>
        </div>
        <a href="{{ route('admin.hub.reports') }}" class="btn btn-sm btn-outline-primary">← Reports Hub</a>
    </div>

    {{-- KPI cards --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-primary bg-opacity-10 p-3"><small class="text-primary">Total Activities</small><h4 class="mb-0">{{ number_format($stats['total']) }}</h4></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-info bg-opacity-10 p-3"><small class="text-info">Today</small><h4 class="mb-0">{{ $stats['today'] }}</h4></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-success bg-opacity-10 p-3"><small class="text-success">This Week</small><h4 class="mb-0">{{ $stats['this_week'] }}</h4></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-warning bg-opacity-10 p-3"><small class="text-warning">Distinct Users</small><h4 class="mb-0">{{ $stats['distinct_users'] }}</h4></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Top Events</h6></div>
                <ul class="list-group list-group-flush">
                    @forelse ($events as $e)
                        <li class="list-group-item d-flex justify-content-between">
                            <span><span class="badge bg-secondary">{{ $e->event ?: '—' }}</span></span>
                            <strong>{{ $e->n }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">No data</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Top Log Channels</h6></div>
                <ul class="list-group list-group-flush">
                    @forelse ($logNames as $l)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $l->log_name ?: '—' }}</span>
                            <strong>{{ $l->n }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">No data</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-2"><input name="log_name" value="{{ request('log_name') }}" class="form-control form-control-sm" placeholder="Log channel"></div>
                <div class="col-md-2"><input name="event" value="{{ request('event') }}" class="form-control form-control-sm" placeholder="Event (created/updated/…)"></div>
                <div class="col-md-2"><input name="subject_type" value="{{ request('subject_type') }}" class="form-control form-control-sm" placeholder="Model name"></div>
                <div class="col-md-2"><input name="causer" value="{{ request('causer') }}" class="form-control form-control-sm" placeholder="User name"></div>
                <div class="col-md-1"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm"></div>
                <div class="col-md-1"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm"></div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button></div>
            </form>
        </div>
    </div>

    {{-- Log table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>When</th><th>Channel</th><th>Event</th><th>Model</th><th>Subject ID</th>
                        <th>Description</th><th>User</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($logs as $l)
                    <tr>
                        <td>{{ $l->id }}</td>
                        <td><small>{{ \Carbon\Carbon::parse($l->created_at)->format('Y-m-d H:i') }}</small></td>
                        <td><span class="badge bg-secondary bg-opacity-15 text-secondary">{{ $l->log_name }}</span></td>
                        <td>
                            @php $col = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', 'restored' => 'info'][$l->event] ?? 'secondary'; @endphp
                            <span class="badge bg-{{ $col }} bg-opacity-15 text-{{ $col }}">{{ $l->event }}</span>
                        </td>
                        <td><small>{{ class_basename($l->subject_type ?: '') }}</small></td>
                        <td><small>{{ $l->subject_id }}</small></td>
                        <td>{{ $l->description }}</td>
                        <td><small>{{ $l->causer_name ?: 'System' }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-3">No activities match your filter</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
