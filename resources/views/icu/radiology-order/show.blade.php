@extends('backend.layouts.master')
@section('title', 'ICU Radiology Order')

@section('content')
    <div class="container-fluid py-3">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Radiology Order Detail</h5>
                <a href="{{ route('icu.admissions.show', $admission->id) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-md-4"><strong>Patient:</strong> {{ $admission->patient->patient_name ?? '-' }}</div>
                    <div class="col-md-4"><strong>Case:</strong> {{ $admission->icu_case_id }}</div>
                    <div class="col-md-4"><strong>Order #:</strong> {{ $order->order_number ?? '-' }}</div>

                    <div class="col-md-4"><strong>Date/Time:</strong>
                        {{ $order->datetime ? $order->datetime->format('d M Y h:i A') : '-' }}
                    </div>
                    <div class="col-md-4"><strong>Doctor:</strong> {{ $order->doctor->name ?? '-' }}</div>
                    <div class="col-md-4"><strong>Priority:</strong> {{ $order->priority ?? '-' }}</div>

                    <div class="col-md-4"><strong>Lab Name:</strong> {{ $order->lab_name ?? '-' }}</div>
                    <div class="col-md-4"><strong>Collected By:</strong> {{ $order->collected_by ?? '-' }}</div>
                    <div class="col-md-4"><strong>Source:</strong> {{ $order->source ?? '-' }}</div>

                    @if ($order->remarks)
                        <div class="col-12"><strong>Remarks:</strong> {{ $order->remarks }}</div>
                    @endif
                </div>

                <hr class="my-3">

                <h6 class="mb-2">Investigations</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">SN</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Investigation</th>
                                <th>Status</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->requests as $req)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $req->labInvestigationType->name ?? '-' }}</td>
                                    <td>{{ $req->labInvestigationCategory->name ?? '-' }}</td>
                                    <td>{{ $req->labInvestigation->name ?? '-' }}</td>
                                    <td>{{ $req->status ?? '-' }}</td>
                                    <td>
                                        @if ($req->file)
                                            <a href="{{ asset('storage/' . $req->file) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-earmark-text"></i> View
                                            </a>
                                        @else
                                            <span class="text-muted">No file</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No investigations found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
