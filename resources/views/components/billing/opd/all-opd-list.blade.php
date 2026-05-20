{{-- All OPD Tab --}}
<div class="tab-pane fade  show active" id="all-opd" role="tabpanel">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-1">All OPD Patients</h5>
                    <div class="text-muted small">
                        Total <strong>{{ number_format($totalOpdPatients) }}</strong> patients
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm opdb-search" style="width: 260px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input id="opdbPatientsSearch" type="text" class="form-control border-start-0 ps-0"
                            placeholder="Search OPD no, patient, doctor...">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table opdb-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width:50px;">#</th>
                            <th>OPD No</th>
                            <th>Patient</th>
                            <th>Mobile</th>
                            <th>Gender</th>
                            <th>Doctor</th>
                            <th>Department</th>
                            <th>Visit Date</th>
                            <th>Visit Type</th>
                            <th class="pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($opdPatients as $i => $opd)
                            @php
                                $statusColor = match (strtolower($opd->status ?? '')) {
                                    'registered', 'active' => 'success',
                                    'completed', 'closed' => 'info',
                                    'cancelled', 'canceled' => 'danger',
                                    default => 'warning',
                                };
                                $opdNo = $opd->token_no ?? ($opd->serial_no ?? ('OPD-' . $opd->id));
                                $visitDate = $opd->visit_date
                                    ? \Illuminate\Support\Carbon::parse($opd->visit_date)
                                    : ($opd->date ?: null);
                            @endphp
                            <tr class="opdb-patient-row" data-index="{{ $i }}">
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <a href="{{ route('billing.opd-billing.show', $opd->id) }}"
                                        class="opdb-bill-link">{{ $opdNo }}</a>
                                </td>
                                <td>
                                    <div class="fw-semibold lh-sm">{{ $opd->patient?->patient_name ?? '-' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        {{ $opd->patient?->mrn ?? '' }}</div>
                                </td>
                                <td>{{ $opd->patient?->mobileno ?? '-' }}</td>
                                <td>{{ $opd->patient?->gender ?? '-' }}</td>
                                <td>{{ $opd->doctor?->name ?? '-' }}</td>
                                <td>{{ $opd->department?->name ?? '-' }}</td>
                                <td>
                                    @if ($visitDate)
                                        <div class="fw-semibold">
                                            {{ $visitDate->format('d/m/Y') }}</div>
                                        <div class="text-muted small">
                                            {{ $visitDate->format('h:i A') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $opd->visit_type ?? '-' }}</td>
                                <td class="pe-4">
                                    <span class="opdb-pill opdb-pill-{{ $statusColor }}">
                                        {{ strtoupper($opd->status ?? '-') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No OPD patients found.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="opdbPatientsNoResults" style="display:none;">
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="bi bi-search fs-1 d-block mb-2"></i>
                                No OPD patients match your search.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if ($totalOpdPatients > 0)
            <div
                class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>Rows per page:</span>
                    <select id="opdbPatientsPerPage" class="form-select form-select-sm" style="width:auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span id="opdbPatientsRangeInfo"></span>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="opdbPatientsPagination"></ul>
                </nav>
            </div>
        @endif
    </div>
</div>
