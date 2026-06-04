@extends('backend.layouts.master')

@section('title', 'Surgery Requests')

@section('content')
<div class="container-fluid">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
        <h1 class="app-page-title mb-0">Surgery Requests</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('ot.surgery-requests.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> New Request
            </a>
            <a href="{{ route('ot.emergency.create') }}" class="btn btn-danger">
                <i class="bi bi-exclamation-triangle me-1"></i> Emergency
            </a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form method="GET" class="card card-body mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <input type="text" name="search" class="form-control flex-grow-1"
                   style="min-width: 200px; max-width: 320px;"
                   placeholder="Search request no, patient, MRN…" value="{{ request('search') }}">

            <select name="status" class="form-select" style="width: auto; min-width: 160px;">
                <option value="">All Status</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>

            <select name="priority" class="form-select" style="width: auto; min-width: 130px;">
                <option value="">All Priority</option>
                @foreach(['Low','Normal','High','Emergency'] as $p)
                    <option value="{{ $p }}" @selected(request('priority') === $p)>{{ $p }}</option>
                @endforeach
            </select>

            <div class="form-check m-0">
                <input type="hidden" name="emergency_only" value="0">
                <input class="form-check-input" type="checkbox" name="emergency_only" value="1" id="emFilter" @checked(request('emergency_only'))>
                <label class="form-check-label" for="emFilter">Emergency only</label>
            </div>
            <div class="form-check m-0">
                <input type="hidden" name="pending_info_only" value="0">
                <input class="form-check-input" type="checkbox" name="pending_info_only" value="1" id="piFilter" @checked(request('pending_info_only'))>
                <label class="form-check-label" for="piFilter">Pending info</label>
            </div>

            <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
            @if(request()->hasAny(['search','status','priority','emergency_only','pending_info_only']))
                <a href="{{ route('ot.surgery-requests.index') }}" class="btn btn-outline-secondary" title="Clear filters">
                    <i class="bi bi-x-circle"></i>
                </a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Request No</th>
                        <th>Patient</th>
                        <th>Procedure</th>
                        <th>Surgeon</th>
                        <th>Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $r)
                        <tr>
                            <td>
                                <a href="{{ route('ot.surgery-requests.show', $r->id) }}">{{ $r->request_no }}</a>
                                @if($r->is_emergency)<span class="badge bg-danger ms-1">ER</span>@endif
                            </td>
                            <td>{{ optional($r->patient)->patient_name }}</td>
                            <td>{{ optional($r->surgeryType)->name ?? '—' }}</td>
                            <td>{{ optional($r->primarySurgeon)->name ?? '—' }}</td>
                            <td>{{ $r->requested_surgery_date?->format('Y-m-d') ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $r->priority }}</span></td>
                            <td><span class="badge {{ $r->status_badge_class }}">{{ $r->status }}</span></td>
                            <td class="text-end col-nowrap">
                                <a href="{{ route('ot.surgery-requests.show', $r->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(in_array($r->status, ['Draft','Submitted','Pending Information','Sent Back for Correction']))
                                    <a href="{{ route('ot.surgery-requests.edit', $r->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                @if($r->status === 'Draft')
                                    <form action="{{ route('ot.surgery-requests.destroy', $r->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete draft {{ $r->request_no }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete draft">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No surgery requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $requests->links() }}</div>
</div>
@endsection
