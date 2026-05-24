@extends('backend.layouts.master')
@section('title','Audit Trail')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Audit Trail</h1>
    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-3"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
            <div class="col-md-3"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
            <div class="col-md-3">
                <select name="entity_type" class="form-select">
                    <option value="">All entities</option>
                    @foreach(['surgery_request','surgery_schedule','pre_op_checklist','ot_transfer','anesthesia_record','intra_op_record','post_op_note','pacu_record','consumable_usage','surgery_team','ot_cleaning','ot_document'] as $e)
                        <option value="{{ $e }}" @selected(request('entity_type') === $e)>{{ $e }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input name="action" class="form-control" placeholder="Action" value="{{ request('action') }}"></div>
            <div class="col-md-1"><button class="btn btn-primary w-100">Go</button></div>
        </div>
    </form>

    <div class="card"><div class="table-responsive"><table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>When</th><th>Entity</th><th>ID</th><th>Action</th><th>From → To</th><th>User</th><th>Reason</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td class="small">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->entity_type }}</td>
                    <td>{{ $log->entity_id }}</td>
                    <td><span class="badge bg-info">{{ $log->action }}</span></td>
                    <td class="small">{{ $log->from_status }} {{ $log->from_status && $log->to_status ? '→' : '' }} {{ $log->to_status }}</td>
                    <td>#{{ $log->user_id ?? '—' }}</td>
                    <td class="small">{{ $log->reason }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No audit entries.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $logs->links() }}</div>
</div>
@endsection
