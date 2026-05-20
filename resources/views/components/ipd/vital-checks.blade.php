<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Vital Checks</h6>
        </div>
        <div>
            @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                <a data-size="lg" class="btn btn-primary px-2 w-100 w-sm-auto"
                    data-url="{{ route('ipd-patients.vital-checks', $iPDPatient->id) }}" data-ajax-popup="true"
                    data-title="Add Vital Check" data-bs-toggle="tooltip" title="Add Vital Check"
                    data-original-title="Add Vital Check"><i class="bi bi-plus-lg me-1"></i>
                    Add Vital Check</a>
            @endif
        </div>
    </div>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th width="3%">SN</th>
                <th width="20%">Date</th>
                <th width="8%">Weight(kg)</th>
                <th width="8%">Height(cm)</th>
                <th width="17%">BP (mmHg)</th>
                <th width="8%">Temp(°F)</th>
                <th width="8%">Heart Rate(bpm)</th>
                <th width="8%">SpO2(%)</th>
                <th width="9%">Resp Rate(breaths/min)</th>
                <th width="10%">Checked By</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($iPDPatient->vitalChecks->sortByDesc('checked_at') as $vital)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $vital->checked_at ? format_datetime($vital->checked_at) : 'N/A' }}</td>
                    <td>{{ $vital->weight ?? '-' }}</td>
                    <td>{{ $vital->height ?? '-' }}</td>
                    <td>{{ $vital->blood_pressure ?? '-' }}</td>
                    <td>
                        @if ($vital->temperature)
                            <span class="{{ $vital->temperature > 100.4 ? 'text-danger fw-bold' : '' }}">
                                {{ $vital->temperature }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $vital->heart_rate ?? '-' }}</td>
                    <td>
                        @if ($vital->spo2)
                            <span class="{{ $vital->spo2 < 95 ? 'text-danger fw-bold' : '' }}">
                                {{ $vital->spo2 }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $vital->respiratory_rate ?? '-' }}</td>
                    <td>{{ $vital->checkedByUser->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center text-muted">No vital checks found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Timeline View --}}
    {{-- <div class="mt-4">
        <h6 class="mb-3 fw-bold">Vital Check Timeline</h6>
        <div class="position-relative" style="padding-left: 40px;">
            <div class="position-absolute" style="left: 18px; top: 0; bottom: 0; width: 2px; background: #dee2e6;">
            </div>

            @foreach ($iPDPatient->vitalChecks->sortByDesc('checked_at') as $vital)
                <div class="position-relative mb-4">
                    <div class="position-absolute d-flex align-items-center justify-content-center rounded-circle bg-white border"
                        style="left: -32px; top: 0; width: 28px; height: 28px; z-index: 1;">
                        @if ($vital->temperature && $vital->temperature > 100.4)
                            <i class="bi bi-thermometer-high text-danger" style="font-size: 14px;"></i>
                        @elseif($vital->spo2 && $vital->spo2 < 95)
                            <i class="bi bi-lungs text-warning" style="font-size: 14px;"></i>
                        @else
                            <i class="bi bi-heart-pulse text-success" style="font-size: 14px;"></i>
                        @endif
                    </div>

                    <div class="text-muted small fw-semibold text-uppercase mb-1">
                        @if ($vital->checked_at->isToday())
                            TODAY, {{ format_datetime($vital->checked_at) }}
                        @elseif($vital->checked_at->isYesterday())
                            YESTERDAY, {{ format_datetime($vital->checked_at) }}
                        @else
                            {{ format_datetime($vital->checked_at) }}
                        @endif
                    </div>

                    @if ($vital->temperature && $vital->temperature > 100.4)
                        <span class="badge rounded-pill bg-danger mb-1">High Temp</span>
                    @endif
                    @if ($vital->spo2 && $vital->spo2 < 95)
                        <span class="badge rounded-pill bg-warning text-dark mb-1">Low SpO2</span>
                    @endif


                    <div class="fw-bold">
                        @if ($vital->temperature)Temp: {{ $vital->temperature }} @endif
                        @if ($vital->blood_pressure)| BP: {{ $vital->blood_pressure }} @endif
                        @if ($vital->heart_rate)| HR: {{ $vital->heart_rate }} @endif
                        @if ($vital->spo2)| SpO2: {{ $vital->spo2 }}% @endif
                    </div>


                    @if ($vital->remarks)
                        <div class="text-muted small">{{ $vital->remarks }}</div>
                    @endif

                    <div class="text-muted small mt-1">Checked by: {{ $vital->checkedByUser->name ?? '-' }}</div>
                </div>
            @endforeach
        </div>
    </div> --}}
</div>
