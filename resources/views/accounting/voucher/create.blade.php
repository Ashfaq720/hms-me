@extends('backend.layouts.master')
@section('title', 'New Voucher')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-receipt-cutoff"></i> New Voucher</h4>
        <a href="{{ route('accounting.voucher.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Manual voucher entry posts into <code>gl_journals</code> + <code>gl_postings</code>. The form below is read-only;
        production voucher entry should be approved through the accounting workflow.
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Voucher Type</label>
                    <select class="form-select"><option>Cash Receipt</option><option>Cash Payment</option><option>Bank Receipt</option><option>Bank Payment</option><option>Manual</option></select>
                </div>
                <div class="col-md-3"><label class="form-label">Date</label><input type="date" class="form-control" value="{{ now()->toDateString() }}"></div>
                <div class="col-md-6"><label class="form-label">Memo</label><input type="text" class="form-control" placeholder="Purpose / narration"></div>
            </div>
            <h6 class="mt-4 mb-2">Postings (debit must equal credit)</h6>
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Account</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead>
                <tbody>
                    @for ($i = 0; $i < 3; $i++)
                    <tr>
                        <td>
                            <select class="form-select form-select-sm">
                                <option value="">-- Select account --</option>
                                @foreach ($accounts as $a)
                                    <option value="{{ $a->id }}">{{ $a->code }} · {{ $a->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end"></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
            <div class="text-end">
                <button class="btn btn-primary" disabled title="Voucher posting endpoint not wired yet">Post Voucher</button>
            </div>
        </div>
    </div>
</div>
@endsection
