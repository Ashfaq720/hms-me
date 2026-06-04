@extends('backend.layouts.master')
@section('title', 'Claim ' . $claim->claim_no)
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between flex-wrap">
        <div>
            <h1 class="app-page-title">{{ $claim->claim_no }}</h1>
            <p class="text-muted small mb-0">
                {{ optional($claim->patient)->patient_name }} ·
                Payer: {{ optional($claim->payer)->name }} ·
                Policy: {{ optional($claim->policy)->policy_no }}
            </p>
        </div>
        <a href="{{ route('insurance.claims.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger mt-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row mt-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><strong>Items</strong></div>
                <table class="table mb-0">
                    <thead><tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit</th><th class="text-end">Line total</th><th class="text-end">Approved</th></tr></thead>
                    <tbody>
                        @forelse ($claim->items as $i)
                            <tr>
                                <td>{{ $i->description }}</td>
                                <td class="text-end">{{ number_format((float)$i->quantity,4) }}</td>
                                <td class="text-end">{{ number_format((float)$i->unit_price,2) }}</td>
                                <td class="text-end">{{ number_format((float)$i->line_total,2) }}</td>
                                <td class="text-end">{{ number_format((float)$i->approved_amount,2) }}</td>
                            </tr>
                        @empty <tr><td colspan="5" class="text-center text-muted py-3">No items.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h5 class="card-title">Summary</h5>
                <dl class="row mb-0">
                    <dt class="col-7">Gross</dt><dd class="col-5 text-end">{{ number_format((float)$claim->gross_amount,2) }}</dd>
                    <dt class="col-7">Patient copay</dt><dd class="col-5 text-end">{{ number_format((float)$claim->patient_copay,2) }}</dd>
                    <dt class="col-7 fw-bold">Claim amount</dt><dd class="col-5 text-end fw-bold">{{ number_format((float)$claim->claim_amount,2) }}</dd>
                    <dt class="col-7">Approved</dt><dd class="col-5 text-end">{{ number_format((float)$claim->approved_amount,2) }}</dd>
                    <dt class="col-7">Settled</dt><dd class="col-5 text-end">{{ number_format((float)$claim->settled_amount,2) }}</dd>
                </dl>
            </div></div>

            <div class="card mt-3"><div class="card-body">
                <h6>Status: <span class="badge bg-info">{{ ucwords(str_replace('_',' ',$claim->status)) }}</span></h6>

                @if ($claim->status === 'draft')
                    @can('insurance.claim.submit')
                        <form method="POST" action="{{ route('insurance.claims.submit',$claim) }}" class="mt-2">
                            @csrf
                            <button class="btn btn-primary w-100">Submit to Payer</button>
                        </form>
                    @endcan
                @endif

                @if (in_array($claim->status, ['submitted','under_review']))
                    @can('insurance.claim.adjudicate')
                        <form method="POST" action="{{ route('insurance.claims.approve',$claim) }}" class="mt-2">
                            @csrf
                            <label class="form-label small">Approved amount</label>
                            <input type="number" step="0.01" min="0" name="approved_amount" class="form-control" value="{{ $claim->claim_amount }}" required>
                            <button class="btn btn-success w-100 mt-2">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('insurance.claims.reject',$claim) }}" class="mt-2">
                            @csrf
                            <input type="text" name="reason" class="form-control" placeholder="Rejection reason" required>
                            <button class="btn btn-outline-danger w-100 mt-2">Reject</button>
                        </form>
                    @endcan
                @endif

                @if (in_array($claim->status, ['approved','short_paid']))
                    @can('insurance.claim.settle')
                        <form method="POST" action="{{ route('insurance.claims.settle',$claim) }}" class="mt-2">
                            @csrf
                            <label class="form-label small">Settled amount</label>
                            <input type="number" step="0.01" min="0" name="settled_amount" class="form-control" value="{{ $claim->approved_amount }}" required>
                            <button class="btn btn-primary w-100 mt-2">Mark Settled</button>
                        </form>
                    @endcan
                @endif
            </div></div>
        </div>
    </div>
</div>
@endsection
