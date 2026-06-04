@extends('backend.layouts.master')
@section('title', 'Journal ' . $journal->journal_no)
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0"><i class="bi bi-journal-text"></i> {{ $journal->journal_no }}</h4>
            <small class="text-muted">{{ $journal->memo }}</small>
        </div>
        <a href="{{ route('accounting.journal.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @php
        $debit = $postings->sum('debit');
        $credit = $postings->sum('credit');
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card bg-light p-3"><small class="text-muted">Posting Date</small><h6 class="mb-0">{{ \Carbon\Carbon::parse($journal->posting_date)->toDateString() }}</h6></div></div>
        <div class="col-md-4"><div class="card bg-light p-3"><small class="text-muted">Source</small><h6 class="mb-0">{{ $journal->source }} · {{ $journal->reference_type }}#{{ $journal->reference_id }}</h6></div></div>
        <div class="col-md-4"><div class="card bg-light p-3"><small class="text-muted">Status</small><h6 class="mb-0">{{ ucfirst($journal->status) }}</h6></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">Postings</h6></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>#</th><th>Account</th><th>Description</th><th class="text-end">Debit ৳</th><th class="text-end">Credit ৳</th></tr>
                </thead>
                <tbody>
                @foreach ($postings as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $p->account_code }}</strong> · {{ $p->account_name }}</td>
                        <td>{{ $p->description }}</td>
                        <td class="text-end">{{ $p->debit > 0 ? number_format($p->debit, 2) : '' }}</td>
                        <td class="text-end">{{ $p->credit > 0 ? number_format($p->credit, 2) : '' }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total</td>
                        <td class="text-end fw-bold">{{ number_format($debit, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($credit, 2) }}</td>
                    </tr>
                    @if (abs($debit - $credit) > 0.01)
                        <tr><td colspan="5" class="text-end text-danger"><strong>Unbalanced</strong> by ৳ {{ number_format(abs($debit - $credit), 2) }}</td></tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
