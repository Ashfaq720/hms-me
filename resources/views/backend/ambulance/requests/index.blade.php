@extends('backend.layouts.master')

@section('title', 'Ambulance Requests')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Ambulance Requests</h1>
            </div>

            <a data-size="xl" class="btn btn-primary waves-effect waves-light" data-url="{{ route('amb.requests.create') }}"
                data-ajax-popup="true" data-title="Add Request" data-bs-toggle="tooltip" title="Add Request"
                data-original-title="Add Request"><i class="bi bi-plus-lg me-1"></i>
                Add Request</a>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div
                        class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-2">Request List</h6>
                        <div id="dt_amb_req_Search"></div>
                    </div>

                    <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                        <table class="table display table-row-rounded">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Source</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Patient</th>
                                    <th>Pickup</th>
                                    <th>Status</th>
                                    <th width="220">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $r)
                                    <tr>
                                        <td>{{ $r->id }}</td>
                                        <td>{{ $r->source }}</td>
                                        <td>{{ $r->request_type }}</td>
                                        <td>
                                            @php
                                                $cls =
                                                    $r->priority === 'CRITICAL'
                                                        ? 'danger'
                                                        : ($r->priority === 'HIGH'
                                                            ? 'warning'
                                                            : 'secondary');
                                            @endphp
                                            <span class="badge bg-{{ $cls }}">{{ $r->priority }}</span>
                                        </td>
                                        <td>
                                            @if ($r->patient)
                                                <div class="fw-bold">{{ $r->patient->patient_name }}</div>
                                                <div class="text-muted small">{{ $r->patient->mobileno ?? '' }}</div>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                                @if ($r->temp_patient_id)
                                                    <div class="text-muted small">{{ $r->temp_patient_id }}</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ $r->pickup_location }}</td>
                                        <td>
                                            @php
                                                $scls =
                                                    $r->status === 'NEW'
                                                        ? 'info'
                                                        : ($r->status === 'ASSIGNED'
                                                            ? 'primary'
                                                            : ($r->status === 'COMPLETED'
                                                                ? 'success'
                                                                : 'secondary'));
                                            @endphp
                                            <span class="badge bg-{{ $scls }}">{{ $r->status }}</span>
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                <a class="btn btn-sm btn-info" title="View"
                                                    href="{{ route('amb.requests.show', $r) }}">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>

                                                @if ($r->status === 'NEW')
                                                    <a class="btn btn-sm btn-success" title="Assign"
                                                        href="{{ route('amb.trips.assignForm', $r) }}">
                                                        <i class="fa-solid fa-truck-medical"></i>
                                                    </a>

                                                    <form method="POST" action="{{ route('amb.requests.destroy', $r) }}"
                                                        onsubmit="return confirm('Cancel this request?')" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" type="submit">
                                                            <i class="fa-solid fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No data found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3 pb-3">
                            {{ $requests->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
