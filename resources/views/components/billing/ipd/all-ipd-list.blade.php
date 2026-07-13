{{-- All Ipd Tab --}}
<div class="tab-pane fade  show active" id="all-ipd" role="tabpanel">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-1">All Ipd Patients</h5>
                    <div class="text-muted small">
                        Total <strong>{{ number_format($totalIpdPatients) }}</strong> patients
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm ipdb-search" style="width: 260px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input id="ipdbPatientsSearch" type="text" class="form-control border-start-0 ps-0"
                            placeholder="Search Ipd no, patient, doctor...">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table ipdb-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width:50px;">#</th>
                            <th>Ipd No</th>
                            <th>Patient</th>
                            <th>Mobile</th>
                            <th>Gender</th>
                            <th>Doctor</th>
                            <th>Department</th>
                            <th>Admission Date</th>
                            <th>Admission Type</th>
                            <th>Bed</th>
                            <th class="pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ipdPatients as $i => $ipd)
                            @php
                                $latestAllocation = $ipd->bedAllocations?->first();
                                $statusColor = match ($ipd->status) {
                                    'Admitted' => 'success',
                                    'Discharged' => 'info',
                                    default => 'warning',
                                };
                            @endphp
                            <tr class="ipdb-patient-row" data-index="{{ $i }}">
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <a href="{{ route('billing.ipd-billing.show', $ipd->id) }}"
                                        class="ipdb-bill-link">{{ $ipd->ipd_no ?? '-' }}</a>
                                </td>
                                <td>
                                    <div class="fw-semibold lh-sm">{{ $ipd->patient?->patient_name ?? '-' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        {{ $ipd->patient?->mrn ?? '' }}</div>
                                </td>
                                <td>{{ $ipd->patient?->mobileno ?? '-' }}</td>
                                <td>{{ $ipd->patient?->gender ?? '-' }}</td>
                                <td>{{ $ipd->doctor?->name ?? '-' }}</td>
                                <td>{{ $ipd->department?->name ?? '-' }}</td>
                                <td>
                                    @if ($ipd->admission_date)
                                        <div class="fw-semibold">
                                            {{ $ipd->admission_date->format('d/m/Y') }}</div>
                                        <div class="text-muted small">
                                            {{ $ipd->admission_date->format('h:i A') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $ipd->admission_type ?? '-' }}</td>
                                <td>
                                    @if ($latestAllocation && $latestAllocation->bed && ($latestAllocation->status ?? null) !== 'Discharged')
                                        <span
                                            class="ipdb-pill ipdb-pill-soft-primary">{{ $latestAllocation->bed->name }}</span>
                                    @else
                                        <span class="text-muted small ipdb-pill ipdb-pill-soft-warning">Not Assigned</span>
                                    @endif
                                </td>
                                <td class="pe-4">
                                    <span class="ipdb-pill ipdb-pill-{{ $statusColor }}">
                                        {{ strtoupper($ipd->status ?? '-') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No Ipd patients found.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="ipdbPatientsNoResults" style="display:none;">
                            <td colspan="11" class="text-center text-muted py-5">
                                <i class="bi bi-search fs-1 d-block mb-2"></i>
                                No Ipd patients match your search.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if ($totalIpdPatients > 0)
            <div
                class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>Rows per page:</span>
                    <select id="ipdbPatientsPerPage" class="form-select form-select-sm" style="width:auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span id="ipdbPatientsRangeInfo"></span>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="ipdbPatientsPagination"></ul>
                </nav>
            </div>
        @endif
    </div>
</div>
