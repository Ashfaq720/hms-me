<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->gate('hr.employee.view');

        $employees = Employee::with(['branch'])
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(function ($w) use ($term) {
                    $w->where('first_name', 'like', "%$term%")
                        ->orWhere('last_name', 'like', "%$term%")
                        ->orWhere('employee_code', 'like', "%$term%")
                        ->orWhere('email', 'like', "%$term%");
                });
            })
            ->when($request->string('staff_type')->toString(), fn ($q, $t) => $q->where('staff_type', $t))
            ->orderBy('first_name')
            ->paginate(25)
            ->withQueryString();

        return view('hr.employees.index', compact('employees'));
    }

    public function create()
    {
        $this->gate('hr.employee.manage');
        return view('hr.employees.create', ['employee' => new Employee()]);
    }

    public function store(Request $request)
    {
        $this->gate('hr.employee.manage');
        Employee::create($request->validate($this->rules()));
        return redirect()->route('hr.employees.index')->with('success', 'Employee created.');
    }

    public function show(Employee $employee)
    {
        $this->gate('hr.employee.view');
        $employee->load('qualifications', 'attendance', 'leaves', 'walletTransactions');
        return view('hr.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $this->gate('hr.employee.manage');
        return view('hr.employees.create', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->gate('hr.employee.manage');
        $employee->update($request->validate($this->rules($employee->id)));
        return redirect()->route('hr.employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $this->gate('hr.employee.manage');
        $employee->delete();
        return redirect()->route('hr.employees.index')->with('success', 'Employee archived.');
    }

    private function rules(?int $id = null): array
    {
        return [
            'employee_code' => ['required', 'string', 'max:32', Rule::unique('employees')->ignore($id)],
            'first_name' => ['required', 'string', 'max:191'],
            'last_name' => ['nullable', 'string', 'max:191'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'staff_type' => ['required', Rule::in([
                'doctor', 'nurse', 'pharmacist', 'lab_technician', 'radiologist',
                'radiology_technician', 'receptionist', 'cashier', 'accountant',
                'inventory_manager', 'hr_manager', 'admin', 'security', 'housekeeping',
                'paramedic', 'ambulance_driver', 'biomedical', 'it_support', 'other',
            ])],
            'joining_date' => ['nullable', 'date'],
            'employment_type' => ['nullable', Rule::in(['full_time', 'part_time', 'visiting', 'contract', 'intern'])],
            'status' => ['nullable', Rule::in(['active', 'on_leave', 'suspended', 'resigned', 'terminated'])],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:191'],
            'bank_account' => ['nullable', 'string', 'max:64'],
        ];
    }

    private function gate(string $perm): void
    {
        if (! auth()->user()?->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }
}
