@extends('backend.layouts.master')
@section('title', 'Payroll')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Payroll · {{ $month }}</h4>
        <form method="GET" class="d-flex gap-2">
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm">
            <button class="btn btn-sm btn-primary">Filter</button>
        </form>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Code</th><th>Name</th><th>Designation</th>
                        <th class="text-end">Base Salary</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($employees as $e)
                    <tr>
                        <td>{{ $e->id }}</td>
                        <td><strong>{{ $e->employee_code }}</strong></td>
                        <td>{{ trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? '')) }}</td>
                        <td>{{ \DB::table('designations')->where('id', $e->designation_id)->value('name') ?? '—' }}</td>
                        <td class="text-end">৳ {{ number_format((float) $e->base_salary, 2) }}</td>
                        <td><span class="badge bg-{{ $e->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($e->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No active employees</td></tr>
                @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Monthly Salary Total:</td>
                        <td class="text-end fw-bold">৳ {{ number_format($employees->sum('base_salary'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="p-3">{{ $employees->links() }}</div>
    </div>
    <p class="text-muted small mt-2">
        <i class="bi bi-info-circle"></i> Pay-run posting endpoint (attendance adjustments, deductions, NSSF, tax) is in <code>app/Models/Hr/PayrollRun.php</code>.
    </p>
</div>
@endsection
