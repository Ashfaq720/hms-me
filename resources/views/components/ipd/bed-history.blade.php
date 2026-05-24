@php
    $latestAlloc = $bedAllocations->sortByDesc('id')->first();
    $currentIsIcu = $latestAlloc && ($latestAlloc->allocation_type ?? 'bed') === 'icu';
@endphp
<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Patient Bed / ICU History</h6>
        </div>
        <div class="d-flex gap-2">
            @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary"
                    data-url="{{ route('ipd-patients.bed-transfer', $iPDPatient->id) }}"
                    data-size="xl" data-ajax-popup="true"
                    data-title="{{ $currentIsIcu ? 'Transfer ICU → Bed' : 'Bed Transfer' }}">
                    <i class="fi fi-rr-exchange me-2"></i>
                    {{ $currentIsIcu ? 'Transfer to Bed' : 'Bed Transfer' }}
                </a>
                @if (! $currentIsIcu)
                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger"
                        data-url="{{ route('ipd-patients.icu-transfer', $iPDPatient->id) }}"
                        data-size="xl" data-ajax-popup="true" data-title="Transfer to ICU">
                        <i class="bi bi-heart-pulse me-2"></i> Transfer to ICU / CCU
                    </a>
                @endif
            @endif
        </div>
    </div>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>SN</th>
                <th scope="col">Bed Name</th>
                <th scope="col">Type</th>
                <th scope="col">From Date</th>
                <th scope="col">To Date</th>
                <th scope="col">Remarks</th>
                <th scope="col">Status</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bedAllocations as $allocation)
                @php $allocIsIcu = ($allocation->allocation_type ?? 'bed') === 'icu'; @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $allocation->bed->name }}</td>
                    <td>
                        @if ($allocIsIcu)
                            <span class="badge bg-danger"><i class="bi bi-heart-pulse"></i> ICU</span>
                        @else
                            <span class="badge bg-info">Bed</span>
                        @endif
                    </td>
                    <td>{{ $allocation->from ? format_datetime($allocation->from) : 'N/A' }}</td>
                    <td>{{ $allocation->to ? format_datetime($allocation->to) : 'Present' }}</td>
                    <td>{{ $allocation->remarks ?? '-' }}</td>
                    <td>{{ $allocation->status ?? '-' }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-start">
                            <a class="btn btn-sm btn-outline-info" data-ajax-popup="true"
                                data-url="{{ route('ipd-patients.bed-allocations.show', [$iPDPatient->id, $allocation->id]) }}"
                                data-title="Bed Allocation Details" data-size="lg" data-bs-toggle="tooltip"
                                title="Show">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if (is_null($allocation->to))
                                <a class="btn btn-sm btn-outline-primary" data-ajax-popup="true"
                                    data-url="{{ route('ipd-patients.bed-allocations.edit', [$iPDPatient->id, $allocation->id]) }}"
                                    data-title="Edit Bed Allocation" data-size="lg" data-bs-toggle="tooltip"
                                    title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No bed allocation history available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Bed History Timeline --}}
@if ($bedAllocations->count())
    <div class="mt-4">
        <h6 class="fw-bold mb-3">Bed History Timeline</h6>
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center flex-wrap gap-2" style="overflow-x: auto; padding: 10px 0;">

                    {{-- Admission Node --}}
                    @if ($iPDPatient && $iPDPatient->admission_date)
                        @php $admColor = '#0d6efd'; @endphp
                        <div class="text-center" style="min-width: 140px;">
                            <div class="d-flex justify-content-center mb-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px; background-color: {{ $admColor }}15; border: 2px solid {{ $admColor }};">
                                    <i class="bi bi-hospital"
                                        style="font-size: 1.3rem; color: {{ $admColor }};"></i>
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: {{ $admColor }};">
                                {{ $iPDPatient->admission_date->format('d M Y') }}
                            </div>
                            <div style="font-size: 0.8rem; color: {{ $admColor }}; font-weight: 600;">
                                {{ $iPDPatient->admission_date->format('h:i A') }}
                            </div>
                            <div class="mt-1">
                                <span class="badge"
                                    style="background-color: {{ $admColor }}; font-size: 0.7rem;">Admitted</span>
                            </div>
                            <div style="font-size: 0.75rem; color: #555; margin-top: 4px;">
                                {{ $iPDPatient->ipd_no }}
                            </div>
                        </div>


                        @if ($bedAllocations->count())
                            <div class="d-flex align-items-center" style="color: #aaa;">
                                <i class="bi bi-arrow-right" style="font-size: 1.5rem;"></i>
                            </div>
                        @endif
                    @endif

                    {{-- Bed Allocation Nodes --}}
                    @foreach ($bedAllocations->sortBy('from') as $index => $allocation)
                        @php
                            $statusColors = [
                                'ACTIVE' => '#28a745',
                                'TRANSFERRED' => '#E30B56',
                                'RELEASED' => '#6c757d',
                            ];
                            $statusIcons = [
                                'ACTIVE' => 'fa-solid fa-bed',
                                'TRANSFERRED' => 'fa-solid fa-bed',
                                'RELEASED' => 'fa-solid fa-bed',
                            ];
                            $color = $statusColors[$allocation->status] ?? '#6c757d';
                            $icon = $statusIcons[$allocation->status] ?? 'fa-solid fa-bed';
                        @endphp

                        <div class="text-center" style="min-width: 140px;">
                            <div class="d-flex justify-content-center mb-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px; background-color: {{ $color }}15; border: 2px solid {{ $color }};">
                                    <i class="{{ $icon }}"
                                        style="font-size: 1.3rem; color: {{ $color }};"></i>
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: {{ $color }};">
                                {{ $allocation->from ? \Carbon\Carbon::parse($allocation->from)->format('d M Y') : '' }}
                            </div>
                            <div style="font-size: 0.8rem; color: {{ $color }}; font-weight: 600;">
                                {{ $allocation->from ? \Carbon\Carbon::parse($allocation->from)->format('h:i A') : '' }}
                            </div>
                            <div class="mt-1">
                                <span class="badge" style="background-color: {{ $color }}; font-size: 0.7rem;">
                                    {{ ucfirst(strtolower($allocation->status)) ?? 'N/A' }}
                                </span>
                            </div>
                            <div style="font-size: 0.75rem; color: #555; margin-top: 4px;">
                                {{ $allocation->bed->name ?? 'N/A' }}
                            </div>
                        </div>

                        @if (!$loop->last || ($iPDPatient && $iPDPatient->discharge_date))
                            <div class="d-flex align-items-center" style="color: #aaa;">
                                <i class="bi bi-arrow-right" style="font-size: 1.5rem;"></i>
                            </div>
                        @endif
                    @endforeach

                    {{-- Discharge Node --}}
                    @if ($iPDPatient && $iPDPatient->discharge_date)
                        @php $disColor = '#e67e22'; @endphp
                        <div class="text-center" style="min-width: 140px;">
                            <div class="d-flex justify-content-center mb-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px; background-color: {{ $disColor }}15; border: 2px solid {{ $disColor }};">
                                    <i class="bi bi-box-arrow-right"
                                        style="font-size: 1.3rem; color: {{ $disColor }};"></i>
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: {{ $disColor }};">
                                {{ $iPDPatient->discharge_date->format('d M Y') }}
                            </div>
                            <div style="font-size: 0.8rem; color: {{ $disColor }}; font-weight: 600;">
                                {{ $iPDPatient->discharge_date->format('h:i A') }}
                            </div>
                            <div class="mt-1">
                                <span class="badge"
                                    style="background-color: {{ $disColor }}; font-size: 0.7rem;">Discharge</span>
                            </div>

                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endif
