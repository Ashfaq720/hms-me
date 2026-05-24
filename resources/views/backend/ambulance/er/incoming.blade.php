@extends('backend.layouts.master')

@section('title', 'ER Incoming Ambulances')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">ER Incoming Ambulances</h1>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                    <table class="table display table-row-rounded">
                        <thead class="table-light">
                            <tr>
                                <th>Trip</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Ambulance</th>
                                <th>Patient</th>
                                <th>Pickup</th>
                                <th>ETA</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($trips as $t)
                            <tr>
                                <td>#{{ $t->id }}</td>
                                <td><span class="badge bg-primary">{{ $t->status }}</span></td>
                                <td>
                                    @php
                                        $pri = $t->request?->priority ?? 'NORMAL';
                                        $cls = $pri === 'CRITICAL' ? 'danger' : ($pri === 'HIGH' ? 'warning' : 'secondary');
                                    @endphp
                                    <span class="badge bg-{{ $cls }}">{{ $pri }}</span>
                                </td>
                                <td>{{ $t->ambulance?->reg_no }} ({{ $t->ambulance?->type }})</td>
                                <td>
                                    @if($t->request?->patient)
                                        <div class="fw-bold">{{ $t->request->patient->patient_name }}</div>
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                                <td>{{ $t->request?->pickup_location }}</td>
                                <td>{{ $t->eta_minutes ? $t->eta_minutes.' min' : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No incoming ambulances</td></tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3 pb-3">
                        {{ $trips->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
