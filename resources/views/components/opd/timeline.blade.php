@props(['opdPatient'])

@php
    $events = collect();

    // Registration
    if ($opdPatient->date) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($opdPatient->date),
            'icon'  => 'bi-person-plus-fill',
            'color' => 'primary',
            'title' => ucfirst($opdPatient->visit_type ?? 'new') . ' OPD visit registered',
            'desc'  => 'Case ID: ' . ($opdPatient->case_id ?? '-'),
        ]);
    }

    // Vital Checks
    foreach ($opdPatient->vitalChecks ?? [] as $vc) {
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

    // Medications
    foreach ($opdPatient->medications ?? [] as $med) {
        $events->push([
            'date'  => $med->datetime,
            'icon'  => 'bi-capsule-pill',
            'color' => 'success',
            'title' => 'Medication: ' . ($med->medicine->medicine_name ?? 'N/A'),
            'desc'  => trim(collect([$med->dosage, $med->medicated_by])->filter()->implode(' • ')) ?: null,
        ]);
    }

    // Charges
    foreach ($opdPatient->charges ?? [] as $ch) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($ch->date),
            'icon'  => 'bi-receipt-cutoff',
            'color' => 'warning',
            'title' => 'Charge added: ' . ($ch->charge_item ?? '-'),
            'desc'  => 'Qty ' . $ch->quantity . ' • Net ' . number_format($ch->net_amount, 2),
        ]);
    }

    // Payments
    foreach ($opdPatient->transactions ?? [] as $tx) {
        $events->push([
            'date'  => \Illuminate\Support\Carbon::parse($tx->payment_date),
            'icon'  => 'bi-cash-coin',
            'color' => 'info',
            'title' => 'Payment received: ' . number_format($tx->net_amount, 2),
            'desc'  => ucfirst($tx->payment_via ?? '-') . ($tx->invoice_no ? ' • ' . $tx->invoice_no : ''),
        ]);
    }

    $events = $events->sortByDesc(fn($e) => $e['date']);
@endphp

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-clock-history me-2 text-dark"></i>Patient Timeline
            <span class="badge bg-secondary-subtle text-secondary ms-2">{{ $events->count() }}</span>
        </h6>
    </div>
    <div class="card-body">
        @if ($events->isEmpty())
            <div class="text-center text-muted py-3">No timeline events yet.</div>
        @else
            <div class="timeline-simple">
                @foreach ($events as $event)
                    <div class="timeline-item border-{{ $event['color'] }}"
                        style="border-left-color: var(--bs-{{ $event['color'] }}) !important;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi {{ $event['icon'] }} text-{{ $event['color'] }} fs-5"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <strong>{{ $event['title'] }}</strong>
                                    <small class="text-muted">
                                        {{ $event['date'] ? $event['date']->format('d M Y, h:i A') : '-' }}
                                    </small>
                                </div>
                                @if (!empty($event['desc']))
                                    <div class="small text-muted">{{ $event['desc'] }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
