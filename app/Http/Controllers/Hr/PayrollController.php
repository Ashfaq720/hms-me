<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index()
    {
        $month = request('month', now()->format('Y-m'));
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('first_name')
            ->paginate(50);
        return view('hr.payroll.index', compact('employees', 'month'));
    }
}
