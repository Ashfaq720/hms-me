@extends('backend.layouts.master')
@section('title', 'NICU Procedures')
@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="bi bi-lightbulb"></i> Procedures &amp; Clinical Events</h4>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Baby</th><th>Code</th><th>Name</th><th>Start</th><th>End</th><th>Status</th><th>Indication</th><th>Outcome</th></tr></thead>
                <tbody>
                @forelse ($procedures as $p)
                    <tr>
                        <td>{{ optional(optional($p->admission)->patient)->patient_name ?? '—' }}</td>
                        <td><span class="badge bg-info bg-opacity-15 text-info">{{ $p->procedure_code }}</span></td>
                        <td>{{ $p->procedure_name }}</td>
                        <td>{{ $p->start_time?->format('m-d H:i') }}</td>
                        <td>{{ $p->end_time?->format('m-d H:i') ?? '—' }}</td>
                        <td><span class="badge bg-{{ $p->status === 'completed' ? 'success' : ($p->status === 'ongoing' ? 'warning' : 'danger') }}">{{ ucfirst($p->status) }}</span></td>
                        <td>{{ $p->clinical_indication }}</td>
                        <td>{{ $p->outcome }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No procedures</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($procedures, 'links'))<div class="p-3">{{ $procedures->links() }}</div>@endif
    </div>
</div>
@endsection
