@extends('backend.layouts.master')
@section('title', 'Department-wise Package Report')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-diagram-3"></i> Department-wise Package Report</h4>
        <a href="{{ route('packages.reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Reports</a>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light"><tr><th>Department</th><th class="text-center">Enrolments</th><th class="text-end">Agreed ৳</th><th class="text-end">Paid ৳</th><th class="text-end">Outstanding ৳</th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td><strong>{{ $r->dept ?? '— No department —' }}</strong></td>
                    <td class="text-center">{{ $r->enrolments }}</td>
                    <td class="text-end">{{ number_format((float) $r->agreed, 2) }}</td>
                    <td class="text-end text-success">{{ number_format((float) $r->paid, 2) }}</td>
                    <td class="text-end {{ ($r->agreed - $r->paid) > 0.01 ? 'text-danger' : '' }}">{{ number_format((float) $r->agreed - (float) $r->paid, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
