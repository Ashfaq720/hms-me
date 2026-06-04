@extends('backend.layouts.master')

@section('title', 'Pathology Orders — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Pathology Orders</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                    — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if (in_array($admission->status, ['Approved', 'Admitted']))
                    <a data-size="lg" class="btn btn-primary btn-sm"
                        data-url="{{ route('icu.admissions.pathology-orders.create', $admission->id) }}"
                        data-ajax-popup="true" data-title="Add Pathology Order"
                        data-bs-toggle="tooltip" title="Add Pathology Order"
                        href="#">
                        <i class="bi bi-plus-lg"></i> Pathology Order
                    </a>
                @endif
                <a href="{{ route('icu.admissions.show', $admission->id) }}"
                    class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        <div class="card mt-2">
            <div class="card-header py-2">
                <h6 class="mb-0 fw-semibold">Pathology Queue</h6>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="3%">SN</th>
                                <th>Order #</th>
                                <th width="12%">Date/Time</th>
                                <th>Investigations</th>
                                <th>Priority</th>
                                <th>Doctor</th>
                                <th>Lab</th>
                                <th>Collected By</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $order->order_number ?? '-' }}</td>
                                    <td>{{ $order->datetime ? $order->datetime->format('d M Y h:i A') : '-' }}</td>
                                    <td>
                                        @forelse ($order->requests as $req)
                                            <div>
                                                <span class="badge bg-light text-dark border">{{ $req->labInvestigation->name ?? '-' }}</span>
                                                @if ($req->labInvestigationCategory)
                                                    <small class="text-muted">({{ $req->labInvestigationCategory->name }})</small>
                                                @endif
                                            </div>
                                        @empty
                                            -
                                        @endforelse
                                    </td>
                                    <td>
                                        @if ($order->priority)
                                            @php
                                                $priorityClass = match (strtolower($order->priority)) {
                                                    'urgent' => 'bg-danger',
                                                    'regular' => 'bg-success',
                                                    'stat' => 'bg-info',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $priorityClass }}">{{ ucfirst($order->priority) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->doctor->name ?? '-' }}</td>
                                    <td>{{ $order->lab_name ?? '-' }}</td>
                                    <td>{{ $order->collected_by ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('icu.admissions.pathology-orders.show', [$admission->id, $order->id]) }}">
                                                        <i class="bi bi-eye text-info me-2"></i> View
                                                    </a>
                                                </li>
                                                @if (in_array($admission->status, ['Approved', 'Admitted']))
                                                    <li>
                                                        <a class="dropdown-item" data-size="lg"
                                                            data-url="{{ route('icu.admissions.pathology-orders.edit', [$admission->id, $order->id]) }}"
                                                            data-ajax-popup="true" data-title="Edit Pathology Order" href="#">
                                                            <i class="bi bi-pencil text-primary me-2"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form
                                                            action="{{ route('icu.admissions.pathology-orders.destroy', [$admission->id, $order->id]) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Are you sure you want to delete this pathology order?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-trash text-danger me-2"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No pathology orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
