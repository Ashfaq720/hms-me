@extends('backend.layouts.master')
@section('title','Employees')
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between">
        <h1 class="app-page-title">Employees</h1>
        @can('hr.employee.manage') <a href="{{ route('hr.employees.create') }}" class="btn btn-primary">Add Employee</a> @endcan
    </div>
    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif

    <form method="GET" class="row g-2 mt-3">
        <div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="Search name/code/email..." value="{{ request('q') }}"></div>
        <div class="col-md-4">
            <select name="staff_type" class="form-select"><option value="">All staff types</option>
                @foreach (['doctor','nurse','pharmacist','lab_technician','radiologist','receptionist','cashier','accountant','inventory_manager','hr_manager','admin'] as $st)
                    <option value="{{ $st }}" @selected(request('staff_type') === $st)>{{ ucwords(str_replace('_',' ',$st)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-outline-primary w-100">Filter</button></div>
    </form>

    <div class="card mt-3"><table class="table mb-0">
        <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Phone</th><th>Branch</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse ($employees as $e)
                <tr>
                    <td><code>{{ $e->employee_code }}</code></td>
                    <td>{{ $e->fullName() }}<br><small class="text-muted">{{ $e->email }}</small></td>
                    <td><span class="badge bg-info-soft">{{ $e->staff_type }}</span></td>
                    <td>{{ $e->phone }}</td>
                    <td>{{ optional($e->branch)->name }}</td>
                    <td><span class="badge bg-{{ $e->status === 'active' ? 'success' : 'secondary' }}">{{ $e->status }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('hr.employees.show',$e) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        @can('hr.employee.manage') <a href="{{ route('hr.employees.edit',$e) }}" class="btn btn-sm btn-outline-primary">Edit</a> @endcan
                    </td>
                </tr>
            @empty <tr><td colspan="7" class="text-center text-muted py-3">No employees yet.</td></tr> @endforelse
        </tbody>
    </table></div>
    <div class="mt-2">{{ $employees->links() }}</div>
</div>
@endsection
