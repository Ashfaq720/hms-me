@extends('backend.layouts.master')

@section('title', 'NICU Live Dashboard')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="bi bi-emoji-smile"></i> NICU Live Dashboard</h4>

    <div class="row g-3 mb-4">
        @php $cards = [
            ['label' => 'Active Admissions',  'value' => $kpi['active_admissions'],     'color' => 'primary'],
            ['label' => 'Critical',           'value' => $kpi['critical_count'],        'color' => 'danger'],
            ['label' => 'Preterm',            'value' => $kpi['preterm_count'],         'color' => 'warning'],
            ['label' => 'Low Birth Weight',   'value' => $kpi['lbw_count'],             'color' => 'warning'],
            ['label' => 'Incubators in Use',  'value' => $kpi['incubators_in_use'],     'color' => 'info'],
            ['label' => 'Warmers in Use',     'value' => $kpi['warmers_in_use'],        'color' => 'info'],
            ['label' => 'Critical Alerts 24h','value' => $kpi['critical_alerts_24h'],   'color' => 'danger'],
            ['label' => 'Active Infections',  'value' => $kpi['active_infections'],     'color' => 'secondary'],
        ]; @endphp
        @foreach ($cards as $c)
            <div class="col-md-3 col-lg-3">
                <div class="card border-0 shadow-sm h-100 bg-{{ $c['color'] }} bg-opacity-10">
                    <div class="card-body">
                        <small class="text-{{ $c['color'] }}">{{ $c['label'] }}</small>
                        <h3 class="mb-0">{{ $c['value'] }}</h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($kpi['cluster_alerts'] > 0)
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Infection cluster detected:</strong> {{ $kpi['cluster_alerts'] }} clusters need infection-control review.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Currently Admitted Neonates</h6>
            <a href="{{ route('nicu.admissions.index') }}" class="btn btn-sm btn-outline-primary">All Admissions →</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Baby ID</th>
                        <th>Name</th>
                        <th>Mother</th>
                        <th>Birth wt</th>
                        <th>GA</th>
                        <th>APGAR</th>
                        <th>Risk flags</th>
                        <th>Resources</th>
                        <th>Admitted</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($admissions as $a)
                    <tr>
                        <td><strong>{{ $a->baby_id }}</strong></td>
                        <td>{{ $a->patient->patient_name ?? '—' }}</td>
                        <td>{{ $a->mother->patient_name ?? '—' }}</td>
                        <td>{{ $a->birth_weight_g ? number_format($a->birth_weight_g) . 'g' : '—' }}</td>
                        <td>{{ $a->gestational_age_weeks ? $a->gestational_age_weeks . 'w' : '—' }}</td>
                        <td>{{ $a->apgar_1min ?? '?' }}/{{ $a->apgar_5min ?? '?' }}</td>
                        <td>
                            @if ($a->is_critical)<span class="badge bg-danger">Critical</span>@endif
                            @if ($a->is_preterm)<span class="badge bg-warning text-dark">Preterm</span>@endif
                            @if ($a->is_low_birth_weight)<span class="badge bg-warning text-dark">LBW</span>@endif
                        </td>
                        <td>
                            @foreach ($a->resources as $r)
                                <span class="badge bg-info bg-opacity-10 text-info">{{ $r->resource_type }}</span>
                            @endforeach
                        </td>
                        <td>{{ $a->admission_time?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No active admissions</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
