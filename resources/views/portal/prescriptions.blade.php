@extends('portal.layout')
@section('title', 'My Prescriptions')

@section('content')
<h4 class="mb-3"><i class="bi bi-prescription2"></i> My Prescriptions</h4>

@forelse ($prescriptions as $rx)
    <div class="card portal-card mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $rx->prescription_no ?? '#'.$rx->id }}</strong>
                <small class="text-muted ms-2">{{ \Carbon\Carbon::parse($rx->date ?? $rx->created_at)->format('Y-m-d') }}</small>
            </div>
            <small class="text-muted">By <strong>{{ optional($rx->doctor)->name ?? '—' }}</strong></small>
        </div>
        <div class="card-body">
            @if ($rx->findings)
                <p><strong>Findings:</strong> {{ $rx->findings }}</p>
            @endif
            @if ($rx->symptoms->count())
                <div class="mb-2"><strong>Symptoms:</strong>
                    @foreach ($rx->symptoms as $s)
                        <span class="badge bg-light text-dark border">{{ optional($s->symptom)->name ?? '—' }}</span>
                    @endforeach
                </div>
            @endif

            @if ($rx->medicines->count())
                <strong>Medicines:</strong>
                <table class="table table-sm mt-2">
                    <thead class="table-light"><tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Note</th></tr></thead>
                    <tbody>
                        @foreach ($rx->medicines as $m)
                            <tr>
                                <td><strong>{{ optional($m->medicine)->medicine_name ?? '—' }}</strong></td>
                                <td><code>{{ $m->dosage ?? '—' }}</code></td>
                                <td>{{ $m->frequency ?? '—' }}</td>
                                <td>{{ $m->duration ?? '—' }}</td>
                                <td><small>{{ $m->note ?? '' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if ($rx->labInvestigations->count())
                <strong>Lab Tests:</strong>
                <ul class="mb-2">
                    @foreach ($rx->labInvestigations as $l)
                        <li>{{ optional($l->labInvestigation)->name ?? '—' }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($rx->advice)
                <p class="mb-1"><strong>Advice:</strong> {{ $rx->advice }}</p>
            @endif
            @if ($rx->next_visit)
                <p class="mb-0 text-info"><strong>Next visit:</strong> {{ $rx->next_visit }}</p>
            @endif
        </div>
    </div>
@empty
    <div class="card portal-card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-prescription2 display-3"></i>
            <p class="mt-2 mb-0">No prescriptions yet.</p>
        </div>
    </div>
@endforelse

@if ($prescriptions->hasPages())
    {{ $prescriptions->links() }}
@endif
@endsection
