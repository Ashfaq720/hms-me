@extends('backend.layouts.master')

@section('title', 'NICU Feeding & Nutrition')

@section('content')
<div class="container-fluid">
    <div class="app-page-head d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0"><i class="bi bi-cup-hot text-primary"></i> Feeding &amp; Nutrition</h1>
            <div class="text-muted small">Track feeds, route, volume and tolerance.</div>
        </div>
        @can('nicu_edit')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFeedingModal">
                <i class="bi bi-plus-circle"></i> Log Feeding
            </button>
        @endcan
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    @include('nicu._filter_partial')

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Baby</th>
                            <th>When</th>
                            <th>Feed</th>
                            <th>Route</th>
                            <th>Volume (ml)</th>
                            <th>Tolerated</th>
                            <th>Vomited</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feedings as $f)
                            <tr>
                                <td>
                                    @if($f->admission)
                                        <a href="{{ route('nicu.admissions.show', $f->admission) }}">{{ $f->admission->baby?->patient_name ?? $f->admission->admission_no }}</a>
                                    @else — @endif
                                </td>
                                <td class="small">{{ optional($f->fed_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ $f->feed_type }}</td>
                                <td>{{ $f->route ?? '—' }}</td>
                                <td>{{ $f->volume_ml ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $f->tolerated ? 'success' : 'warning' }}">
                                        {{ $f->tolerated ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    @if($f->vomited)<span class="badge bg-danger">Vomited</span>@else<span class="text-muted small">—</span>@endif
                                </td>
                                <td class="small">{{ $f->recordedBy?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No feedings recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($feedings->hasPages())<div class="card-footer bg-white">{{ $feedings->links() }}</div>@endif
    </div>
</div>

@can('nicu_edit')
<div class="modal fade" id="addFeedingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" action="{{ route('nicu.feedings.store') }}" method="POST">@csrf
            <div class="modal-header bg-primary-subtle">
                <h5 class="modal-title"><i class="bi bi-cup-hot"></i> Log Feeding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-md-7">
                        <label class="form-label small">Admission *</label>
                        <select name="nicu_admission_id" class="form-select form-select-sm" required>
                            <option value="">— pick —</option>
                            @foreach($activeAdmissions as $a)
                                <option value="{{ $a->id }}">{{ $a->admission_no }} — {{ $a->baby?->patient_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">Fed At *</label>
                        <input type="datetime-local" name="fed_at" class="form-control form-control-sm"
                               value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Feed Type *</label>
                        <select name="feed_type" class="form-select form-select-sm" required>
                            @foreach(\App\Models\Nicu\NicuFeeding::FEED_TYPES as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Route</label>
                        <select name="route" class="form-select form-select-sm">
                            <option value="">—</option>
                            @foreach(\App\Models\Nicu\NicuFeeding::ROUTES as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Volume (ml)</label>
                        <input type="number" step="0.1" min="0" max="500" name="volume_ml" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-6 form-check ms-2 mt-3">
                        <input type="hidden" name="tolerated" value="0">
                        <input class="form-check-input" type="checkbox" name="tolerated" value="1" id="feed_tol" checked>
                        <label class="form-check-label" for="feed_tol">Tolerated</label>
                    </div>
                    <div class="col-md-5 form-check mt-3">
                        <input type="hidden" name="vomited" value="0">
                        <input class="form-check-input" type="checkbox" name="vomited" value="1" id="feed_vom">
                        <label class="form-check-label" for="feed_vom">Vomited</label>
                    </div>

                    <div class="col-md-12 mt-2"><label class="form-label small">Notes</label>
                        <textarea name="notes" rows="2" class="form-control form-control-sm"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
