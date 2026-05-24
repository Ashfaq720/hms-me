@extends('backend.layouts.master')
@section('title', 'Attendance')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-calendar-check"></i> Employee Attendance</h4>
        <span class="badge bg-primary p-2">{{ $records->total() }} records</span>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Date</th><th>Employee</th><th>Check-In</th><th>Check-Out</th>
                        <th>Status</th><th>Source</th><th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($records as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->date }}</td>
                        <td>
                            @if ($r->employee)
                                <strong>{{ trim(($r->employee->first_name ?? '') . ' ' . ($r->employee->last_name ?? '')) }}</strong>
                                <br><small class="text-muted">{{ $r->employee->employee_code }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $r->check_in }}</td>
                        <td>{{ $r->check_out }}</td>
                        <td>
                            @php $col = ['present'=>'success','absent'=>'danger','leave'=>'warning text-dark','half'=>'info']; @endphp
                            <span class="badge bg-{{ $col[strtolower($r->status ?? '')] ?? 'secondary' }}">{{ ucfirst($r->status ?? '—') }}</span>
                        </td>
                        <td><small>{{ $r->source }}</small></td>
                        <td>{{ $r->remarks }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">
                        No attendance recorded yet. Capture via biometric / manual entry — table: <code>employee_attendance</code>.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $records->links() }}</div>
    </div>
</div>
@endsection
