@extends('backend.layouts.master')
@section('title','Bills')
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between">
        <div>
            <h1 class="app-page-title">Bills</h1>
            <p class="text-muted small mb-0">Consolidated bills assembled from service-charge postings (SRS &sect;5.17).</p>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" class="row g-2 mt-3">
        <div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="Bill # or patient name..." value="{{ request('q') }}"></div>
        <div class="col-md-3">
            <select name="status" class="form-select"><option value="">All statuses</option>
                @foreach (['draft','provisional','final','paid','partially_paid','cancelled','refunded'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select"><option value="">All types</option>
                @foreach (['opd','ipd','er','icu','ot','procedure','lab','radiology','pharmacy','ambulance','package'] as $t)
                    <option value="{{ $t }}" @selected(request('type') === $t)>{{ strtoupper($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
    </form>

    <div class="card mt-3"><table class="table mb-0">
        <thead><tr>
            <th>Bill #</th><th>Date</th><th>Patient</th><th>Type</th>
            <th class="text-end">Grand total</th><th class="text-end">Paid</th><th class="text-end">Due</th>
            <th>Status</th><th></th>
        </tr></thead>
        <tbody>
            @forelse ($bills as $bill)
                <tr>
                    <td><code>{{ $bill->bill_no }}</code></td>
                    <td>{{ optional($bill->bill_date)->toDateString() }}</td>
                    <td>{{ optional($bill->patient)->patient_name }}<br><small class="text-muted">MRN {{ optional($bill->patient)->mrn }}</small></td>
                    <td><span class="badge bg-info-soft">{{ strtoupper($bill->bill_type) }}</span></td>
                    <td class="text-end">{{ number_format((float) $bill->grand_total, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $bill->paid_total, 2) }}</td>
                    <td class="text-end {{ $bill->balance_due > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format((float) $bill->balance_due, 2) }}</td>
                    <td>
                        @php $color = match($bill->status){'paid'=>'success','partially_paid'=>'warning','final'=>'primary','cancelled'=>'secondary','refunded'=>'dark',default=>'info'}; @endphp
                        <span class="badge bg-{{ $color }}">{{ ucwords(str_replace('_',' ',$bill->status)) }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('billing.bills.show',$bill) }}" class="btn btn-sm btn-outline-primary">Open</a>
                    </td>
                </tr>
            @empty <tr><td colspan="9" class="text-center text-muted py-3">No bills yet.</td></tr> @endforelse
        </tbody>
    </table></div>
    <div class="mt-2">{{ $bills->links() }}</div>
</div>
@endsection
