@extends('backend.layouts.master')
@section('title', $employee->fullName())
@section('content')
<div class="container">
    <div class="d-flex justify-content-between">
        <h1 class="app-page-title">{{ $employee->fullName() }}</h1>
        <div>
            @can('hr.employee.manage') <a href="{{ route('hr.employees.edit',$employee) }}" class="btn btn-primary">Edit</a> @endcan
            <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6"><div class="card"><div class="card-body">
            <dl class="row mb-0">
                <dt class="col-4">Code</dt><dd class="col-8"><code>{{ $employee->employee_code }}</code></dd>
                <dt class="col-4">Type</dt><dd class="col-8">{{ $employee->staff_type }}</dd>
                <dt class="col-4">Email</dt><dd class="col-8">{{ $employee->email }}</dd>
                <dt class="col-4">Phone</dt><dd class="col-8">{{ $employee->phone }}</dd>
                <dt class="col-4">Joined</dt><dd class="col-8">{{ optional($employee->joining_date)->toDateString() }}</dd>
                <dt class="col-4">Status</dt><dd class="col-8"><span class="badge bg-success">{{ $employee->status }}</span></dd>
            </dl>
        </div></div></div>
        <div class="col-md-6"><div class="card"><div class="card-body">
            <h6>Salary</h6>
            <p class="fs-3 mb-0">{{ number_format((float) $employee->base_salary, 2) }}</p>
            <small class="text-muted">{{ $employee->bank_name }} · {{ $employee->bank_account }}</small>
        </div></div></div>
    </div>
</div>
@endsection
