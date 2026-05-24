@extends('backend.layouts.master')
@section('title', 'Vouchers')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-receipt-cutoff"></i> Vouchers (Cash / Bank / Manual)</h4>
        <a href="{{ route('accounting.voucher.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> New Voucher
        </a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>#</th><th>Voucher No</th><th>Date</th><th>Type</th><th>Memo</th><th>Status</th></tr>
                </thead>
                <tbody>
                @forelse ($vouchers as $v)
                    <tr>
                        <td>{{ $v->id }}</td>
                        <td><strong>{{ $v->journal_no }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($v->posting_date)->toDateString() }}</td>
                        <td><span class="badge bg-info bg-opacity-15 text-info">{{ $v->source }}</span></td>
                        <td>{{ $v->memo }}</td>
                        <td><span class="badge bg-{{ $v->status === 'posted' ? 'success' : 'warning text-dark' }}">{{ ucfirst($v->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">
                        No vouchers yet. Vouchers post into <code>gl_journals</code> with source = cash_receipt / cash_payment / bank_receipt / bank_payment / manual.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $vouchers->links() }}</div>
    </div>
</div>
@endsection
