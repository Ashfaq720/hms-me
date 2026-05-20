@props(['opdPatient'])

@php
    $patient = $opdPatient->patient;
    $latestVital = ($opdPatient->vitalChecks ?? collect())->sortByDesc('created_at')->first();

    $totalVisits = \App\Models\OpdPatient::where('patient_id', $opdPatient->patient_id)->count();
    $recentVisits = \App\Models\OpdPatient::with(['doctor'])
        ->where('patient_id', $opdPatient->patient_id)
        ->latest('date')
        ->take(5)
        ->get();

    $totalPaid = \App\Models\Transaction::where('patient_id', $opdPatient->patient_id)
        ->where('section', 'opd')
        ->sum('net_amount');

    $age = calculateAgeFromDob($patient?->dob) ?? '-';
@endphp

{{-- OPD Overview (Ipd-style layout) --}}
<div class="p-2" style="font-size: 0.82rem;">

    {{-- Top Overview Section --}}
    <div class="row g-2 mb-4">

        {{-- Patient Header / Info --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-2">

                    <div class="row g-2 align-items-start">
                        {{-- Left: Image + Name --}}
                        <div class="col-md-3 text-center">
                            @if ($patient?->image)
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#patientImageModal">
                                    <img src="{{ asset('storage/' . $patient->image) }}"
                                        class="rounded-4 border shadow-sm object-fit-cover" role="button"
                                        width="100" height="110" alt="Patient Image">
                                </a>
                            @else
                                <div class="rounded-4 border d-flex align-items-center justify-content-center bg-light mx-auto"
                                    style="width:100px; height:110px;">
                                    <i class="bi bi-person fs-1 text-secondary"></i>
                                </div>
                            @endif

                            <div class="mt-2">
                                <span class="badge rounded-pill bg-primary px-2 py-1" style="font-size: 0.75rem;">
                                    Health Card Details
                                </span>
                            </div>

                            <h6 class="fw-bold mt-1 mb-0">
                                {{ $patient?->patient_name ?? '-' }}
                            </h6>

                            <div class="text-muted" style="font-size: 0.75rem;">
                                {{ $patient?->mrn ?? '' }}
                                <br>
                                Age: {{ $age }}
                            </div>

                            <div class="mt-2">
                                <a href="{{ route('opd-patients.print', $opdPatient->id) }}" target="_blank"
                                    class="btn btn-warning btn-sm w-100 fw-semibold rounded-3">
                                    <i class="bi bi-printer me-1"></i> Print
                                </a>
                            </div>
                        </div>

                        {{-- Middle: Patient Details --}}
                        <div class="col-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                <h6 class="fw-bold mb-0">Patient Information</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    <span
                                        class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill"
                                        style="font-size: 0.7rem;">
                                        {{ strtoupper($opdPatient->status ?? 'ACTIVE') }}
                                    </span>
                                    <span
                                        class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill"
                                        style="font-size: 0.7rem;">
                                        Case #{{ $opdPatient->case_id ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="row g-1" style="font-size: 0.78rem;">
                                <div class="col-6">
                                    <div class="text-muted mb-0">Gender</div>
                                    <div class="fw-semibold">{{ $patient?->gender ?? '-' }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Phone</div>
                                    <div class="fw-semibold">{{ $patient?->mobileno ?? '-' }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Guardian</div>
                                    <div class="fw-semibold">{{ $patient?->guardian_name ?? '-' }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">OPD No</div>
                                    <div class="fw-semibold">#OPD-{{ str_pad($opdPatient->id ?? 0, 4, '0', STR_PAD_LEFT) }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Visit Date</div>
                                    <div class="fw-semibold">
                                        {{ format_datetime($opdPatient->date) }}
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Blood Group</div>
                                    <div class="fw-semibold">{{ $patient?->blood_group ?? '-' }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Visit Type</div>
                                    <div class="fw-semibold text-capitalize">{{ $opdPatient->visit_type ?? 'New' }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Organization</div>
                                    <div class="fw-semibold text-primary">
                                        {{ $patient?->organization_name ?? '-' }}
                                    </div>
                                </div>

                                @if($opdPatient->chief_complaint)
                                <div class="col-12">
                                    <div class="text-muted mb-0">Chief Complaint</div>
                                    <div class="fw-semibold" style="white-space:pre-wrap;">{{ $opdPatient->chief_complaint }}</div>
                                </div>
                                @endif

                                @if($opdPatient->referral_source)
                                <div class="col-12">
                                    <div class="text-muted mb-0">Referral Source</div>
                                    <div class="fw-semibold">{{ $opdPatient->referral_source }}</div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Doctor + Actions --}}
                        <div class="col-md-4">
                            <div class="card border-0 bg-light rounded-3 mb-4">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center"
                                            style="width:40px; height:40px;">
                                            <i class="bi bi-hospital fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold">
                                                Consulting Doctor
                                            </div>
                                            <div class="fw-bold fs-6 text-primary">
                                                {{ $opdPatient->doctor?->name ?? 'Not Assigned' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-1">
                                @if ($patient)
                                    <button type="button"
                                        class="btn btn-outline-primary btn-sm rounded-3 fw-semibold"
                                        data-bs-toggle="modal" data-bs-target="#editPatientProfileModal">
                                        <i class="bi bi-pencil-square me-1"></i> Edit Profile
                                    </button>
                                @endif

                                <a href="{{ route('ipd-patients.create', ['patient_id' => $opdPatient->patient_id, 'from_opd_id' => $opdPatient->id]) }}"
                                    class="btn btn-info btn-sm rounded-3 fw-semibold text-white">
                                    <i class="bi bi-arrow-left-right me-1"></i> Move to Ipd
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-2">
                    <h6 class="fw-bold mb-2">Financial Summary</h6>

                    @php
                        $totalCharges = $opdPatient->charges->sum('net_amount');
                        $paidPercent = $totalCharges > 0 ? min(round(($totalPaid / $totalCharges) * 100), 100) : 0;

                        $financialItems = [
                            [
                                'label' => 'OPD',
                                'percent' => $paidPercent,
                                'class' => $paidPercent >= 100 ? 'bg-success' : ($paidPercent > 0 ? 'bg-primary' : 'bg-secondary'),
                                'text' => $paidPercent >= 100 ? 'text-success' : ($paidPercent > 0 ? 'text-primary' : 'text-muted'),
                            ],
                            ['label' => 'PHARMACY', 'percent' => 0, 'class' => 'bg-secondary', 'text' => 'text-muted'],
                            ['label' => 'PATHOLOGY', 'percent' => 0, 'class' => 'bg-secondary', 'text' => 'text-muted'],
                            ['label' => 'RADIOLOGY', 'percent' => 0, 'class' => 'bg-secondary', 'text' => 'text-muted'],
                            ['label' => 'APPOINTMENT', 'percent' => 0, 'class' => 'bg-secondary', 'text' => 'text-muted'],
                        ];
                    @endphp

                    @foreach ($financialItems as $item)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold">{{ $item['label'] }}</span>
                                <span class="fw-semibold {{ $item['text'] }}">
                                    {{ $item['percent'] }}{{ $item['percent'] > 0 ? '% PAID' : '%' }}
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $item['class'] }}" role="progressbar"
                                    style="width: {{ $item['percent'] }}%;" aria-valuenow="{{ $item['percent'] }}"
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Current Vitals --}}
    <div class="row g-2 mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold mb-0">Current Vitals</h6>
                            <i class="bi bi-arrow-clockwise text-muted small"></i>
                        </div>
                        @if ($latestVital && $latestVital->checked_at)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                <i class="bi bi-clock me-1"></i>{{ \Illuminate\Support\Carbon::parse($latestVital->checked_at)->format('d M Y, h:i A') }}
                            </span>
                        @endif
                    </div>

                    @if ($latestVital)
                        @php
                            // BP status
                            $bpParts = explode('/', $latestVital->blood_pressure ?? '');
                            $sys = (int) ($bpParts[0] ?? 0);
                            $dia = (int) ($bpParts[1] ?? 0);
                            if ($sys > 140 || $dia > 90) {
                                $bpStatus = 'HIGH'; $bpBadge = 'bg-danger-subtle text-danger'; $bpBorder = 'border-danger';
                            } elseif ($sys < 90 || $dia < 60) {
                                $bpStatus = 'LOW'; $bpBadge = 'bg-warning-subtle text-warning'; $bpBorder = 'border-warning';
                            } else {
                                $bpStatus = 'NORMAL'; $bpBadge = 'bg-success-subtle text-success'; $bpBorder = '';
                            }

                            // Heart Rate status
                            $pulse = $latestVital->heart_rate;
                            if ($pulse > 100) {
                                $pulseStatus = 'HIGH'; $pulseBadge = 'bg-danger-subtle text-danger'; $pulseBorder = 'border-danger';
                            } elseif ($pulse < 60) {
                                $pulseStatus = 'LOW'; $pulseBadge = 'bg-warning-subtle text-warning'; $pulseBorder = 'border-warning';
                            } else {
                                $pulseStatus = 'NORMAL'; $pulseBadge = 'bg-success-subtle text-success'; $pulseBorder = '';
                            }

                            // Temp status (°F)
                            $temp = $latestVital->temperature;
                            if ($temp > 99.5) {
                                $tempStatus = 'HIGH'; $tempBadge = 'bg-danger-subtle text-danger'; $tempBorder = 'border-danger';
                            } elseif ($temp < 96.8) {
                                $tempStatus = 'LOW'; $tempBadge = 'bg-warning-subtle text-warning'; $tempBorder = 'border-warning';
                            } else {
                                $tempStatus = 'NORMAL'; $tempBadge = 'bg-success-subtle text-success'; $tempBorder = '';
                            }

                            // SpO2 status
                            $spo2 = $latestVital->spo2;
                            if ($spo2 >= 95) {
                                $spo2Status = 'NORMAL'; $spo2Badge = 'bg-success-subtle text-success'; $spo2Border = '';
                            } elseif ($spo2 >= 90) {
                                $spo2Status = 'LOW'; $spo2Badge = 'bg-warning-subtle text-warning'; $spo2Border = 'border-warning';
                            } else {
                                $spo2Status = 'CRITICAL'; $spo2Badge = 'bg-danger text-white'; $spo2Border = 'border-danger';
                            }

                            // Resp Rate status
                            $respRate = $latestVital->respiratory_rate;
                            if ($respRate > 20) {
                                $respStatus = 'HIGH'; $respBadge = 'bg-danger-subtle text-danger'; $respBorder = 'border-danger';
                            } elseif ($respRate < 12) {
                                $respStatus = 'LOW'; $respBadge = 'bg-warning-subtle text-warning'; $respBorder = 'border-warning';
                            } else {
                                $respStatus = 'NORMAL'; $respBadge = 'bg-success-subtle text-success'; $respBorder = '';
                            }
                        @endphp

                        <div class="row g-2">
                            <div class="col-md-3 col-6">
                                <div class="border rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">WEIGHT</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $latestVital->weight ?? '-' }} <small class="fw-normal text-muted">kg</small></h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">HEIGHT</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $latestVital->height ?? '-' }} <small class="fw-normal text-muted">cm</small></h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $bpBorder }} rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">BP (mmHg)</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $latestVital->blood_pressure ?? '-' }}</h5>
                                        <span class="badge rounded-pill {{ $bpBadge }}">{{ $bpStatus }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $tempBorder }} rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">TEMP</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $temp ?? '-' }} <small class="fw-normal text-muted">°F</small></h5>
                                        <span class="badge rounded-pill {{ $tempBadge }}">{{ $tempStatus }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $pulseBorder }} rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">HEART RATE</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $pulse ?? '-' }} <small class="fw-normal text-muted">bpm</small></h5>
                                        <span class="badge rounded-pill {{ $pulseBadge }}">{{ $pulseStatus }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $spo2Border }} rounded-3 p-2 h-100">
                                    <div class="small {{ $spo2Status !== 'NORMAL' ? 'text-danger' : 'text-muted' }} fw-semibold mb-1">SPO2</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $spo2 ?? '-' }} <small class="fw-normal text-muted">%</small></h5>
                                        <span class="badge rounded-pill {{ $spo2Badge }}">{{ $spo2Status }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $respBorder }} rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">RESP RATE</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $respRate ?? '-' }} <small class="fw-normal text-muted">/min</small></h5>
                                        <span class="badge rounded-pill {{ $respBadge }}">{{ $respStatus }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-heart-pulse fs-4 d-block mb-1"></i>
                            No vital records found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Visits --}}
    <div class="row g-2 mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="p-2 pb-1">
                        <h6 class="fw-bold mb-0">Recent Visit Details</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-2 py-1">Case ID</th>
                                    <th class="py-1">Date</th>
                                    <th class="py-1">Doctor</th>
                                    <th class="py-1">Visit Type</th>
                                    <th class="py-1">Status</th>
                                    <th class="py-1">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentVisits as $visit)
                                    <tr @class(['table-active' => $visit->id === $opdPatient->id])>
                                        <td class="px-2">{{ $visit->case_id ?? '-' }}</td>
                                        <td>{{ $visit->date ? format_datetime($visit->date) : '-' }}</td>
                                        <td class="fw-semibold">{{ $visit->doctor->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary text-capitalize">{{ $visit->visit_type ?? 'new' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">{{ $visit->status ?? '-' }}</span>
                                        </td>
                                        <td class="text-muted">{{ $visit->remarks ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No recent visits.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Known Allergies + Medical Summary --}}
    <div class="row g-2 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-2">
                    <h6 class="fw-bold text-danger mb-2">
                        <i class="bi bi-exclamation-triangle me-2"></i>Known Allergies
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($patient?->known_allergies)
                            @foreach (explode(',', $patient->known_allergies) as $allergy)
                                <span class="badge rounded-pill bg-danger-subtle text-danger px-2 py-1">{{ trim($allergy) }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">No known allergies found.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-2">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="bi bi-clipboard2-pulse me-2"></i>Medical Summary
                    </h6>
                    <ul class="mb-0 ps-3 text-muted" style="font-size: 0.78rem;">
                        <li>Total OPD visits: <strong>{{ $totalVisits }}</strong></li>
                        <li>Prescriptions issued: <strong>{{ $opdPatient->prescriptions->count() }}</strong></li>
                        <li>Medications recorded: <strong>{{ $opdPatient->medications->count() }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-2">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-person-lines-fill me-2"></i>Patient Note
                    </h6>
                    <p class="mb-0 text-muted" style="font-size: 0.78rem;">
                        {{ $patient?->note ?: 'No notes available.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charges --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-2 mb-2 pb-1">
                <h6 class="fw-bold mb-0">Patient Charges</h6>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>SN</th>
                            <th class="px-2 py-1">Name</th>
                            <th class="py-1">Charge Type</th>
                            <th class="py-1">Unit Charge</th>
                            <th class="py-1">Qty</th>
                            <th class="py-1">Vat</th>
                            <th class="py-1">Tax</th>
                            <th class="py-1">Applied Charge</th>
                            <th class="py-1">Net Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($opdPatient->charges as $charge)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="px-2">{{ $charge->charge_item }}</td>
                                <td>{{ ucfirst($charge->charge_module) }}</td>
                                <td>{{ number_format($charge->unit_price, 2) }}</td>
                                <td>{{ $charge->quantity ?? '' }}</td>
                                <td>
                                    {{ number_format($charge->vat, 2) }}
                                    @if ($charge->amount > 0)
                                        <div class="small text-muted">
                                            ({{ number_format(($charge->vat / $charge->amount) * 100, 2) }}%)
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ number_format($charge->tax, 2) }}
                                    @if ($charge->amount > 0)
                                        <div class="small text-muted">
                                            ({{ number_format(($charge->tax / $charge->amount) * 100, 2) }}%)
                                        </div>
                                    @endif
                                </td>
                                <td>{{ number_format($charge->amount, 2) }}</td>
                                <td class="fw-bold">{{ number_format($charge->net_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">No charges found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Patient Documents --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-2 pb-1">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-paperclip me-1 text-primary"></i>Patient Documents
                    <span class="badge bg-secondary ms-1" style="font-size:11px;">{{ $opdPatient->documents->count() }}</span>
                </h6>
                <button type="button" class="btn btn-sm btn-primary"
                    data-bs-toggle="modal" data-bs-target="#addOpdDocModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Document
                </button>
            </div>

            @if ($opdPatient->documents->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-1">#</th>
                                <th class="px-2 py-1">Title</th>
                                <th class="py-1">Remarks</th>
                                <th class="py-1">Uploaded</th>
                                <th class="py-1 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($opdPatient->documents as $doc)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="px-2 fw-semibold">{{ $doc->title ?: '—' }}</td>
                                    <td class="text-muted">{{ $doc->remarks ?: '—' }}</td>
                                    <td>{{ $doc->created_at->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ asset('storage/' . $doc->file) }}" target="_blank"
                                                class="btn btn-xs btn-outline-primary" title="View / Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <form action="{{ route('opd-patients.documents.destroy', [$opdPatient->id, $doc->id]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Delete this document?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-folder2-open fs-4 d-block mb-1"></i>
                    No documents uploaded yet.
                </div>
            @endif
        </div>
    </div>

    {{-- Add Document Modal --}}
    <div class="modal fade" id="addOpdDocModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('opd-patients.documents.store', $opdPatient->id) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-paperclip me-2 text-primary"></i>Upload Patient Documents
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-2">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:30%;">Title</th>
                                        <th style="width:30%;">File <span class="text-danger">*</span></th>
                                        <th>Remarks</th>
                                        <th style="width:50px;" class="text-center">
                                            <button type="button" class="btn btn-xs btn-outline-primary" id="ovAddDocRow" title="Add row">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="ovDocTbody">
                                    <tr class="ov-doc-row">
                                        <td><input type="text" name="documents[0][title]" class="form-control form-control-sm" placeholder="e.g. Lab Report"></td>
                                        <td><input type="file" name="documents[0][file]" class="form-control form-control-sm" required></td>
                                        <td><input type="text" name="documents[0][remarks]" class="form-control form-control-sm" placeholder="Optional notes"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-outline-danger ov-remove-row" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Allowed: pdf, docx, png, jpg, jpeg &mdash; max 5 MB each.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-upload me-1"></i>Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var tbody  = document.getElementById('ovDocTbody');
        var addBtn = document.getElementById('ovAddDocRow');
        if (!tbody || !addBtn) return;
        var idx = 1;

        function rebuildIdx() {
            Array.from(tbody.querySelectorAll('tr.ov-doc-row')).forEach(function (row, i) {
                row.querySelectorAll('input').forEach(function (inp) {
                    var n = inp.getAttribute('name');
                    if (n) inp.setAttribute('name', n.replace(/documents\[\d+\]/, 'documents[' + i + ']'));
                });
            });
        }

        addBtn.addEventListener('click', function () {
            var row = document.createElement('tr');
            row.className = 'ov-doc-row';
            row.innerHTML =
                '<td><input type="text" name="documents[' + idx + '][title]" class="form-control form-control-sm" placeholder="e.g. Lab Report"></td>' +
                '<td><input type="file" name="documents[' + idx + '][file]" class="form-control form-control-sm" required></td>' +
                '<td><input type="text" name="documents[' + idx + '][remarks]" class="form-control form-control-sm" placeholder="Optional notes"></td>' +
                '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger ov-remove-row" title="Remove"><i class="bi bi-trash"></i></button></td>';
            tbody.appendChild(row);
            idx++;
        });

        tbody.addEventListener('click', function (e) {
            var btn = e.target.closest('.ov-remove-row');
            if (!btn) return;
            var rows = tbody.querySelectorAll('tr.ov-doc-row');
            if (rows.length <= 1) {
                btn.closest('tr').querySelectorAll('input').forEach(function (i) { i.value = ''; });
            } else {
                btn.closest('tr').remove();
                rebuildIdx();
            }
        });

        // Re-open modal on validation error
        @if (session('success') || $errors->has('documents'))
        var modal = document.getElementById('addOpdDocModal');
        if (modal && {{ $errors->has('documents') ? 'true' : 'false' }}) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
        @endif

        // Move modal to body to avoid overflow clipping
        var modal = document.getElementById('addOpdDocModal');
        if (modal) document.body.appendChild(modal);
    });
    </script>

    {{-- Payment --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="p-2 pb-1">
                <h6 class="fw-bold mb-0">PAYMENT</h6>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-1">SN</th>
                            <th class="px-2 py-1">Invoice No.</th>
                            <th class="py-1">Date</th>
                            <th class="py-1">Type</th>
                            <th class="py-1">Payment Mode</th>
                            <th class="py-1">Amount</th>
                            <th class="py-1">Vat</th>
                            <th class="py-1">Tax</th>
                            <th class="py-1">Total</th>
                            <th class="py-1">Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($opdPatient->transactions as $transaction)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="px-4 fw-bold">{{ $transaction->invoice_no }}</td>
                                <td>{{ format_datetime($transaction->payment_date) }}</td>
                                <td>{{ $transaction->type }}</td>
                                <td>{{ ucfirst($transaction->payment_via ?? '-') }}</td>
                                <td>{{ number_format($transaction->amount, 2) }}</td>
                                <td>{{ number_format($transaction->vat, 2) }}</td>
                                <td>{{ number_format($transaction->tax, 2) }}</td>
                                <td class="fw-bold">{{ number_format($transaction->net_amount, 2) }}</td>
                                <td>{{ $transaction->received_by ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">No payment records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Modals are moved to document.body via JS to avoid overflow clipping inside tab-pane --}}

{{-- Patient Image Zoom Modal --}}
@if ($patient?->image)
    <div class="modal fade" id="patientImageModal" tabindex="-1" aria-label="Patient Image" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-3 overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">{{ $patient?->patient_name ?? 'Patient' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img src="{{ asset('storage/' . $patient->image) }}"
                        class="img-fluid rounded-4 shadow" alt="Patient Image">
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Edit Patient Profile Modal --}}
@if ($patient)
    <div class="modal fade" id="editPatientProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('patients.update', $patient->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_redirect" value="back">

                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-person-gear me-2 text-primary"></i>Edit Patient Profile
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12 text-center">
                                <div class="rounded-4 border d-inline-flex align-items-center justify-content-center overflow-hidden"
                                    style="width:100px; height:110px;">
                                    <img id="editPatientImagePreview"
                                        src="{{ $patient->image ? asset('storage/' . $patient->image) : asset('backend/images/no-image.png') }}"
                                        alt="Patient" class="object-fit-cover" width="100" height="110">
                                </div>
                                <input type="file" name="image" accept="image/*" class="form-control form-control-sm mt-2"
                                    onchange="(function(e){var r=new FileReader();r.onload=function(ev){document.getElementById('editPatientImagePreview').src=ev.target.result;};if(e.target.files[0])r.readAsDataURL(e.target.files[0]);})(event)">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Patient Name <span class="text-danger">*</span></label>
                                <input type="text" name="patient_name" class="form-control" value="{{ old('patient_name', $patient->patient_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile No <span class="text-danger">*</span></label>
                                <input type="text" name="mobileno" class="form-control" value="{{ old('mobileno', $patient->mobileno) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    @foreach (['Male','Female','Other'] as $g)
                                        <option value="{{ $g }}" @selected(old('gender', $patient->gender) === $g)>{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">DOB</label>
                                <input type="date" name="dob" class="form-control" value="{{ old('dob', $patient->dob ? \Illuminate\Support\Carbon::parse($patient->dob)->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Marital Status <span class="text-danger">*</span></label>
                                <select name="marital_status" class="form-select" required>
                                    @foreach (['Single','Married','Divorced','Widowed'] as $m)
                                        <option value="{{ $m }}" @selected(old('marital_status', $patient->marital_status) === $m)>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Blood Group</label>
                                <select name="blood_group" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                        <option value="{{ $bg }}" @selected(old('blood_group', $patient->blood_group) === $bg)>{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Guardian Name</label>
                                <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $patient->guardian_name) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" rows="2" class="form-control">{{ old('address', $patient->address) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Known Allergies</label>
                                <input type="text" name="known_allergies" class="form-control" value="{{ old('known_allergies', $patient->known_allergies) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Note</label>
                                <input type="text" name="note" class="form-control" value="{{ old('note', $patient->note) }}">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-save me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Move modals to body so they are not clipped by parent overflow/z-index --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['patientImageModal', 'editPatientProfileModal'].forEach(function (id) {
            var modal = document.getElementById(id);
            if (modal) document.body.appendChild(modal);
        });
    });
</script>
