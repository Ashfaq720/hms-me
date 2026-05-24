@extends('backend.layouts.master')
@section('title', 'GL Journal')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-journal-text"></i> General-Ledger Journal</h4>
        <span class="badge bg-primary p-2">{{ $journals->total() }} entries</span>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Journal No</th><th>Posting Date</th>
                        <th>Source</th><th>Reference</th><th>Memo</th>
                        <th>Status</th><th>Created By</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($journals as $j)
                    <tr>
                        <td>{{ $j->id }}</td>
                        <td><strong>{{ $j->journal_no }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($j->posting_date)->toDateString() }}</td>
                        <td><span class="badge bg-secondary">{{ $j->source }}</span></td>
                        <td><small>{{ $j->reference_type }}#{{ $j->reference_id }}</small></td>
                        <td>{{ $j->memo }}</td>
                        <td><span class="badge bg-{{ $j->status === 'posted' ? 'success' : 'warning text-dark' }}">{{ ucfirst($j->status) }}</span></td>
                        <td>{{ $j->creator_name ?? '—' }}</td>
                        <td>
                            <a href="{{ route('accounting.journal.show', $j->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">
                        No journal entries yet. Journals are auto-posted by Billing/Refund/Cash-in/Cash-out flows.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $journals->links() }}</div>
    </div>
</div>
@endsection
