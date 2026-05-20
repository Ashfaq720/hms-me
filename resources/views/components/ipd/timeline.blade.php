@props(['iPDPatient'])

@php
    $events = collect();

    // Admission
    if ($iPDPatient->admission_date) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($iPDPatient->admission_date),
            'icon'  => 'bi-person-plus-fill',
            'color' => 'primary',
            'title' => 'Patient admitted',
            'desc'  => 'Ipd No: ' . ($iPDPatient->ipd_no ?? '-') . ' • Case ID: ' . ($iPDPatient->case_id ?? '-'),
        ]);
    }

    // Bed Allocations
    foreach ($iPDPatient->bedAllocations ?? [] as $ba) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($ba->allocate_date ?? $ba->created_at),
            'icon'  => 'bi-hospital',
            'color' => 'secondary',
            'title' => 'Bed allocated',
            'desc'  => trim(collect([
                $ba->bed->name ?? null,
                $ba->bedType->title ?? null,
            ])->filter()->implode(' • ')) ?: null,
        ]);
    }

    // Vital Checks
    foreach ($iPDPatient->vitalChecks ?? [] as $vc) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($vc->created_at),
            'icon'  => 'bi-heart-pulse-fill',
            'color' => 'danger',
            'title' => 'Vital check recorded',
            'desc'  => trim(collect([
                $vc->blood_pressure ? 'BP ' . $vc->blood_pressure : null,
                $vc->pulse ? 'Pulse ' . $vc->pulse : null,
                $vc->temperature ? 'Temp ' . $vc->temperature : null,
            ])->filter()->implode(' • ')) ?: null,
        ]);
    }

    // Nurse Notes
    foreach ($iPDPatient->nurseNotes ?? [] as $nn) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($nn->date ?? $nn->created_at),
            'icon'  => 'bi-journal-medical',
            'color' => 'info',
            'title' => 'Nurse note',
            'desc'  => $nn->description ?? ($nn->note ?? null),
        ]);
    }

    // Round Doctor
    foreach ($iPDPatient->roundDrs ?? [] as $rd) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($rd->date ?? $rd->created_at),
            'icon'  => 'bi-person-badge',
            'color' => 'primary',
            'title' => 'Round doctor visit',
            'desc'  => $rd->description ?? null,
        ]);
    }

    // Case Doctor
    foreach ($iPDPatient->caseDrs ?? [] as $cd) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($cd->date ?? $cd->created_at),
            'icon'  => 'bi-person-badge-fill',
            'color' => 'primary',
            'title' => 'Case doctor note',
            'desc'  => $cd->description ?? null,
        ]);
    }

    // Operation Histories
    foreach ($iPDPatient->operationHistories ?? [] as $op) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($op->date ?? $op->created_at),
            'icon'  => 'bi-scissors',
            'color' => 'dark',
            'title' => 'Operation: ' . ($op->operation->name ?? $op->name ?? '-'),
            'desc'  => $op->description ?? null,
        ]);
    }

    // Medicine Orders
    foreach ($iPDPatient->medicineOrders ?? [] as $mo) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($mo->date ?? $mo->created_at),
            'icon'  => 'bi-prescription2',
            'color' => 'success',
            'title' => 'Medicine order',
            'desc'  => $mo->note ?? null,
        ]);
    }

    // Medications
    foreach ($iPDPatient->medications ?? [] as $med) {
        $events->push([
            'date'  => $med->datetime ?? $med->created_at,
            'icon'  => 'bi-capsule-pill',
            'color' => 'success',
            'title' => 'Medication: ' . ($med->medicine->medicine_name ?? 'N/A'),
            'desc'  => trim(collect([$med->dosage ?? null, $med->medicated_by ?? null])->filter()->implode(' • ')) ?: null,
        ]);
    }

    // Pathology Orders
    foreach ($iPDPatient->pathologyOrders ?? [] as $po) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($po->date ?? $po->created_at),
            'icon'  => 'bi-clipboard2-pulse',
            'color' => 'warning',
            'title' => 'Pathology order',
            'desc'  => $po->pathology->name ?? null,
        ]);
    }

    // Radiology Orders
    foreach ($iPDPatient->radiologyOrders ?? [] as $ro) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($ro->date ?? $ro->created_at),
            'icon'  => 'bi-clipboard2-pulse-fill',
            'color' => 'warning',
            'title' => 'Radiology order',
            'desc'  => $ro->radiology->name ?? null,
        ]);
    }

    // Treatment Histories
    foreach ($iPDPatient->treatmentHistories ?? [] as $th) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($th->date ?? $th->created_at),
            'icon'  => 'bi-clipboard-heart',
            'color' => 'info',
            'title' => 'Treatment history',
            'desc'  => $th->description ?? null,
        ]);
    }

    // Charges
    foreach ($iPDPatient->charges ?? [] as $ch) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($ch->date),
            'icon'  => 'bi-receipt-cutoff',
            'color' => 'warning',
            'title' => 'Charge added: ' . ($ch->charge_item ?? '-'),
            'desc'  => 'Qty ' . $ch->quantity . ' • Net ' . number_format($ch->net_amount, 2),
        ]);
    }

    // Payments
    foreach ($iPDPatient->transactions ?? [] as $tx) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($tx->payment_date),
            'icon'  => 'bi-cash-coin',
            'color' => 'info',
            'title' => 'Payment received: ' . number_format($tx->net_amount, 2),
            'desc'  => ucfirst($tx->payment_via ?? '-') . ($tx->invoice_no ? ' • ' . $tx->invoice_no : ''),
        ]);
    }

    // Discharge
    if ($iPDPatient->discharge_date) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($iPDPatient->discharge_date),
            'icon'  => 'bi-box-arrow-right',
            'color' => 'success',
            'title' => 'Patient discharged',
            'desc'  => null,
        ]);
    }

    $events = $events->filter(fn($e) => !empty($e['date']))->sortByDesc(fn($e) => $e['date']);
@endphp

<div class="card border shadow-sm rounded-3">
    <div class="card-header bg-light border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-clock-history me-2 text-dark"></i>Patient Timeline
        </h6>
        <span class="badge bg-primary">{{ $events->count() }} events</span>
    </div>
    <div class="card-body p-0">
        @if ($events->isEmpty())
            <div class="text-center text-muted py-4">No timeline events yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th style="width: 70px;" class="text-center">Type</th>
                            <th>Event</th>
                            <th>Details</th>
                            <th style="width: 180px;">Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events->values() as $i => $event)
                            <tr class="timeline-row" data-index="{{ $i }}">
                                <td class="text-center fw-semibold text-muted">{{ $i + 1 }}</td>
                                <td class="text-center">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $event['color'] }}-subtle text-{{ $event['color'] }}"
                                        style="width: 36px; height: 36px;">
                                        <i class="bi {{ $event['icon'] }} fs-5"></i>
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $event['title'] }}</td>
                                <td class="text-muted small">{{ $event['desc'] ?? '-' }}</td>
                                <td class="small text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ \Illuminate\Support\Carbon::parse($event['date'])->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>Rows per page:</span>
                    <select id="timelinePerPage" class="form-select form-select-sm" style="width:auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span id="timelineRangeInfo"></span>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="timelinePagination"></ul>
                </nav>
            </div>
        @endif
    </div>
</div>

<script>
    (function () {
        const rows = Array.from(document.querySelectorAll('.timeline-row'));
        if (!rows.length) return;
        const perPageSel = document.getElementById('timelinePerPage');
        const pagination = document.getElementById('timelinePagination');
        const rangeInfo = document.getElementById('timelineRangeInfo');
        let currentPage = 1;

        function render() {
            const perPage = parseInt(perPageSel.value, 10);
            const total = rows.length;
            const totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) currentPage = totalPages;
            const start = (currentPage - 1) * perPage;
            const end = Math.min(start + perPage, total);

            rows.forEach((r, i) => {
                r.style.display = (i >= start && i < end) ? '' : 'none';
            });

            rangeInfo.textContent = `Showing ${total ? start + 1 : 0}–${end} of ${total}`;

            let html = '';
            const mkItem = (label, page, disabled, active) =>
                `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${page}">${label}</a>
                 </li>`;
            html += mkItem('&laquo;', currentPage - 1, currentPage === 1, false);
            for (let p = 1; p <= totalPages; p++) {
                if (p === 1 || p === totalPages || Math.abs(p - currentPage) <= 1) {
                    html += mkItem(p, p, false, p === currentPage);
                } else if (Math.abs(p - currentPage) === 2) {
                    html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                }
            }
            html += mkItem('&raquo;', currentPage + 1, currentPage === totalPages, false);
            pagination.innerHTML = html;

            pagination.querySelectorAll('a.page-link').forEach(a => {
                a.addEventListener('click', e => {
                    e.preventDefault();
                    const p = parseInt(a.dataset.page, 10);
                    if (!isNaN(p) && p >= 1 && p <= totalPages) {
                        currentPage = p;
                        render();
                    }
                });
            });
        }

        perPageSel.addEventListener('change', () => { currentPage = 1; render(); });
        render();
    })();
</script>
