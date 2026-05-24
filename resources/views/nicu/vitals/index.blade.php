@extends('backend.layouts.master')
@section('title', 'NICU Vitals')
@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="bi bi-activity"></i> NICU Vital Monitoring</h4>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr>
                    <th>Time</th><th>Baby</th><th>HR</th><th>RR</th><th>SpO2</th><th>Temp °C</th><th>Glucose</th><th>Source</th><th>Alert</th><th>Notes</th>
                </tr></thead>
                <tbody>
                @forelse ($vitals as $v)
                    <tr>
                        <td>{{ $v->recorded_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ optional(optional($v->admission)->patient)->patient_name ?? '—' }}</td>
                        <td>{{ $v->heart_rate ?? '—' }}</td>
                        <td>{{ $v->respiratory_rate ?? '—' }}</td>
                        <td>{{ $v->spo2 ?? '—' }}</td>
                        <td>{{ $v->temperature_c ?? '—' }}</td>
                        <td>{{ $v->blood_glucose_mgdl ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $v->source }}</span></td>
                        <td>
                            @php $color = ['NORMAL'=>'success','WARNING'=>'warning','CRITICAL'=>'danger'][$v->alert_level] ?? 'secondary'; @endphp
                            <span class="badge bg-{{ $color }}">{{ $v->alert_level }}</span>
                            @if ($v->alert_apnea)<span class="badge bg-danger ms-1">Apnea</span>@endif
                            @if ($v->alert_hypothermia)<span class="badge bg-info ms-1">Hypothermia</span>@endif
                            @if ($v->alert_spo2_critical)<span class="badge bg-danger ms-1">SpO2</span>@endif
                            @if ($v->alert_hr_abnormal)<span class="badge bg-warning text-dark ms-1">HR</span>@endif
                        </td>
                        <td>{{ $v->notes }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No vitals recorded</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($vitals, 'links'))<div class="p-3">{{ $vitals->links() }}</div>@endif
    </div>
</div>
@endsection
