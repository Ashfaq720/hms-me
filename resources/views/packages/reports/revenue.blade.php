@extends('backend.layouts.master')
@section('title', 'Package Revenue Report')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Package Revenue Report</h4>
        <a href="{{ route('packages.reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Reports</a>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light"><tr><th>Code</th><th>Package</th><th>Type</th><th class="text-center">Enrolments</th><th class="text-end">Agreed ৳</th><th class="text-end">Paid ৳</th><th class="text-end">Outstanding ৳</th></tr></thead>
            <tbody>
            @php $tAgreed = 0; $tPaid = 0; $tOut = 0; @endphp
            @forelse ($rows as $r)
                @php $tAgreed += $r->agreed; $tPaid += $r->paid; $tOut += $r->outstanding; @endphp
                <tr>
                    <td><code>{{ $r->code }}</code></td>
                    <td><strong>{{ $r->name }}</strong></td>
                    <td><span class="badge bg-info bg-opacity-15 text-info">{{ $r->package_type }}</span></td>
                    <td class="text-center">{{ $r->enrolments }}</td>
                    <td class="text-end">{{ number_format((float) $r->agreed, 2) }}</td>
                    <td class="text-end text-success">{{ number_format((float) $r->paid, 2) }}</td>
                    <td class="text-end {{ $r->outstanding > 0.01 ? 'text-danger' : '' }}">{{ number_format((float) $r->outstanding, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No data</td></tr>
            @endforelse
            </tbody>
            @if ($rows->count())
                <tfoot class="table-light fw-bold">
                    <tr><td colspan="4" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($tAgreed, 2) }}</td>
                        <td class="text-end text-success">{{ number_format($tPaid, 2) }}</td>
                        <td class="text-end text-danger">{{ number_format($tOut, 2) }}</td></tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
