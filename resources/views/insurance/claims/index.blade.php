@extends('backend.layouts.master')
@section('title','Insurance Claims')
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between">
        <div>
            <h1 class="app-page-title">Insurance Claims</h1>
            <p class="text-muted small mb-0">Claims built from finalized bills against insurance policies (SRS &sect;5.21).</p>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif

    <form method="GET" class="row g-2 mt-3">
        <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Claim # or bill # or patient..." value="{{ request('q') }}"></div>
        <div class="col-md-4">
            <select name="status" class="form-select"><option value="">All statuses</option>
                @foreach (['draft','submitted','under_review','approved','rejected','short_paid','settled','appeal','closed'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
    </form>

    <div class="card mt-3"><table class="table mb-0">
        <thead><tr><th>Claim #</th><th>Date</th><th>Patient</th><th>Payer</th><th>Bill ref</th>
            <th class="text-end">Claim amt</th><th class="text-end">Approved</th><th class="text-end">Settled</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse ($claims as $c)
                <tr>
                    <td><code>{{ $c->claim_no }}</code></td>
                    <td>{{ optional($c->claim_date)->toDateString() }}</td>
                    <td>{{ optional($c->patient)->patient_name }}</td>
                    <td>{{ optional($c->payer)->name }}</td>
                    <td>{{ $c->bill_reference }}</td>
                    <td class="text-end">{{ number_format((float)$c->claim_amount,2) }}</td>
                    <td class="text-end">{{ number_format((float)$c->approved_amount,2) }}</td>
                    <td class="text-end">{{ number_format((float)$c->settled_amount,2) }}</td>
                    <td>
                        @php $color = match($c->status){'settled'=>'success','approved'=>'primary','rejected'=>'danger','short_paid'=>'warning','submitted','under_review'=>'info',default=>'secondary'}; @endphp
                        <span class="badge bg-{{ $color }}">{{ ucwords(str_replace('_',' ',$c->status)) }}</span>
                    </td>
                    <td class="text-end"><a href="{{ route('insurance.claims.show',$c) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            @empty <tr><td colspan="10" class="text-center text-muted py-3">No claims yet. Open a finalized bill and click "Build Claim" to create one.</td></tr> @endforelse
        </tbody>
    </table></div>
    <div class="mt-2">{{ $claims->links() }}</div>
</div>
@endsection
