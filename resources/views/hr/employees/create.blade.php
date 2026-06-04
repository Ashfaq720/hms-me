@extends('backend.layouts.master')
@section('title', $employee->exists ? 'Edit Employee' : 'Add Employee')
@section('content')
<div class="container">
    <h1 class="app-page-title">{{ $employee->exists ? 'Edit Employee' : 'Add Employee' }}</h1>
    <form method="POST" action="{{ $employee->exists ? route('hr.employees.update',$employee) : route('hr.employees.store') }}" class="card p-4 mt-3">
        @csrf @if($employee->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Code *</label><input name="employee_code" class="form-control" value="{{ old('employee_code',$employee->employee_code) }}" required></div>
            <div class="col-md-4"><label class="form-label">First name *</label><input name="first_name" class="form-control" value="{{ old('first_name',$employee->first_name) }}" required></div>
            <div class="col-md-4"><label class="form-label">Last name</label><input name="last_name" class="form-control" value="{{ old('last_name',$employee->last_name) }}"></div>
            <div class="col-md-3"><label class="form-label">Staff type *</label>
                <select name="staff_type" class="form-select" required>
                    @foreach (['doctor','nurse','pharmacist','lab_technician','radiologist','radiology_technician','receptionist','cashier','accountant','inventory_manager','hr_manager','admin','security','housekeeping','paramedic','ambulance_driver','biomedical','it_support','other'] as $st)
                        <option value="{{ $st }}" @selected(old('staff_type',$employee->staff_type) === $st)>{{ ucwords(str_replace('_',' ',$st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email',$employee->email) }}"></div>
            <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone',$employee->phone) }}"></div>
            <div class="col-md-4"><label class="form-label">Joining date</label><input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', optional($employee->joining_date)->toDateString()) }}"></div>
            <div class="col-md-3"><label class="form-label">Employment type</label>
                <select name="employment_type" class="form-select">
                    @foreach (['full_time','part_time','visiting','contract','intern'] as $et)
                        <option value="{{ $et }}" @selected(old('employment_type',$employee->employment_type ?? 'full_time') === $et)>{{ ucwords(str_replace('_',' ',$et)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach (['active','on_leave','suspended','resigned','terminated'] as $s)
                        <option value="{{ $s }}" @selected(old('status',$employee->status ?? 'active') === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Base salary</label><input type="number" step="0.01" name="base_salary" class="form-control" value="{{ old('base_salary',$employee->base_salary ?? 0) }}"></div>
            <div class="col-md-3"><label class="form-label">Bank name</label><input name="bank_name" class="form-control" value="{{ old('bank_name',$employee->bank_name) }}"></div>
            <div class="col-md-4"><label class="form-label">Bank account</label><input name="bank_account" class="form-control" value="{{ old('bank_account',$employee->bank_account) }}"></div>
        </div>
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
