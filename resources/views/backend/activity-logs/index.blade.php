@extends('backend.layouts.master')

@section('title', 'Activity Logs')

@push('styles')
<style>
    .act-row:hover { background:#fafbfc; }
    .badge.event-created   { background:#d6f5e3; color:#0a5d2b; }
    .badge.event-updated   { background:#d6e6ff; color:#0a3c8a; }
    .badge.event-deleted   { background:#ffe1ec; color:#7a1f3d; }
    .badge.event-default   { background:#f4f6f8; color:#495057; }

    .act-detail-grid { display:grid; grid-template-columns: 160px 1fr; gap:.5rem 1rem; font-size:.88rem; }
    .act-detail-grid dt { color:#6c757d; font-weight:500; }
    .act-detail-grid dd { margin:0; word-break:break-word; }

    .diff-table { width:100%; font-size:.82rem; border:1px solid #e9ecef; border-radius:.45rem; overflow:hidden; }
    .diff-table th { background:#fafbfc; padding:.45rem .65rem; text-align:left; font-weight:600; border-bottom:1px solid #e9ecef; }
    .diff-table td { padding:.45rem .65rem; border-top:1px solid #f1f3f5; vertical-align:top; }
    .diff-table .field { font-weight:600; color:#495057; min-width:140px; }
    .diff-old { background:#fef0f0; color:#7a1f3d; }
    .diff-new { background:#eaf8ee; color:#0a5d2b; }
    .diff-empty { color:#adb5bd; font-style:italic; }
    .json-block { background:#f8f9fa; padding:.65rem; border-radius:.4rem; font-size:.78rem; max-height:300px; overflow:auto; }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Activity Logs</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Activity Logs</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">All Activities</h6>
                        <span class="badge bg-light text-dark border">{{ $activities->total() }} total</span>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Log Name</th>
                                        <th>Event</th>
                                        <th>Description</th>
                                        <th>Causer</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th style="width:120px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($activities as $activity)
                                        @php
                                            $event = strtolower((string) $activity->event);
                                            $eventCls = in_array($event, ['created','updated','deleted'])
                                                ? "event-$event" : 'event-default';
                                            $props    = $activity->properties ? $activity->properties->toArray() : [];
                                            $oldVals  = is_array($props['old'] ?? null) ? $props['old'] : [];
                                            $newVals  = is_array($props['attributes'] ?? null) ? $props['attributes'] : [];
                                            $other    = array_diff_key($props, ['old' => true, 'attributes' => true]);
                                        @endphp
                                        <tr class="act-row">
                                            <td>{{ ($activities->currentPage() - 1) * $activities->perPage() + $loop->iteration }}</td>
                                            <td><span class="badge bg-info-subtle text-info">{{ $activity->log_name ?? '—' }}</span></td>
                                            <td><span class="badge {{ $eventCls }}">{{ $activity->event ?? '—' }}</span></td>
                                            <td>{{ $activity->description }}</td>
                                            <td>
                                                @if ($activity->causer)
                                                    <div class="fw-medium small">{{ $activity->causer->name ?? 'N/A' }}</div>
                                                    <div class="text-muted small">{{ $activity->causer->email ?? '' }}</div>
                                                @else
                                                    <span class="text-muted small">System</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($activity->subject_type)
                                                    <span class="badge bg-light text-dark border">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="small">{{ $activity->created_at->format('Y-m-d H:i') }}</div>
                                                <div class="text-muted" style="font-size:.7rem;">{{ $activity->created_at->diffForHumans() }}</div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary view-details-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#activityModal"
                                                        data-id="{{ $activity->id }}"
                                                        data-log-name="{{ $activity->log_name }}"
                                                        data-event="{{ $activity->event }}"
                                                        data-description="{{ $activity->description }}"
                                                        data-causer-name="{{ $activity->causer->name ?? 'System' }}"
                                                        data-causer-email="{{ $activity->causer->email ?? '' }}"
                                                        data-causer-id="{{ $activity->causer_id ?? '' }}"
                                                        data-causer-type="{{ $activity->causer_type ?? '' }}"
                                                        data-subject-type="{{ $activity->subject_type ?? '' }}"
                                                        data-subject-id="{{ $activity->subject_id ?? '' }}"
                                                        data-subject-basename="{{ $activity->subject_type ? class_basename($activity->subject_type) : '' }}"
                                                        data-batch-uuid="{{ $activity->batch_uuid ?? '' }}"
                                                        data-created-at="{{ $activity->created_at->format('Y-m-d H:i:s') }}"
                                                        data-created-human="{{ $activity->created_at->diffForHumans() }}"
                                                        data-old='@json($oldVals)'
                                                        data-new='@json($newVals)'
                                                        data-other='@json($other)'>
                                                    <i class="bi bi-eye"></i> View Details
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">No activity logged yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2">
                            {{ $activities->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ────────── Activity Details Modal ────────── --}}
        <div class="modal fade" id="activityModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-info-circle text-primary"></i>
                            Activity Details
                            <span id="actModalId" class="text-muted small ms-2"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Header summary --}}
                        <div class="card border mb-3">
                            <div class="card-body py-2">
                                <dl class="act-detail-grid mb-0">
                                    <dt>Log Name</dt>     <dd><span id="actLogName" class="badge bg-info-subtle text-info">—</span></dd>
                                    <dt>Event</dt>        <dd><span id="actEvent" class="badge event-default">—</span></dd>
                                    <dt>Description</dt>  <dd id="actDescription">—</dd>
                                    <dt>Performed by</dt> <dd>
                                        <div id="actCauserName" class="fw-medium">—</div>
                                        <div id="actCauserEmail" class="text-muted small"></div>
                                        <div id="actCauserMeta" class="text-muted" style="font-size:.7rem;"></div>
                                    </dd>
                                    <dt>Subject</dt>      <dd id="actSubject" class="font-monospace small">—</dd>
                                    <dt>When</dt>         <dd>
                                        <div id="actDate">—</div>
                                        <div id="actDateHuman" class="text-muted small"></div>
                                    </dd>
                                    <dt>Batch ID</dt>     <dd id="actBatch" class="text-muted font-monospace small">—</dd>
                                </dl>
                            </div>
                        </div>

                        {{-- Field changes (old vs new) --}}
                        <div id="actChangesWrap" class="d-none mb-3">
                            <h6 class="text-muted small fw-semibold text-uppercase mb-2">
                                <i class="bi bi-arrow-left-right"></i> Field Changes
                            </h6>
                            <div class="table-responsive">
                                <table class="diff-table">
                                    <thead>
                                        <tr><th class="field">Field</th><th>Old Value</th><th>New Value</th></tr>
                                    </thead>
                                    <tbody id="actChangesBody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Snapshot (when only `attributes`, no `old`) --}}
                        <div id="actSnapshotWrap" class="d-none mb-3">
                            <h6 class="text-muted small fw-semibold text-uppercase mb-2">
                                <i class="bi bi-camera"></i> Recorded Values
                            </h6>
                            <div class="table-responsive">
                                <table class="diff-table">
                                    <thead><tr><th class="field">Field</th><th>Value</th></tr></thead>
                                    <tbody id="actSnapshotBody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Other properties (custom keys) --}}
                        <div id="actOtherWrap" class="d-none mb-3">
                            <h6 class="text-muted small fw-semibold text-uppercase mb-2">
                                <i class="bi bi-tag"></i> Additional Properties
                            </h6>
                            <pre id="actOtherJson" class="json-block mb-0"></pre>
                        </div>

                        {{-- Raw JSON (collapsible) --}}
                        <details class="mt-3">
                            <summary class="small text-muted" style="cursor:pointer;">
                                <i class="bi bi-code-slash"></i> Raw properties JSON
                            </summary>
                            <pre id="actRawJson" class="json-block mt-2 mb-0"></pre>
                        </details>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const modal = document.getElementById('activityModal');
    if (!modal) return;

    function format(v) {
        if (v === null || v === undefined || v === '') return '<span class="diff-empty">empty</span>';
        if (typeof v === 'boolean') return v ? 'true' : 'false';
        if (typeof v === 'object') return '<code>' + JSON.stringify(v) + '</code>';
        const s = String(v);
        // Escape HTML
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function renderChanges(oldVals, newVals) {
        const wrap = document.getElementById('actChangesWrap');
        const body = document.getElementById('actChangesBody');
        body.innerHTML = '';
        const keys = Array.from(new Set([...Object.keys(oldVals || {}), ...Object.keys(newVals || {})]));
        if (keys.length === 0) { wrap.classList.add('d-none'); return; }
        for (const k of keys) {
            const tr = document.createElement('tr');
            tr.innerHTML =
                `<td class="field">${format(k)}</td>` +
                `<td class="diff-old">${format(oldVals?.[k] ?? null)}</td>` +
                `<td class="diff-new">${format(newVals?.[k] ?? null)}</td>`;
            body.appendChild(tr);
        }
        wrap.classList.remove('d-none');
    }

    function renderSnapshot(newVals) {
        const wrap = document.getElementById('actSnapshotWrap');
        const body = document.getElementById('actSnapshotBody');
        body.innerHTML = '';
        const keys = Object.keys(newVals || {});
        if (keys.length === 0) { wrap.classList.add('d-none'); return; }
        for (const k of keys) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td class="field">${format(k)}</td><td>${format(newVals[k])}</td>`;
            body.appendChild(tr);
        }
        wrap.classList.remove('d-none');
    }

    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            let oldVals = {}, newVals = {}, other = {};
            try { oldVals = JSON.parse(d.old || '{}'); }   catch { oldVals = {}; }
            try { newVals = JSON.parse(d.new || '{}'); }   catch { newVals = {}; }
            try { other   = JSON.parse(d.other || '{}'); } catch { other = {}; }

            document.getElementById('actModalId').textContent     = '#' + d.id;
            document.getElementById('actLogName').textContent     = d.logName || '—';
            const ev = document.getElementById('actEvent');
            ev.textContent = d.event || '—';
            ev.className   = 'badge ' + (['created','updated','deleted'].includes((d.event||'').toLowerCase())
                                          ? 'event-' + d.event.toLowerCase() : 'event-default');
            document.getElementById('actDescription').textContent = d.description || '—';
            document.getElementById('actCauserName').textContent  = d.causerName || 'System';
            document.getElementById('actCauserEmail').textContent = d.causerEmail || '';
            document.getElementById('actCauserMeta').textContent  =
                d.causerId ? (`${d.causerType ? d.causerType.split('\\\\').pop() : 'User'} #${d.causerId}`) : '';
            document.getElementById('actSubject').innerHTML       = d.subjectType
                ? `${d.subjectBasename} #${d.subjectId} <span class="text-muted">(${d.subjectType})</span>`
                : '—';
            document.getElementById('actDate').textContent        = d.createdAt;
            document.getElementById('actDateHuman').textContent   = d.createdHuman;
            document.getElementById('actBatch').textContent       = d.batchUuid || '—';

            // Render appropriate body section based on event type
            const hasOld = Object.keys(oldVals).length > 0;
            const hasNew = Object.keys(newVals).length > 0;

            if (hasOld && hasNew) {
                renderChanges(oldVals, newVals);
                document.getElementById('actSnapshotWrap').classList.add('d-none');
            } else if (hasNew) {
                renderSnapshot(newVals);
                document.getElementById('actChangesWrap').classList.add('d-none');
            } else if (hasOld) {
                renderSnapshot(oldVals);
                document.getElementById('actChangesWrap').classList.add('d-none');
            } else {
                document.getElementById('actChangesWrap').classList.add('d-none');
                document.getElementById('actSnapshotWrap').classList.add('d-none');
            }

            // Other custom properties
            const otherWrap = document.getElementById('actOtherWrap');
            const otherJson = document.getElementById('actOtherJson');
            if (Object.keys(other).length > 0) {
                otherJson.textContent = JSON.stringify(other, null, 2);
                otherWrap.classList.remove('d-none');
            } else {
                otherWrap.classList.add('d-none');
            }

            // Raw JSON
            document.getElementById('actRawJson').textContent = JSON.stringify(
                { old: oldVals, attributes: newVals, ...other }, null, 2
            );
        });
    });
})();
</script>
@endpush
