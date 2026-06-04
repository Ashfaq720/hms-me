<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeAttendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $records = EmployeeAttendance::with('employee')
            ->orderByDesc('date')->orderByDesc('id')->paginate(50);
        return view('hr.attendance.index', compact('records'));
    }
}
