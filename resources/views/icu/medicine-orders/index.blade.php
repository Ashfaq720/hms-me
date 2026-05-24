@extends('backend.layouts.master')

@section('title', 'Medicine Orders — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Medicine Orders</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                    — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if (in_array($admission->status, ['Approved', 'Admitted']))
                    <a data-size="xl" class="btn btn-primary btn-sm"
                        data-url="{{ route('icu.admissions.medicine-orders.create', $admission->id) }}"
                        data-ajax-popup="true" data-title="Add Medicine Order"
                        data-bs-toggle="tooltip" title="Add Medicine Order"
                        href="#">
                        <i class="bi bi-plus-lg"></i> Add Medicine Order
                    </a>
                @endif
                <a href="{{ route('icu.admissions.show', $admission->id) }}"
                    class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        <div class="card mt-2">
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="3%">SN</th>
                                <th>Medicine</th>
                                <th>Unit</th>
                                <th>Category</th>
                                <th>Group</th>
                                <th>Qty</th>
                                <th>Prescribed By</th>
                                <th>Status</th>
                                <th>Order By</th>
                                <th>Date</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $order->medicine->medicine_name ?? 'N/A' }}</td>
                                    <td>{{ $order->medicine->unit?->name ?? '-' }}</td>
                                    <td>{{ $order->medicine->category?->name ?? '-' }}</td>
                                    <td>{{ $order->medicine->medicalGroup?->name ?? '-' }}</td>
                                    <td>{{ $order->qty }}</td>
                                    <td>{{ $order->prescribedBy->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ strtoupper($order->status) }}
                                    </td>
                                    <td>{{ $order->order_by ?? '-' }}</td>
                                    <td>{{ $order->created_at?->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if ($order->status == 'pending')
                                                    <li>
                                                        <a class="dropdown-item"
                                                            data-url="{{ route('icu.admissions.medicine-orders.edit', [$admission->id, $order->id]) }}"
                                                            data-ajax-popup="true" data-title="Edit Medicine Order" href="#">
                                                            <i class="bi bi-pencil text-primary me-2"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form
                                                            action="{{ route('icu.admissions.medicine-orders.destroy', [$admission->id, $order->id]) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Are you sure you want to delete this medicine order?')">
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
                                    <td colspan="11" class="text-center text-muted py-4">No medicine orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
