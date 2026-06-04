<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Surgery Requests</h6>
            <small class="text-muted">Each row syncs with the OT Management module — click any action to continue the workflow there.</small>
        </div>
        <div class="d-flex gap-2">
            @if(\Illuminate\Support\Facades\Route::has('ot.surgery-requests.index'))
                <a href="{{ route('ot.surgery-requests.index') . '?search=' . urlencode($iPDPatient->patient->patient_name ?? '') }}"
                   class="btn btn-outline-secondary btn-sm" title="Open OT Surgery Request list">
                    <i class="bi bi-box-arrow-up-right me-1"></i> OT Module
                </a>
            @endif
            @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                <a data-size="lg" class="btn btn-primary px-2"
                    data-url="{{ route('ipd-patients.surgery-requests', $iPDPatient->id) }}" data-ajax-popup="true"
                    data-title="New Surgery Request" data-bs-toggle="tooltip" title="New Surgery Request">
                    <i class="bi bi-plus-lg me-1"></i> New Surgery Request
                </a>
            @endif
        </div>
    </div>

    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th width="3%">SN</th>
                <th width="13%">Request No</th>
                <th width="11%">Requested On</th>
                <th width="11%">Surgery Date</th>
                <th width="14%">Surgeon</th>
                <th width="10%">Priority</th>
                <th width="18%">Status</th>
                <th width="20%">Next Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($iPDPatient->surgeryRequests->sortByDesc('id') as $entry)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if(\Illuminate\Support\Facades\Route::has('ot.surgery-requests.show'))
                            <a href="{{ route('ot.surgery-requests.show', $entry->id) }}" class="fw-semibold">
                                {{ $entry->request_no }}
                            </a>
                        @else
                            {{ $entry->request_no }}
                        @endif
                        @if($entry->is_emergency)
                            <span class="badge bg-danger ms-1">ER</span>
                        @endif
                    </td>
                    <td>{{ format_datetime($entry->created_at) ?? '-' }}</td>
                    <td>{{ $entry->requested_surgery_date ? $entry->requested_surgery_date->format('Y-m-d') : '-' }}</td>
                    <td>{{ optional($entry->primarySurgeon)->name ?? '-' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $entry->priority ?? '-' }}</span></td>
                    <td><span class="badge {{ $entry->status_badge_class }}">{{ $entry->status }}</span></td>
                    <td>
                        @php
                            $status = $entry->status;
                            $rView = \Illuminate\Support\Facades\Route::has('ot.surgery-requests.show')
                                ? route('ot.surgery-requests.show', $entry->id) : null;
                            $rEdit = \Illuminate\Support\Facades\Route::has('ot.surgery-requests.edit')
                                ? route('ot.surgery-requests.edit', $entry->id) : null;
                            $rSchedule = \Illuminate\Support\Facades\Route::has('ot.schedules.create')
                                ? route('ot.schedules.create', ['request_id' => $entry->id]) : null;
                        @endphp

                        <div class="d-flex flex-wrap gap-1">
                            @if($rView)
                                <a href="{{ $rView }}" class="btn btn-sm btn-outline-primary" title="View / Submit / Approve in OT">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif

                            @if($rEdit && in_array($status, ['Draft','Submitted','Pending Information','Sent Back for Correction']))
                                <a href="{{ $rEdit }}" class="btn btn-sm btn-outline-warning" title="Edit / Complete details">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif

                            @if($rSchedule && in_array($status, ['Accepted','Moved to Scheduling','Emergency Fast-Tracked']))
                                <a href="{{ $rSchedule }}" class="btn btn-sm btn-outline-success" title="Create Schedule">
                                    <i class="bi bi-calendar-plus me-1"></i> Schedule
                                </a>
                            @endif

                            @if($status === 'Scheduled' && $entry->activeSchedule)
                                <a href="{{ route('ot.schedules.show', $entry->activeSchedule->id) }}"
                                   class="btn btn-sm btn-outline-info" title="Open Schedule">
                                    <i class="bi bi-calendar-check me-1"></i> Schedule
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-3">
                        No surgery requests yet.
                        @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                            Click <strong>New Surgery Request</strong> above to add one.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($iPDPatient->surgeryRequests->count() > 0)
        <div class="small text-muted mt-2">
            <i class="bi bi-info-circle"></i>
            Click the request number or any action icon to continue the workflow in the OT module.
            Once accepted there, return here to see the schedule.
        </div>
    @endif

    {{-- Related family surgeries (mother ↔ baby cross-link via NICU) --}}
    @php
        $nicu = \App\Models\Nicu\NicuAdmission::where('ipd_patient_id', $iPDPatient->id)->first();
        $relatedSurgeries = collect();
        $relatedLabel = null;
        $relatedHref = null;
        if ($nicu && $nicu->mother_ipd_patient_id) {
            $relatedSurgeries = \App\Models\Ot\OtSurgeryRequest::with('primarySurgeon')
                ->where('ipd_admission_id', $nicu->mother_ipd_patient_id)->latest('id')->get();
            $relatedLabel = "Mother's surgeries (delivery / pre-natal)";
            $relatedHref = route('ipd-patients.show', $nicu->mother_ipd_patient_id);
        } else {
            $babyNicu = \App\Models\Nicu\NicuAdmission::with('patient')
                ->where('mother_ipd_patient_id', $iPDPatient->id)->get();
            if ($babyNicu->count()) {
                $babyIpdIds = $babyNicu->pluck('ipd_patient_id')->filter();
                $relatedSurgeries = \App\Models\Ot\OtSurgeryRequest::with('primarySurgeon')
                    ->whereIn('ipd_admission_id', $babyIpdIds)->latest('id')->get();
                $relatedLabel = "Newborn surgeries (" . $babyNicu->count() . " baby)";
            }
        }
    @endphp
    @if ($relatedSurgeries->count())
        <div class="card border-info bg-info bg-opacity-10 mt-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 text-info">
                        <i class="bi bi-link-45deg"></i> Related: {{ $relatedLabel }}
                    </h6>
                    @if ($relatedHref)
                        <a href="{{ $relatedHref }}" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-arrow-right"></i> Open related IPD
                        </a>
                    @endif
                </div>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Request</th><th>Surgery Date</th><th>Surgeon</th><th>Priority</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($relatedSurgeries as $rs)
                        <tr>
                            <td><strong>{{ $rs->request_no }}</strong>
                                @if ($rs->is_emergency)<span class="badge bg-danger ms-1">ER</span>@endif
                            </td>
                            <td>{{ $rs->requested_surgery_date?->format('Y-m-d') }}</td>
                            <td>{{ optional($rs->primarySurgeon)->name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $rs->priority }}</span></td>
                            <td><span class="badge bg-secondary">{{ $rs->status }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
